<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Evaluation;
use App\Models\User;
use App\Services\SalomonService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $data = ['user' => $user];

        if ($user->isAdmin()) {
            $data['totalEmployees']      = User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('slug', 'empleado'))->count();
            $data['totalAreas']          = Area::where('is_active', true)->count();
            $data['pendingEvaluations']  = Evaluation::where('status', 'pendiente')->count();
            $data['inProgressEvaluations'] = Evaluation::where('status', 'en_progreso')->count();
            $data['completedEvaluations'] = Evaluation::where('status', 'completada')->count();
            $data['recentEvaluations']   = Evaluation::with(['template', 'employee.person', 'evaluator.person'])
                ->latest()->take(8)->get();

            // Score distribution for current year (rows for dashboard: label, count, color)
            $yearEvals = Evaluation::whereYear('created_at', now()->year)->whereNotNull('final_score')->get();
            $data['scoreBreakdown'] = [
                [
                    'label' => 'Sobrepasa las expectativas',
                    'count' => $yearEvals->filter(fn ($e) => (float) $e->final_score >= 91)->count(),
                    'color' => '#22C55E',
                ],
                [
                    'label' => 'Buen desempeño',
                    'count' => $yearEvals->filter(fn ($e) => (float) $e->final_score >= 71 && (float) $e->final_score < 91)->count(),
                    'color' => '#3B82F6',
                ],
                [
                    'label' => 'Cumple las expectativas',
                    'count' => $yearEvals->filter(fn ($e) => (float) $e->final_score >= 50 && (float) $e->final_score < 71)->count(),
                    'color' => '#EAB308',
                ],
                [
                    'label' => 'Requiere mejora',
                    'count' => $yearEvals->filter(fn ($e) => (float) $e->final_score < 50)->count(),
                    'color' => '#EF4444',
                ],
            ];

        } elseif ($user->isJefeArea()) {
            $data['teamEvaluations'] = Evaluation::with(['template', 'employee.person'])
                ->whereHas('employee', fn ($q) => $q->where('area_id', $user->area_id))
                ->latest()->take(10)->get();
            $data['pendingCount']    = Evaluation::where('evaluator_id', $user->id)->where('status', 'pendiente')->count();
            $data['inProgressCount'] = Evaluation::where('evaluator_id', $user->id)->where('status', 'en_progreso')->count();
            $data['completedCount']  = Evaluation::where('evaluator_id', $user->id)->where('status', 'completada')->count();

            // Salomón team data
            $data = array_merge($data, $this->getSalomonTeamData($user));

        } else {
            $data['myEvaluations']   = $user->evaluationsAsEmployee()->with('template')->latest()->get();
            $data['pendingCount']    = $user->evaluationsAsEmployee()->where('status', 'pendiente')->count();
            $data['inProgressCount'] = $user->evaluationsAsEmployee()->where('status', 'en_progreso')->count();
            $data['completedCount']  = $user->evaluationsAsEmployee()->where('status', 'completada')->count();
        }

        return view('dashboard', $data);
    }

    /**
     * Build the "Equipo vigente desde Salomón" data for jefe dashboard.
     */
    private function getSalomonTeamData(User $user): array
    {
        $data = [
            'salomonTeam' => [],
            'salomonTeamSummary' => ['total' => 0, 'withEvaluation' => 0, 'withoutEvaluation' => 0, 'autoDone' => 0],
            'salomonSyncWarning' => null,
        ];

        $areaCodigo = $user->area?->salomon_codigo;

        if (!$areaCodigo) {
            $data['salomonSyncWarning'] = 'Tu área no tiene un código de Salomón configurado. Contacta al administrador.';
            return $data;
        }

        try {
            $salomonService = app(SalomonService::class);
            $employees = $salomonService->getActiveEmployeesByArea($areaCodigo);
        } catch (\Throwable $e) {
            $data['salomonSyncWarning'] = 'No se pudo conectar con Salomón: ' . $e->getMessage();
            return $data;
        }

        // Filter out the jefe themselves by cédula
        $jefeCedula = $user->person?->document_number;
        $employees = array_filter($employees, fn ($emp) => $emp->cedula !== $jefeCedula);
        $employees = array_values($employees);

        $team = [];
        $withEvaluation = 0;
        $withoutEvaluation = 0;
        $autoDone = 0;

        foreach ($employees as $emp) {
            // Find local user by cédula
            $localUser = User::whereHas('person', fn ($q) => $q->where('document_number', $emp->cedula))
                ->where('is_active', true)
                ->first();

            // Get latest evaluation for this employee
            $latestEval = null;
            if ($localUser) {
                $latestEval = Evaluation::where('employee_id', $localUser->id)
                    ->latest()
                    ->first();
            }

            $hasEval = $latestEval !== null;
            $autoCompleted = $hasEval && $latestEval->hasCompletedAutoEvaluation();

            if ($hasEval) {
                $withEvaluation++;
                if ($autoCompleted) {
                    $autoDone++;
                }
            } else {
                $withoutEvaluation++;
            }

            $team[] = [
                'nombre_completo' => trim(($emp->primer_nombre ?? '') . ' ' . ($emp->segundo_nombre ?? '') . ' ' . ($emp->primer_apellido ?? '') . ' ' . ($emp->segundo_apellido ?? '')),
                'cedula' => $emp->cedula,
                'cargo_nombre' => $emp->cargo_nombre,
                'has_evaluation' => $hasEval,
                'evaluation_status' => $latestEval?->status,
                'evaluation_id' => $latestEval?->id,
                'auto_done' => $autoCompleted,
            ];
        }

        $data['salomonTeam'] = $team;
        $data['salomonTeamSummary'] = [
            'total' => count($team),
            'withEvaluation' => $withEvaluation,
            'withoutEvaluation' => $withoutEvaluation,
            'autoDone' => $autoDone,
        ];

        return $data;
    }
}
