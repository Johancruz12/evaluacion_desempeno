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
     *   1. Salomón (fuente principal): empleados con contrato activo en el
     *      área del jefe (mediante area->salomon_codigo).
     *   2. BD local: usuarios con area_id = área del jefe (cargados desde
     *      el Excel importado o creados manualmente). Sirven para enriquecer
     *      con info de evaluaciones y para cubrir empleados que no estén
     *      en Salomón.
     *
     * Se cruzan por cédula.
     */
    public function team(Request $request)
    {
        $user = $request->user();
        if (!$user->isJefeArea() && !$user->isAdmin()) {
            abort(403);
        }

        $area            = $user->area;
        $areaSalomonCode = $area?->salomon_codigo;
        $usingSalomon    = false;
        $salomonError    = null;

        // Jefes/Coordinadores del área (se muestran aparte y se excluyen del listado de empleados)
        $areaJefes = collect();
        $jefeCedulas = collect();
        $jefeIds     = [];
        if ($user->area_id) {
            $areaJefes = User::where('is_active', true)
                ->where('area_id', $user->area_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'jefe_area'))
                ->with('person', 'positionType')
                ->get()
                ->sortBy(fn ($u) => $u->person?->last_name)
                ->values();

            $jefeCedulas = $areaJefes->map(fn ($u) => $u->person?->document_number)->filter()->values();
            $jefeIds     = $areaJefes->pluck('id')->all();
        }

        // 1) Empleados de Salomón en el área (FUENTE PRINCIPAL)
        $salomonEmployees = collect();
        if ($areaSalomonCode && (extension_loaded('pdo_sqlsrv') || extension_loaded('sqlsrv'))) {
            try {
                $rows = $this->salomon->getActiveEmployeesByArea((int) $areaSalomonCode);
                $salomonEmployees = collect($rows)->filter(
                    fn ($e) => !$jefeCedulas->contains($e->cedula)
                )->values();
                $usingSalomon = true;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Salomón connection failed (team)', ['error' => $e->getMessage()]);
                $salomonError = 'No fue posible obtener los datos de Salomón en este momento. Se muestran solo los datos locales.';
            }
        } elseif ($areaSalomonCode) {
            // Driver not installed — skip silently
        } else {
            $salomonError = 'El área no tiene código Salomón configurado.';
        }

        // 2) Usuarios locales del área (para enriquecer con evaluaciones / cargo)
        $localMembers = collect();
        if ($user->area_id) {
            $localMembers = User::where('is_active', true)
                ->where('area_id', $user->area_id)
                ->whereNotIn('id', $jefeIds)
                ->with([
                    'person',
                    'positionType',
                    'evaluationsAsEmployee' => fn ($q) => $q->with('template')->latest(),
                ])
                ->get()
                ->keyBy(fn ($u) => $u->person?->document_number);
        }

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
                'source'            => $localUser ? 'salomon+excel' : 'salomon',
                'cedula'            => $cedula,
                'nombre_completo'   => collect([
                    $emp->primer_nombre,
                    $emp->segundo_nombre ?? null,
                    $emp->primer_apellido,
                    $emp->segundo_apellido ?? null,
                ])->filter()->implode(' '),
                'primer_apellido'   => $emp->primer_apellido,
                'cargo'             => $emp->cargo_nombre ?? $localUser?->positionType?->name,
                'local_user'        => $localUser,
                'latest_evaluation' => $latestEval,
                'has_pending'       => $evals->where('status', 'pendiente')->count() > 0,
                'has_in_progress'   => $evals->where('status', 'en_progreso')->count() > 0,
                'total_evaluations' => $evals->count(),
                'completed_count'   => $evals->whereIn('status', ['completada', 'revisada'])->count(),
            ]);

            $seen[$cedula] = true;
        }

        // Luego, los locales (Excel) que NO aparecieron en Salomón
        foreach ($localMembers as $cedula => $member) {
            if ($cedula && isset($seen[$cedula])) {
                continue;
            }
            $evals      = $member->evaluationsAsEmployee ?? collect();
            $latestEval = $evals->first();
            $nombre     = $member->person
                ? trim(($member->person->first_name ?? '').' '.($member->person->last_name ?? ''))
                : ($member->name ?? 'Sin nombre');

            $teamData->push([
                'source'            => 'excel',
                'cedula'            => $cedula,
                'nombre_completo'   => $nombre !== '' ? $nombre : ($member->name ?? '—'),
                'primer_apellido'   => $member->person?->last_name,
                'cargo'             => $member->positionType?->name,
                'local_user'        => $member,
                'latest_evaluation' => $latestEval,
                'has_pending'       => $evals->where('status', 'pendiente')->count() > 0,
                'has_in_progress'   => $evals->where('status', 'en_progreso')->count() > 0,
                'total_evaluations' => $evals->count(),
                'completed_count'   => $evals->whereIn('status', ['completada', 'revisada'])->count(),
            ]);
        }

        $teamData = $teamData->sortBy(fn ($r) => mb_strtolower($r['primer_apellido'] ?? $r['nombre_completo']))->values();

        return view('jefe.team', compact('teamData', 'area', 'usingSalomon', 'salomonError', 'areaJefes'));
    }
}
