<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationCriteria;
use App\Models\EvaluationResponse;
use App\Models\EvaluationSection;
use Illuminate\Http\Request;

class EvaluationBuilderController extends Controller
{
    /** Verify the authenticated user can modify the evaluation's template structure. */
    private function authorizeBuilder(Evaluation $evaluation): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;
        if ($user->isJefeArea() && $evaluation->employee?->area_id === $user->area_id) return;
        abort(403, 'Sin permisos para editar esta evaluación.');
    }

    /** Reorder sections within the evaluation's template. */
    public function reorderSections(Request $request, Evaluation $evaluation)
    {
        $this->authorizeBuilder($evaluation);
        $data = $request->validate(['order' => ['required', 'array']]);

        foreach ($data['order'] as $i => $sectionId) {
            EvaluationSection::where('id', $sectionId)
                ->where('template_id', $evaluation->template_id)
                ->update(['order' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    /** Toggle a section's is_active flag in the template. */
    public function toggleSection(Request $request, Evaluation $evaluation, EvaluationSection $section)
    {
        $this->authorizeBuilder($evaluation);
        if ($section->template_id !== $evaluation->template_id) abort(403);

        $section->update(['is_active' => !$section->is_active]);

        return response()->json(['is_active' => $section->is_active]);
    }

    /** Add a new section to the evaluation's template. */
    public function addSection(Request $request, Evaluation $evaluation)
    {
        $this->authorizeBuilder($evaluation);
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['required', 'in:competencias_org,competencias_cargo,responsabilidades,rango'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Prevent duplicate sections with same name and type
        $existing = EvaluationSection::where('template_id', $evaluation->template_id)
            ->where('name', $data['name'])
            ->where('type', $data['type'])
            ->where('is_active', true)
            ->first();

        if ($existing) {
            return response()->json([
                'id'   => $existing->id,
                'name' => $existing->name,
                'type' => $existing->type,
                'description' => $existing->description,
                'order' => $existing->order,
                'duplicate' => true,
            ]);
        }

        $maxOrder = EvaluationSection::where('template_id', $evaluation->template_id)->max('order') ?? 0;

        $section = EvaluationSection::create([
            'template_id' => $evaluation->template_id,
            'name'        => $data['name'],
            'type'        => $data['type'],
            'description' => $data['description'] ?? null,
            'order'       => $maxOrder + 1,
            'is_active'   => true,
            'weight'      => 0,
        ]);

        return response()->json([
            'id'   => $section->id,
            'name' => $section->name,
            'type' => $section->type,
            'description' => $section->description,
            'order' => $section->order,
        ]);
    }

    /** Add a new criterion to a section (also creates the EvaluationResponse record). */
    public function addCriteria(Request $request, Evaluation $evaluation, EvaluationSection $section)
    {
        $this->authorizeBuilder($evaluation);
        if ($section->template_id !== $evaluation->template_id) abort(403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'max_score'   => ['nullable', 'numeric', 'min:1', 'max:10'],
        ]);

        // Prevent duplicate criteria with same name in the same section
        $existing = EvaluationCriteria::where('section_id', $section->id)
            ->where('name', $data['name'])
            ->where('is_active', true)
            ->first();

        if ($existing) {
            // Ensure the response row exists for this evaluation
            EvaluationResponse::firstOrCreate([
                'evaluation_id' => $evaluation->id,
                'criteria_id'   => $existing->id,
            ]);
            return response()->json([
                'id'          => $existing->id,
                'name'        => $existing->name,
                'description' => $existing->description,
                'max_score'   => $existing->max_score,
                'duplicate'   => true,
            ]);
        }

        $maxOrder = EvaluationCriteria::where('section_id', $section->id)->max('order') ?? 0;

        $criteria = EvaluationCriteria::create([
            'section_id'  => $section->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'max_score'   => $data['max_score'] ?? 5,
            'order'       => $maxOrder + 1,
            'is_active'   => true,
        ]);

        // Create the response row for this specific evaluation
        EvaluationResponse::firstOrCreate([
            'evaluation_id' => $evaluation->id,
            'criteria_id'   => $criteria->id,
        ]);

        return response()->json([
            'id'          => $criteria->id,
            'name'        => $criteria->name,
            'description' => $criteria->description,
            'max_score'   => $criteria->max_score,
        ]);
    }

    /** Soft-remove a criterion from the template (marks inactive, removes response). */
    public function removeCriteria(Request $request, Evaluation $evaluation, EvaluationCriteria $criteria)
    {
        $this->authorizeBuilder($evaluation);
        if ($criteria->section?->template_id !== $evaluation->template_id) abort(403);

        EvaluationResponse::where('evaluation_id', $evaluation->id)
            ->where('criteria_id', $criteria->id)
            ->delete();

        $criteria->update(['is_active' => false]);

        return response()->json(['ok' => true]);
    }

    /** AJAX individual score/comment save (all authenticated users). */
    public function saveScore(Request $request, Evaluation $evaluation, EvaluationCriteria $criteria)
    {
        $user = auth()->user();

        // Determinar si es jefe evaluando a un empleado de su área
        $isJefeEvaluatingEmployee = $user->isJefeArea() && !$user->isAdmin()
            && $evaluation->employee_id !== $user->id
            && $evaluation->employee?->area_id === $user->area_id;

        // Block saving on someone else's eval (unless admin or jefe evaluating their employee)
        if (!$user->isAdmin() && !$isJefeEvaluatingEmployee && $evaluation->employee_id !== $user->id) {
            abort(403, 'Solo puedes calificar tu propia evaluación.');
        }

        // Revisada = cerrada definitivamente para todos
        if ($evaluation->status === 'revisada') {
            return response()->json(['error' => 'Evaluación cerrada definitivamente.'], 422);
        }

        // Completada = el empleado finalizó su parte; solo admin/jefe puede seguir (para calificación del evaluador)
        if ($evaluation->status === 'completada' && $evaluation->employee_id === $user->id && !$user->isAdmin()) {
            return response()->json(['error' => 'Ya finalizaste tu autoevaluación.'], 422);
        }

        $data = $request->validate([
            'auto_score'      => ['nullable', 'numeric', 'min:1', 'max:5'],
            'evaluator_score' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'comment'         => ['nullable', 'string', 'max:1000'],
        ]);

        $response = EvaluationResponse::where('evaluation_id', $evaluation->id)
            ->where('criteria_id', $criteria->id)
            ->first();

        if (!$response) {
            return response()->json(['error' => 'Respuesta no encontrada.'], 404);
        }

        $updateData = [];

        // Any user can save auto_score on their own evaluation
        if ($evaluation->employee_id === $user->id) {
            if (array_key_exists('auto_score', $data)) {
                $updateData['auto_score'] = $data['auto_score'];
            }
        }

        // Admin y jefe de área pueden guardar evaluator_score y comentarios
        $canSaveEvaluatorScore = $user->isAdmin() || $isJefeEvaluatingEmployee;
        if ($canSaveEvaluatorScore) {
            if (array_key_exists('evaluator_score', $data)) {
                $updateData['evaluator_score'] = $data['evaluator_score'];
            }
            if (array_key_exists('comment', $data)) {
                $updateData['comment'] = $data['comment'];
            }
        }

        if (!empty($updateData)) {
            $response->update($updateData);
        }

        if ($evaluation->status === 'pendiente') {
            $evaluation->update(['status' => 'en_progreso']);
        }

        $evaluation->calculateScores();
        $evaluation->refresh();

        return response()->json([
            'ok'                    => true,
            'total_auto_score'      => (float) $evaluation->total_auto_score,
            'total_evaluator_score' => (float) $evaluation->total_evaluator_score,
            'final_score'           => (float) $evaluation->final_score,
        ]);
    }
}
