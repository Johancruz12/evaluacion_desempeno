<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentPlan;
use App\Models\Evaluation;
use App\Models\EvaluationNotification;
use Illuminate\Http\Request;

class DevelopmentPlanController extends Controller
{
    public function store(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();
        // Solo admin o jefes pueden agregar planes de desarrollo
        if (!$user->isAdmin() && !$user->isJefeArea()) {
            abort(403, 'No tienes permiso para agregar planes de desarrollo.');
        }

        // Regla: el empleado debe haber completado su autoevaluación primero
        if (!$evaluation->hasCompletedAutoEvaluation()) {
            return back()->withErrors(['general' => 'El empleado debe completar su autoevaluación antes de registrar el plan de desarrollo.']);
        }

        $data = $request->validate([
            'competencia'       => ['required', 'string', 'max:255'],
            'actividad'         => ['required', 'string', 'max:2000'],
            'responsable'       => ['required', 'string', 'max:255'],
            'fecha_seguimiento' => ['nullable', 'date'],
            'observaciones'     => ['nullable', 'string', 'max:2000'],
        ]);

        $data['evaluation_id'] = $evaluation->id;
        DevelopmentPlan::create($data);

        $this->notifyEmployee($evaluation, $user, 'plan_added',
            'Nuevo ítem en tu plan de desarrollo',
            "{$user->name} agregó la actividad \"{$data['competencia']}\" al plan de desarrollo de tu evaluación \"{$evaluation->template->name}\"."
        );

        return back()->with('success', 'Actividad agregada al plan de desarrollo correctamente.');
    }

    public function update(Request $request, DevelopmentPlan $plan)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isJefeArea()) {
            abort(403, 'No tienes permiso para modificar planes de desarrollo.');
        }

        // Regla: el empleado debe haber completado su autoevaluación primero
        if ($plan->evaluation && !$plan->evaluation->hasCompletedAutoEvaluation()) {
            return back()->withErrors(['general' => 'El empleado debe completar su autoevaluación antes de modificar el plan de desarrollo.']);
        }

        $data = $request->validate([
            'competencia'       => ['required', 'string', 'max:255'],
            'actividad'         => ['required', 'string', 'max:2000'],
            'responsable'       => ['required', 'string', 'max:255'],
            'fecha_seguimiento' => ['nullable', 'date'],
            'observaciones'     => ['nullable', 'string', 'max:2000'],
        ]);

        $plan->update($data);

        if ($plan->evaluation) {
            $this->notifyEmployee($plan->evaluation, $user, 'plan_updated',
                'Plan de desarrollo actualizado',
                "{$user->name} modificó la actividad \"{$plan->competencia}\" en tu plan de desarrollo."
            );
        }

        return back()->with('success', 'Plan de desarrollo actualizado correctamente.');
    }

    public function destroy(DevelopmentPlan $plan)
    {
        $user = request()->user();
        if (!$user->isAdmin() && !$user->isJefeArea()) {
            abort(403, 'No tienes permiso para eliminar planes de desarrollo.');
        }

        $evaluation = $plan->evaluation;
        $competencia = $plan->competencia;

        $plan->delete();

        if ($evaluation) {
            $this->notifyEmployee($evaluation, $user, 'plan_removed',
                'Ítem eliminado de tu plan de desarrollo',
                "{$user->name} eliminó la actividad \"{$competencia}\" de tu plan de desarrollo."
            );
        }

        return back()->with('success', 'Actividad eliminada del plan de desarrollo correctamente.');
    }

    /** Crea una notificación dirigida al empleado dueño de la evaluación. */
    private function notifyEmployee(Evaluation $evaluation, $actor, string $type, string $title, string $message): void
    {
        if (!$evaluation->employee_id || $evaluation->employee_id === $actor->id) {
            return;
        }

        EvaluationNotification::create([
            'user_id'       => $evaluation->employee_id,
            'evaluation_id' => $evaluation->id,
            'type'          => $type,
            'title'         => $title,
            'message'       => $message,
        ]);
    }
}
