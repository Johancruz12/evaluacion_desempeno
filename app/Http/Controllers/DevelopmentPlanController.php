<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentPlan;
use App\Models\Evaluation;
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

        $data = $request->validate([
            'competencia'       => ['required', 'string', 'max:255'],
            'actividad'         => ['required', 'string', 'max:2000'],
            'responsable'       => ['required', 'string', 'max:255'],
            'fecha_seguimiento' => ['nullable', 'date'],
            'observaciones'     => ['nullable', 'string', 'max:2000'],
        ]);

        $data['evaluation_id'] = $evaluation->id;
        DevelopmentPlan::create($data);

        return back()->with('success', 'Actividad agregada al plan de desarrollo correctamente.');
    }

    public function update(Request $request, DevelopmentPlan $plan)
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isJefeArea()) {
            abort(403, 'No tienes permiso para modificar planes de desarrollo.');
        }

        $data = $request->validate([
            'competencia'       => ['required', 'string', 'max:255'],
            'actividad'         => ['required', 'string', 'max:2000'],
            'responsable'       => ['required', 'string', 'max:255'],
            'fecha_seguimiento' => ['nullable', 'date'],
            'observaciones'     => ['nullable', 'string', 'max:2000'],
        ]);

        $plan->update($data);

        return back()->with('success', 'Plan de desarrollo actualizado correctamente.');
    }

    public function destroy(DevelopmentPlan $plan)
    {
        $user = request()->user();
        if (!$user->isAdmin() && !$user->isJefeArea()) {
            abort(403, 'No tienes permiso para eliminar planes de desarrollo.');
        }

        $plan->delete();

        return back()->with('success', 'Actividad eliminada del plan de desarrollo correctamente.');
    }
}
