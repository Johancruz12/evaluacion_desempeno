<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SalomonService;
use Illuminate\Http\Request;

class JefeController extends Controller
{
    public function __construct(private SalomonService $salomon) {}

    /**
     * Mi Equipo — visible para usuarios con rol jefe_area (o director_rh).
     *
     * Estrategia: UNIÓN de dos fuentes.
     *   1. Salomón: empleados con contrato activo en el área del jefe
     *      (mediante area->salomon_codigo).
     *   2. BD local: usuarios con area_id = área del jefe (cargados desde
     *      el Excel importado o creados manualmente).
     *
     * Se cruzan por cédula para enriquecer cada miembro con su info de
     * usuario local (evaluaciones, cargo, etc.) si existe.
     */
    public function team(Request $request)
    {
        $user = $request->user();
        if (!$user->isJefeArea() && !$user->isAdmin()) {
            abort(403);
        }

        $area            = $user->area;
        $areaSalomonCode = $area?->salomon_codigo;
        $jefeCedula      = $user->person?->document_number;
        $usingSalomon    = false;
        $salomonError    = null;

        // 1) Empleados de Salomón en el área (si la conexión funciona)
        $salomonEmployees = collect();
        if ($areaSalomonCode) {
            try {
                $rows = $this->salomon->getActiveEmployeesByArea((int) $areaSalomonCode);
                $salomonEmployees = collect($rows)->filter(
                    fn ($e) => $e->cedula !== $jefeCedula
                );
                $usingSalomon = true;
            } catch (\Throwable $e) {
                $salomonError = 'No se pudo conectar a Salomón: ' . $e->getMessage();
            }
        }

        // 2) Usuarios locales del área (Excel importado / manuales)
        $localMembers = User::where('is_active', true)
            ->where('area_id', $user->area_id)
            ->where('id', '!=', $user->id)
            ->with([
                'person',
                'positionType',
                'evaluationsAsEmployee' => fn ($q) => $q->with('template')->latest(),
            ])
            ->get()
            ->keyBy(fn ($u) => $u->person?->document_number);

        // 3) UNIÓN por cédula
        $teamData = collect();
        $seen     = [];

        // Primero, los de Salomón (con info enriquecida desde BD local si existe)
        foreach ($salomonEmployees as $emp) {
            $cedula     = $emp->cedula;
            $localUser  = $localMembers->get($cedula);
            $evals      = $localUser?->evaluationsAsEmployee ?? collect();
            $latestEval = $evals->first();

            $teamData->push([
                'source'           => $localUser ? 'salomon+excel' : 'salomon',
                'cedula'           => $cedula,
                'nombre_completo'  => collect([
                    $emp->primer_nombre,
                    $emp->segundo_nombre ?? null,
                    $emp->primer_apellido,
                    $emp->segundo_apellido ?? null,
                ])->filter()->implode(' '),
                'primer_apellido'  => $emp->primer_apellido,
                'cargo'            => $emp->cargo_nombre ?? $localUser?->positionType?->name,
                'local_user'       => $localUser,
                'latest_evaluation'=> $latestEval,
                'has_pending'      => $evals->where('status', 'pendiente')->count() > 0,
                'has_in_progress'  => $evals->where('status', 'en_progreso')->count() > 0,
                'total_evaluations'=> $evals->count(),
                'completed_count'  => $evals->whereIn('status', ['completada', 'revisada'])->count(),
            ]);

            $seen[$cedula] = true;
        }

        // Después, los locales (Excel) que NO aparecieron en Salomón
        foreach ($localMembers as $cedula => $member) {
            if (isset($seen[$cedula])) {
                continue;
            }
            $evals      = $member->evaluationsAsEmployee ?? collect();
            $latestEval = $evals->first();

            $teamData->push([
                'source'           => 'excel',
                'cedula'           => $cedula,
                'nombre_completo'  => $member->name,
                'primer_apellido'  => $member->person?->last_name,
                'cargo'            => $member->positionType?->name,
                'local_user'       => $member,
                'latest_evaluation'=> $latestEval,
                'has_pending'      => $evals->where('status', 'pendiente')->count() > 0,
                'has_in_progress'  => $evals->where('status', 'en_progreso')->count() > 0,
                'total_evaluations'=> $evals->count(),
                'completed_count'  => $evals->whereIn('status', ['completada', 'revisada'])->count(),
            ]);
        }

        $teamData = $teamData->sortBy('primer_apellido')->values();

        return view('jefe.team', compact('teamData', 'area', 'usingSalomon', 'salomonError'));
    }
}
