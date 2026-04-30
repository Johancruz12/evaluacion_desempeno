<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Services\SalomonService;
use Illuminate\Http\Request;

class JefeController extends Controller
{
    public function __construct(private SalomonService $salomon) {}

    /**
     * Mi Equipo — visible to jefe_area users.
     * Pulls team members from Salomón (by area salomon_codigo) and
     * cross-references with the local DB to show evaluation status.
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
        $jefeCedula      = $user->person?->document_number;

        $teamData = collect();

        if ($areaSalomonCode) {
            try {
                $salomonEmployees = $this->salomon->getActiveEmployeesByArea((int) $areaSalomonCode);

                // Exclude the jefe themselves from the list
                $salomonEmployees = array_filter(
                    $salomonEmployees,
                    fn ($e) => $e->cedula !== $jefeCedula
                );

                $cedulas = collect($salomonEmployees)->pluck('cedula')->values()->toArray();

                $localUsers = User::with([
                    'person',
                    'positionType',
                    'evaluationsAsEmployee' => fn ($q) => $q->with('template')->latest(),
                ])
                    ->whereHas('person', fn ($q) => $q->whereIn('document_number', $cedulas))
                    ->get()
                    ->keyBy(fn ($u) => $u->person?->document_number);

                $teamData = collect($salomonEmployees)->map(function ($emp) use ($localUsers) {
                    $localUser  = $localUsers->get($emp->cedula);
                    $evals      = $localUser?->evaluationsAsEmployee ?? collect();
                    $latestEval = $evals->first();

                    return [
                        'source'           => 'salomon',
                        'cedula'           => $emp->cedula,
                        'nombre_completo'  => collect([
                            $emp->primer_nombre,
                            $emp->segundo_nombre ?? null,
                            $emp->primer_apellido,
                            $emp->segundo_apellido ?? null,
                        ])->filter()->implode(' '),
                        'primer_apellido'  => $emp->primer_apellido,
                        'cargo'            => $emp->cargo_nombre,
                        'local_user'       => $localUser,
                        'latest_evaluation'=> $latestEval,
                        'has_pending'      => $evals->where('status', 'pendiente')->count() > 0,
                        'has_in_progress'  => $evals->where('status', 'en_progreso')->count() > 0,
                        'total_evaluations'=> $evals->count(),
                        'completed_count'  => $evals->whereIn('status', ['completada', 'revisada'])->count(),
                    ];
                })->sortBy('primer_apellido')->values();

                $usingSalomon = true;
            } catch (\Exception $e) {
                // Fall through to local-DB fallback
            }
        }

        // Fallback: use local DB if Salomón not configured or failed
        if ($teamData->isEmpty()) {
            $localMembers = User::where('is_active', true)
                ->where('area_id', $user->area_id)
                ->where('id', '!=', $user->id)
                ->with([
                    'person',
                    'positionType',
                    'evaluationsAsEmployee' => fn ($q) => $q->with('template')->latest(),
                ])
                ->get();

            $teamData = $localMembers->map(function ($member) {
                $evals      = $member->evaluationsAsEmployee ?? collect();
                $latestEval = $evals->first();

                return [
                    'source'           => 'local',
                    'cedula'           => $member->person?->document_number,
                    'nombre_completo'  => $member->name,
                    'primer_apellido'  => $member->person?->last_name,
                    'cargo'            => $member->positionType?->name,
                    'local_user'       => $member,
                    'latest_evaluation'=> $latestEval,
                    'has_pending'      => $evals->where('status', 'pendiente')->count() > 0,
                    'has_in_progress'  => $evals->where('status', 'en_progreso')->count() > 0,
                    'total_evaluations'=> $evals->count(),
                    'completed_count'  => $evals->whereIn('status', ['completada', 'revisada'])->count(),
                ];
            })->sortBy('primer_apellido')->values();
        }

        return view('jefe.team', compact('teamData', 'area', 'usingSalomon'));
    }

    /**
     * Admin overview — all Salomón jefes/coordinadores who have subordinates,
     * grouped by area, with evaluation progress per jefe.
     */
    public function overview(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        try {
            $jefes = collect($this->salomon->getJefesWithEmployees())
                ->filter(fn ($j) => $j->total_empleados > 0);
            $salomonError = null;
        } catch (\Throwable $e) {
            $jefes = collect();
            $salomonError = 'No se pudo conectar a Salomón: ' . $e->getMessage();
        }

        // Load all local users indexed by document_number
        $allLocalUsers = User::with([
            'person',
            'area',
            'evaluationsAsEmployee',
        ])
            ->whereHas('person')
            ->get()
            ->keyBy(fn ($u) => $u->person?->document_number);

        // Load local areas indexed by salomon_codigo
        $areasMap = Area::where('is_active', true)->get()->keyBy('salomon_codigo');

        $jefesData = $jefes->map(function ($jefe) use ($allLocalUsers, $areasMap) {
            $localUser = $allLocalUsers->get($jefe->cedula);
            $localArea = $areasMap->get($jefe->area_codigo);
            $jefeEvals = $localUser?->evaluationsAsEmployee ?? collect();

            return [
                'cedula'         => $jefe->cedula,
                'nombre'         => collect([
                    $jefe->primer_nombre,
                    $jefe->segundo_nombre ?? null,
                    $jefe->primer_apellido,
                    $jefe->segundo_apellido ?? null,
                ])->filter()->implode(' '),
                'primer_apellido'=> $jefe->primer_apellido,
                'cargo'          => $jefe->cargo_nombre,
                'area_nombre'    => $jefe->area_nombre,
                'area_codigo'    => $jefe->area_codigo,
                'total_empleados'=> $jefe->total_empleados,
                'local_user'     => $localUser,
                'local_area'     => $localArea,
                'jefe_eval_count'=> $jefeEvals->count(),
                'jefe_completed' => $jefeEvals->whereIn('status', ['completada', 'revisada'])->count(),
            ];
        })->sortBy('area_nombre')->groupBy('area_nombre');

        return view('admin.jefes-overview', compact('jefesData', 'salomonError'));
    }
}
