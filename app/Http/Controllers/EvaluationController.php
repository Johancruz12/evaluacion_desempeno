<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentPlan;
use App\Models\Area;
use App\Models\Evaluation;
use App\Models\EvaluationNotification;
use App\Models\EvaluationResponse;
use App\Models\EvaluationTemplate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Evaluation::with(['template', 'employee.person', 'employee.area', 'evaluator.person']);

        if ($user->isAdmin()) {
            // admin sees all
        } elseif ($user->isJefeArea()) {
            $query->where(function ($q) use ($user) {
                $q->where('evaluator_id', $user->id)
                  ->orWhereHas('employee', fn ($q2) => $q2->where('area_id', $user->area_id));
            });
        } else {
            $query->where('employee_id', $user->id);
        }

        // Filter by area
        if ($request->filled('area_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('area_id', $request->area_id));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $evaluations = $query->latest()->get();

        // Group evaluations by template → area → employees
        $groupedEvaluations = $evaluations->groupBy(function ($evaluation) {
            return $evaluation->template_id . '|||' . $evaluation->template?->name;
        })->map(function ($templateGroup, $templateKey) {
            $parts = explode('|||', $templateKey);
            return [
                'template_id' => $parts[0],
                'template_name' => $parts[1] ?? 'Sin plantilla',
                'areas' => $templateGroup->groupBy(function ($evaluation) {
                    return $evaluation->employee?->area_id . '|||' . $evaluation->employee?->area?->name;
                })->map(function ($areaGroup, $areaKey) {
                    $areaParts = explode('|||', $areaKey);
                    return [
                        'area_id' => $areaParts[0],
                        'area_name' => $areaParts[1] ?? 'Sin área',
                        'evaluations' => $areaGroup->sortBy('employee.person.first_name')->values()
                    ];
                })->values()
            ];
        })->values();

        // Areas for filter
        $areas = Area::where('is_active', true)->orderBy('name')->get();

        // Templates for creation (only for authorized users)
        $templates = collect();
        if ($user->canCreateEvaluations()) {
            $templates = EvaluationTemplate::where('is_active', true)
                ->with(['positionType', 'areas'])
                ->withCount(['sections', 'evaluations'])
                ->get();
        }

        // Templates for management (only for authorized users)
        $templatesManage = collect();
        if ($user->canEditEvaluationTemplates()) {
            $templatesManage = EvaluationTemplate::with('areas')
                ->withCount(['sections', 'evaluations'])
                ->get();
        }

        // Jefe team data — show assigned employees and their evaluation status
        $teamData = collect();
        if ($user->isJefeArea()) {
            $teamMembers = User::where('is_active', true)
                ->where('area_id', $user->area_id)
                ->where('id', '!=', $user->id)
                ->with(['person', 'positionType', 'evaluationsAsEmployee' => function ($q) {
                    $q->with('template')->latest();
                }])
                ->get();

            $teamData = $teamMembers->map(function ($member) {
                $latestEval = $member->evaluationsAsEmployee->first();
                return [
                    'user' => $member,
                    'latest_evaluation' => $latestEval,
                    'has_pending' => $member->evaluationsAsEmployee->where('status', 'pendiente')->count() > 0,
                    'has_in_progress' => $member->evaluationsAsEmployee->where('status', 'en_progreso')->count() > 0,
                    'total_evaluations' => $member->evaluationsAsEmployee->count(),
                    'completed_count' => $member->evaluationsAsEmployee->where('status', 'completada')->count(),
                ];
            });
        }

        // Jefe's own evaluations
        $myEvaluations = collect();
        if ($user->isJefeArea()) {
            $myEvaluations = Evaluation::where('employee_id', $user->id)
                ->with(['template', 'responses'])
                ->latest()
                ->get();
        }

        $areasForCreate = Area::where('is_active', true)->orderBy('name')->get();

        return view('evaluations.index', compact('evaluations', 'groupedEvaluations', 'areas', 'templates', 'areasForCreate', 'templatesManage', 'teamData', 'myEvaluations'));
    }

    public function create(Request $request)
    {
        // Redirect to the unified evaluations page (crear tab is handled client-side)
        return redirect()->route('evaluations.index');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->canCreateEvaluations()) { abort(403); }

        $data = $request->validate([
            'template_id'       => ['required', 'exists:evaluation_templates,id'],
            'period'            => ['required', 'string', 'max:100'],
            'period_type'       => ['required', 'in:trimestral,semestral,anual'],
            'evaluation_date'   => ['nullable', 'date'],
            'target_audience'   => ['nullable', 'in:todos,empleados,jefes'],
        ]);

        $template = EvaluationTemplate::with(['areas', 'sections.criteria'])->find($data['template_id']);

        // Use areas assigned to the template; if global (no areas), use all active areas
        $selectedAreaIds = $template->areas->pluck('id');
        if ($selectedAreaIds->isEmpty()) {
            $selectedAreaIds = Area::where('is_active', true)->pluck('id');
        }

        $year = (int) substr($data['period'], 0, 4);
        if ($year < (int) now()->year) {
            return back()->withErrors(['period' => 'No se permiten períodos de años anteriores.'])->withInput();
        }

        $employees = User::where('is_active', true)
            ->whereIn('area_id', $selectedAreaIds);

        $audience = $data['target_audience'] ?? 'todos';
        if ($audience === 'jefes') {
            $employees->whereHas('roles', fn ($q) => $q->where('slug', 'jefe_area'));
        } elseif ($audience === 'empleados') {
            $employees->whereHas('roles', fn ($q) => $q->where('slug', 'empleado'));
        }

        $employees = $employees->get();

        if ($employees->isEmpty()) {
            return back()->withErrors([
                'template_id' => 'No hay empleados activos para evaluar en las áreas asignadas a esta plantilla.',
            ])->withInput();
        }

        $template->load('sections.criteria');

        $createdCount = 0;
        $skippedCount = 0;
        $lastEvaluationId = null;

        foreach ($employees as $employee) {
            $alreadyExists = Evaluation::where('template_id', $data['template_id'])
                ->where('employee_id', $employee->id)
                ->where('period', $data['period'])
                ->where('period_type', $data['period_type'])
                ->exists();

            if ($alreadyExists) {
                $skippedCount++;
                continue;
            }

            $evaluation = Evaluation::create([
                'template_id' => $data['template_id'],
                'employee_id' => $employee->id,
                'evaluator_id' => $user->id,
                'period' => $data['period'],
                'period_type' => $data['period_type'],
                'evaluation_date' => $data['evaluation_date'] ?? null,
                'status' => 'pendiente',
                'admission_date' => $employee?->created_at?->toDateString(),
            ]);

            foreach ($template->sections as $section) {
                if (!$section->is_active) continue;
                foreach ($section->criteria as $criteria) {
                    if (!$criteria->is_active) continue;
                    EvaluationResponse::create([
                        'evaluation_id' => $evaluation->id,
                        'criteria_id'   => $criteria->id,
                    ]);
                }
            }

            $createdCount++;
            $lastEvaluationId = $evaluation->id;

            // Notification: evaluation assigned to employee
            $this->notify($employee->id, $evaluation->id, 'evaluation_created',
                'Nueva evaluación asignada',
                "Se te ha asignado la evaluación \"{$template->name}\" para el período {$data['period']}."
            );

            // Notification: if employee has jefe, notify jefe
            if ($employee->area_id) {
                $jefe = User::where('area_id', $employee->area_id)
                    ->where('id', '!=', $employee->id)
                    ->whereHas('roles', fn ($q) => $q->where('slug', 'jefe_area'))
                    ->first();
                if ($jefe) {
                    $this->notify($jefe->id, $evaluation->id, 'boss_review',
                        'Evaluación asignada a tu equipo',
                        "{$employee->name} tiene una nueva evaluación \"{$template->name}\" por completar."
                    );
                }
            }
        }

        if ($createdCount === 0) {
            return redirect()->route('evaluations.index')
                ->with('success', 'No fue necesario crear evaluaciones nuevas — todas ya existían para el período seleccionado.');
        }

        $message = "Se crearon {$createdCount} evaluaciones exitosamente para las áreas seleccionadas.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} ya existían y se omitieron.";
        }

        return redirect()->route('evaluations.index')
            ->with('success', $message);
    }

    public function show(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();

        // Verificar acceso jerárquicamente: Admin > Jefe > Empleado
        $this->authorizeEvaluationAccess($user, $evaluation);

        $evaluation->load([
            'template.sections.criteria',
            'template.scoringRanges',
            'responses.criteria.section',
            'employee.person',
            'employee.area',
            'employee.positionType',
            'evaluator.person',
            'developmentPlans',
        ]);

        return view('evaluations.show', compact('evaluation'));
    }

    /**
     * Verifica si el usuario tiene permiso para ver una evaluación.
     * Orden de verificación: Admin > Jefe de Área > Empleado
     * Un usuario con múltiples roles se verifica por el rol más alto.
     */
    private function authorizeEvaluationAccess($user, Evaluation $evaluation): void
    {
        // 1. Admin (Director RH) puede ver TODO
        if ($user->isAdmin()) {
            return;
        }

        // 2. Jefe de área puede ver:
        //    - Su propia evaluación
        //    - Evaluaciones de empleados en su área
        //    - Evaluaciones donde es el evaluador asignado
        if ($user->isJefeArea()) {
            if ($evaluation->employee_id === $user->id) {
                return; // Su propia evaluación
            }
            if ($evaluation->employee?->area_id === $user->area_id) {
                return; // Empleado de su área
            }
            if ($evaluation->evaluator_id === $user->id) {
                return; // Es el evaluador asignado
            }
            abort(403, 'Solo puedes ver evaluaciones de empleados en tu área.');
        }

        // 3. Empleado (sin rol jefe) solo puede ver su propia evaluación
        if ($evaluation->employee_id !== $user->id) {
            abort(403, 'Solo puedes ver tu propia evaluación.');
        }
    }

    public function saveResponses(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();

        // Verificar acceso a la evaluación
        $this->authorizeEvaluationAccess($user, $evaluation);

        // Determinar si el usuario es jefe evaluando a un empleado de su área
        $isJefeEvaluatingEmployee = $user->isJefeArea() && !$user->isAdmin()
            && $evaluation->employee_id !== $user->id
            && $evaluation->employee?->area_id === $user->area_id;

        // Revisada = cerrada definitivamente para todos
        if ($evaluation->status === 'revisada') {
            return back()->withErrors(['general' => 'Esta evaluación ya fue revisada y cerrada definitivamente.']);
        }

        // Completada = el empleado ya finalizó; solo el admin o jefe evaluador puede continuar
        $isOwnEmployee = $evaluation->employee_id === $user->id;
        if ($evaluation->status === 'completada' && $isOwnEmployee && !$user->isAdmin()) {
            return back()->withErrors(['general' => 'Ya finalizaste tu autoevaluación. No puedes modificarla.']);
        }

        $request->validate([
            'responses'                   => ['required', 'array'],
            'responses.*.criteria_id'     => ['required', 'exists:evaluation_criteria,id'],
            'responses.*.auto_score'      => ['nullable', 'numeric', 'min:1', 'max:5'],
            'responses.*.evaluator_score' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'responses.*.comment'         => ['nullable', 'string', 'max:1000'],
        ]);

        // Admin y jefe solo pueden guardar evaluator_score si el empleado completó la autoevaluación
        $canSaveEvaluatorScore = $user->isAdmin() || $isJefeEvaluatingEmployee;
        if ($canSaveEvaluatorScore && !$evaluation->hasCompletedAutoEvaluation()) {
            $hasEvaluatorScore = collect($request->responses)->contains(fn ($r) => !empty($r['evaluator_score']));
            if ($hasEvaluatorScore) {
                return back()->withErrors(['general' => 'El empleado debe completar su autoevaluación antes de que puedas calificarlo.']);
            }
        }

        foreach ($request->responses as $responseData) {
            $response = EvaluationResponse::where('evaluation_id', $evaluation->id)
                ->where('criteria_id', $responseData['criteria_id'])
                ->first();
            if (!$response) continue;

            $updateData = [];

            // Any user can save auto_score on their own evaluation
            if ($evaluation->employee_id === $user->id) {
                if (array_key_exists('auto_score', $responseData)) {
                    $updateData['auto_score'] = $responseData['auto_score'];
                }
            }

            // Admin y jefe de área pueden guardar evaluator_score y comentarios
            if ($canSaveEvaluatorScore) {
                if (array_key_exists('evaluator_score', $responseData)) {
                    $updateData['evaluator_score'] = $responseData['evaluator_score'];
                }
                if (array_key_exists('comment', $responseData)) {
                    $updateData['comment'] = $responseData['comment'];
                }
            }

            if (!empty($updateData)) { $response->update($updateData); }
        }

        if ($evaluation->status === 'pendiente') {
            $evaluation->update(['status' => 'en_progreso']);
        }
        $evaluation->calculateScores();

        // Si el empleado ya llenó todos los criterios, bloquear la evaluación automáticamente
        if ($isOwnEmployee && !$user->isAdmin() && $evaluation->hasCompletedAutoEvaluation()) {
            $evaluation->update(['status' => 'completada']);
            $evaluation->calculateScores();

            // Notify admin(s) and jefe that employee completed
            $this->notifyAdminsAndJefe($evaluation, 'employee_completed',
                'Autoevaluación finalizada',
                "{$evaluation->employee->name} ha completado su autoevaluación \"{$evaluation->template->name}\"."
            );

            return back()->with('success', '¡Autoevaluación completada con éxito! Tu jefe y el área de RR.HH. han sido notificados.');
        }

        // Si el jefe acaba de calificar, notificar al admin
        if ($isJefeEvaluatingEmployee) {
            return back()->with('success', 'Calificaciones del jefe guardadas correctamente.');
        }

        $pendingCount = $evaluation->responses()->whereNull('auto_score')->count();
        if ($pendingCount > 0) {
            return back()->with('info', "Progreso guardado correctamente. Aún te faltan {$pendingCount} criterio(s) por calificar para completar tu evaluación.");
        }

        return back()->with('success', 'Calificaciones guardadas correctamente.');
    }

    public function saveObservations(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();

        // Solo admins y jefes pueden guardar observaciones
        if (!$user->isAdmin() && !$user->isJefeArea()) {
            abort(403, 'No tienes permiso para agregar observaciones.');
        }

        // Verificar acceso a la evaluación
        $this->authorizeEvaluationAccess($user, $evaluation);

        $data = $request->validate([
            'obs_organizacional'    => ['nullable', 'string', 'max:2000'],
            'obs_cargo'             => ['nullable', 'string', 'max:2000'],
            'obs_responsabilidades' => ['nullable', 'string', 'max:2000'],
            'observations'          => ['nullable', 'string', 'max:2000'],
        ]);

        $evaluation->update($data);

        return back()->with('success', 'Observaciones actualizadas correctamente en la evaluación.');
    }

    public function complete(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();
        if (!$user->canCreateEvaluations()) { abort(403); }

        if ($evaluation->status !== 'completada') {
            return back()->withErrors(['general' => 'El empleado debe finalizar su autoevaluación antes de cerrar la evaluación.']);
        }

        if (!$evaluation->hasCompletedAutoEvaluation()) {
            return back()->withErrors(['general' => 'El empleado debe completar todos los criterios antes de cerrar la evaluación.']);
        }

        $evaluation->calculateScores();
        $evaluation->update(['status' => 'revisada']);

        // Notify employee and jefe that evaluation was closed by RH
        $this->notify($evaluation->employee_id, $evaluation->id, 'rh_closed',
            'Evaluación cerrada por RR.HH.',
            "Tu evaluación \"{$evaluation->template->name}\" ha sido revisada y cerrada definitivamente."
        );
        if ($evaluation->employee?->area_id) {
            $jefe = User::where('area_id', $evaluation->employee->area_id)
                ->where('id', '!=', $evaluation->employee_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'jefe_area'))
                ->first();
            if ($jefe) {
                $this->notify($jefe->id, $evaluation->id, 'rh_closed',
                    'Evaluación cerrada por RR.HH.',
                    "La evaluación de {$evaluation->employee->name} ha sido cerrada definitivamente."
                );
            }
        }

        return back()->with('success', 'Evaluación cerrada definitivamente. El empleado y su jefe han sido notificados.');
    }

    /**
     * Retorna el estado actual de la evaluación en JSON para polling en tiempo real.
     */
    public function liveState(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();
        $this->authorizeEvaluationAccess($user, $evaluation);

        $evaluation->load('responses');

        $scores = $evaluation->responses->mapWithKeys(function ($r) {
            return [$r->criteria_id => [
                'auto'      => $r->auto_score      !== null ? (int) $r->auto_score      : null,
                'evaluator' => $r->evaluator_score !== null ? (int) $r->evaluator_score : null,
            ]];
        });

        return response()->json([
            'status'                => $evaluation->status,
            'total_auto_score'      => (float) ($evaluation->total_auto_score      ?? 0),
            'total_evaluator_score' => (float) ($evaluation->total_evaluator_score ?? 0),
            'final_score'           => (float) ($evaluation->final_score           ?? 0),
            'scores'                => $scores,
        ]);
    }

    public function exportPdf(Evaluation $evaluation)
    {
        $user = auth()->user();

        // Verificar que PDF esté habilitado en configuración
        if (!\App\Models\Setting::bool('pdf_enabled', true)) {
            abort(403, 'La descarga de PDF está deshabilitada por el administrador.');
        }

        // Verificar acceso usando la misma lógica jerárquica
        $this->authorizeEvaluationAccess($user, $evaluation);

        $evaluation->load([
            'template.sections.criteria',
            'template.scoringRanges',
            'employee.person',
            'employee.area',
            'employee.positionType',
            'evaluator.person',
            'evaluator.positionType',
            'responses',
            'developmentPlans',
        ]);

        $pdf = Pdf::loadView('evaluations.pdf', compact('evaluation'))
            ->setPaper('letter', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $employeeName = str_replace(' ', '_', $evaluation->employee?->name ?? 'evaluacion');
        $filename = "Evaluacion_{$employeeName}_{$evaluation->period}.pdf";

        return $pdf->download($filename);
    }

    public function reopen(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();
        if (!$user->canCreateEvaluations()) { abort(403); }

        if (!in_array($evaluation->status, ['completada', 'revisada'])) {
            return back()->withErrors(['general' => 'Solo se pueden reabrir evaluaciones completadas o revisadas.']);
        }

        $data = $request->validate([
            'reopen_reason'   => ['required', 'string', 'min:10', 'max:500'],
            'reopen_deadline' => ['required', 'date', 'after:today'],
        ]);

        $evaluation->update([
            'status'          => 'en_progreso',
            'reopen_reason'   => $data['reopen_reason'],
            'reopen_deadline' => $data['reopen_deadline'],
            'reopened_at'     => now(),
            'reopened_by'     => $user->id,
        ]);

        // Notify employee
        $this->notify($evaluation->employee_id, $evaluation->id, 'evaluation_reopened',
            'Evaluación reabierta',
            "Tu evaluación \"{$evaluation->template->name}\" fue reabierta. Motivo: {$data['reopen_reason']}. Fecha límite: {$data['reopen_deadline']}."
        );

        // Notify jefe
        if ($evaluation->employee?->area_id) {
            $jefe = User::where('area_id', $evaluation->employee->area_id)
                ->where('id', '!=', $evaluation->employee_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'jefe_area'))
                ->first();
            if ($jefe) {
                $this->notify($jefe->id, $evaluation->id, 'evaluation_reopened',
                    'Evaluación reabierta',
                    "La evaluación de {$evaluation->employee->name} fue reabierta por RR.HH. Fecha límite: {$data['reopen_deadline']}."
                );
            }
        }

        return back()->with('success', 'Evaluación reabierta correctamente. El empleado ha sido notificado con la nueva fecha límite.');
    }

    public function resetBulk(Request $request)
    {
        $user = $request->user();
        if (!$user->canCreateEvaluations()) { abort(403); }

        $data = $request->validate([
            'evaluation_ids'   => ['required', 'array', 'min:1'],
            'evaluation_ids.*' => ['required', 'integer', 'exists:evaluations,id'],
        ]);

        $evaluations = Evaluation::whereIn('id', $data['evaluation_ids'])->get();

        $resetCount = 0;
        foreach ($evaluations as $evaluation) {
            $this->resetEvaluationData($evaluation);

            $this->notify($evaluation->employee_id, $evaluation->id, 'evaluation_reset',
                'Evaluación reiniciada por RR.HH.',
                "Tu evaluación \"{$evaluation->template->name}\" fue reiniciada por RR.HH. y quedó disponible nuevamente para diligenciar desde cero."
            );

            if ($evaluation->employee?->area_id) {
                $jefe = User::where('area_id', $evaluation->employee->area_id)
                    ->where('id', '!=', $evaluation->employee_id)
                    ->whereHas('roles', fn ($q) => $q->where('slug', 'jefe_area'))
                    ->first();

                if ($jefe) {
                    $this->notify($jefe->id, $evaluation->id, 'evaluation_reset',
                        'Evaluación reiniciada por RR.HH.',
                        "La evaluación de {$evaluation->employee->name} fue reiniciada y quedó nuevamente en estado pendiente."
                    );
                }
            }

            $resetCount++;
        }

        return back()->with('success', "Se reiniciaron {$resetCount} evaluación(es) correctamente. Los empleados han sido notificados.");
    }

    private function resetEvaluationData(Evaluation $evaluation): void
    {
        foreach ($evaluation->responses as $response) {
            $response->update([
                'auto_score' => null,
                'evaluator_score' => null,
                'comment' => null,
            ]);
        }

        $evaluation->update([
            'status' => 'pendiente',
            'observations' => null,
            'obs_organizacional' => null,
            'obs_cargo' => null,
            'obs_responsabilidades' => null,
            'reopen_reason' => null,
            'reopen_deadline' => null,
            'reopened_at' => null,
            'reopened_by' => null,
            'total_auto_score' => null,
            'total_evaluator_score' => null,
            'final_score' => null,
        ]);

        $evaluation->developmentPlans()->delete();
    }

    /**
     * Create a notification record.
     */
    private function notify(int $userId, int $evaluationId, string $type, string $title, string $message): void
    {
        EvaluationNotification::create([
            'user_id'       => $userId,
            'evaluation_id' => $evaluationId,
            'type'          => $type,
            'title'         => $title,
            'message'       => $message,
        ]);
    }

    /**
     * Notify all admins (director_rh) and the employee's jefe.
     */
    private function notifyAdminsAndJefe(Evaluation $evaluation, string $type, string $title, string $message): void
    {
        // Notify admins
        $admins = User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('slug', 'director_rh'))
            ->where('id', '!=', $evaluation->employee_id)
            ->get();

        foreach ($admins as $admin) {
            $this->notify($admin->id, $evaluation->id, $type, $title, $message);
        }

        // Notify jefe
        if ($evaluation->employee?->area_id) {
            $jefe = User::where('area_id', $evaluation->employee->area_id)
                ->where('id', '!=', $evaluation->employee_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'jefe_area'))
                ->whereNotIn('id', $admins->pluck('id'))
                ->first();
            if ($jefe) {
                $this->notify($jefe->id, $evaluation->id, $type, $title, $message);
            }
        }
    }
}
