<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Evaluation;
use App\Models\EvaluationTemplate;
use App\Models\EvaluationSection;
use App\Models\EvaluationCriteria;
use App\Models\PositionType;
use App\Models\ScoringRange;
use App\Models\SectionType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationTemplateController extends Controller
{
    public function index()
    {
        $templates = EvaluationTemplate::with('positionType.area')
            ->withCount('sections', 'evaluations')
            ->latest()
            ->get();
        $areas = Area::where('is_active', true)->orderBy('name')->get();
        $positionTypes = PositionType::with('area')->where('is_active', true)->get();
        return view('admin.templates.index', compact('templates', 'positionTypes', 'areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'position_type_id' => ['nullable', 'exists:position_types,id'],
            'area_ids' => ['nullable', 'array'],
            'area_ids.*' => ['exists:areas,id'],
        ]);

        $template = EvaluationTemplate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'position_type_id' => $data['position_type_id'] ?? null,
        ]);

        if (!empty($data['area_ids'])) {
            $template->areas()->sync($data['area_ids']);
        }

        return redirect()->route('admin.templates.edit', $template)
            ->with('success', 'Plantilla de evaluación creada correctamente. Ya puedes configurar sus secciones y criterios.');
    }

    public function edit(EvaluationTemplate $template)
    {
        $template->load(['sections.criteria', 'scoringRanges', 'positionType.area', 'areas']);
        $positionTypes = PositionType::with('area')->where('is_active', true)->get();
        $areas = Area::where('is_active', true)->orderBy('name')->get();
        $sectionTypes = SectionType::activeOptions();

        // Count evaluations that don't match the current area configuration
        $templateAreaIds = $template->areas->pluck('id');
        if ($templateAreaIds->isEmpty()) {
            // Global template: any existing evaluations are potentially orphaned
            $orphanedCount = $template->evaluations()->count();
        } else {
            // Specific areas: orphaned = employees NOT in those areas
            $orphanedCount = $template->evaluations()
                ->whereHas('employee', fn ($q) => $q->whereNotIn('area_id', $templateAreaIds))
                ->count();
        }

        return view('admin.templates.edit', compact('template', 'positionTypes', 'areas', 'sectionTypes', 'orphanedCount'));
    }

    public function update(Request $request, EvaluationTemplate $template)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'instructions'     => ['nullable', 'string'],
            'score_scale'      => ['nullable', 'array'],
            'score_scale.*'    => ['nullable', 'string', 'max:500'],
            'position_type_id' => ['nullable', 'exists:position_types,id'],
            'is_active'        => ['boolean'],
            'area_ids'         => ['nullable', 'array'],
            'area_ids.*'       => ['exists:areas,id'],
        ]);

        // Build score_scale array (keys 1-5)
        $scaleInput = $data['score_scale'] ?? [];
        $scoreScale = null;
        if (!empty(array_filter($scaleInput))) {
            $scoreScale = [];
            for ($i = 1; $i <= 5; $i++) {
                $scoreScale[$i] = $scaleInput[$i] ?? '';
            }
        }

        $template->update([
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'instructions'     => $data['instructions'] ?? null,
            'score_scale'      => $scoreScale,
            'position_type_id' => $data['position_type_id'] ?? null,
            'is_active'        => $data['is_active'] ?? false,
        ]);

        $newAreaIds     = $data['area_ids'] ?? [];
        $oldAreaIds     = $template->areas()->pluck('areas.id')->all();
        $removedAreaIds = array_values(array_diff($oldAreaIds, $newAreaIds));

        $template->areas()->sync($newAreaIds);

        // Build the set of evaluation IDs to delete
        $toDelete = collect();

        // Case A: areas were explicitly removed in this save
        if (!empty($removedAreaIds)) {
            $toDelete = $toDelete->merge(
                Evaluation::where('template_id', $template->id)
                    ->whereHas('employee', fn ($q) => $q->whereIn('area_id', $removedAreaIds))
                    ->pluck('id')
            );
        }

        // Case B: template now has specific areas → also clean up any pre-existing
        // stale evaluations for employees whose area is NOT in the current list.
        // This handles orphaned evaluations from before this logic was in place.
        if (!empty($newAreaIds)) {
            $toDelete = $toDelete->merge(
                Evaluation::where('template_id', $template->id)
                    ->whereHas('employee', fn ($q) => $q->whereNotIn('area_id', $newAreaIds))
                    ->pluck('id')
            );
        }

        $toDelete = $toDelete->unique()->values()->all();

        if (!empty($toDelete)) {
            DB::transaction(function () use ($toDelete) {
                DB::table('evaluation_responses')->whereIn('evaluation_id', $toDelete)->delete();
                DB::table('development_plans')->whereIn('evaluation_id', $toDelete)->delete();
                DB::table('evaluation_notifications')->whereIn('evaluation_id', $toDelete)->delete();
                DB::table('evaluations')->whereIn('id', $toDelete)->delete();
            });
        }

        return back()->with('success', 'Plantilla de evaluación actualizada correctamente.');
    }

    /**
     * Explicitly delete all evaluations that don't match the template's current area configuration.
     * Used for cleaning up stale evaluations when the template is already in global mode.
     */
    public function cleanupParticipants(EvaluationTemplate $template)
    {
        $template->load('areas');
        $areaIds = $template->areas->pluck('id');

        if ($areaIds->isEmpty()) {
            // Template is global (no areas): remove ALL evaluations
            $toDelete = Evaluation::where('template_id', $template->id)->pluck('id')->all();
        } else {
            // Template has specific areas: remove evaluations for employees NOT in those areas
            $toDelete = Evaluation::where('template_id', $template->id)
                ->whereHas('employee', fn ($q) => $q->whereNotIn('area_id', $areaIds))
                ->pluck('id')->all();
        }

        if (empty($toDelete)) {
            return back()->with('info', 'No hay evaluaciones huérfanas que limpiar.');
        }

        DB::transaction(function () use ($toDelete) {
            DB::table('evaluation_responses')->whereIn('evaluation_id', $toDelete)->delete();
            DB::table('development_plans')->whereIn('evaluation_id', $toDelete)->delete();
            DB::table('evaluation_notifications')->whereIn('evaluation_id', $toDelete)->delete();
            DB::table('evaluations')->whereIn('id', $toDelete)->delete();
        });

        return back()->with('success', count($toDelete) . ' evaluación(es) eliminadas correctamente.');
    }

    public function destroy(EvaluationTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.templates.index')
            ->with('success', 'Plantilla eliminada del sistema correctamente.');
    }

    // === SECTIONS ===
    public function storeSection(Request $request, EvaluationTemplate $template)
    {
        $validSlugs = SectionType::map()->keys()->all();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:' . implode(',', $validSlugs)],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'content' => ['nullable', 'array'],
            'content.*' => ['nullable', 'string'],
        ]);

        $data['content'] = $this->normalizeContent($data['type'] ?? null, $data['content'] ?? null);
        $data['order'] = $template->sections()->count();
        $template->sections()->create($data);

        return back()->with('success', 'Nueva sección agregada a la plantilla correctamente.');
    }

    public function updateSection(Request $request, EvaluationSection $section)
    {
        $validSlugs = SectionType::map()->keys()->all();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:' . implode(',', $validSlugs)],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'content' => ['nullable', 'array'],
            'content.*' => ['nullable', 'string'],
        ]);

        $data['content'] = $this->normalizeContent($data['type'] ?? null, $data['content'] ?? null);
        $section->update($data);
        return back()->with('success', 'Sección actualizada correctamente.');
    }

    /**
     * Filter empty paragraphs and keep content only for sections with 'texto' behavior.
     */
    private function normalizeContent(?string $typeSlug, ?array $content): ?array
    {
        if (!$typeSlug || empty($content)) {
            return null;
        }
        $type = SectionType::map()->get($typeSlug);
        if (!$type || $type->behavior !== 'texto') {
            return null;
        }
        $clean = array_values(array_filter(
            array_map(fn ($p) => trim((string) $p), $content),
            fn ($p) => $p !== ''
        ));
        return $clean ?: null;
    }

    public function destroySection(EvaluationSection $section)
    {
        $section->delete();
        return back()->with('success', 'Sección y sus criterios eliminados correctamente.');
    }

    // === CRITERIA ===
    public function storeCriteria(Request $request, EvaluationSection $section)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['order'] = $section->criteria()->count();
        $section->criteria()->create($data);

        return back()->with('success', 'Nuevo criterio de evaluación agregado correctamente.');
    }

    public function updateCriteria(Request $request, EvaluationCriteria $criteria)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $criteria->update($data);
        return back()->with('success', 'Criterio de evaluación actualizado correctamente.');
    }

    public function destroyCriteria(EvaluationCriteria $criteria)
    {
        $criteria->delete();
        return back()->with('success', 'Criterio eliminado de la sección correctamente.');
    }

    // === SCORING RANGES ===
    public function storeRange(Request $request, EvaluationTemplate $template)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'min_score' => ['required', 'numeric', 'min:0'],
            'max_score' => ['required', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $data['order'] = $template->scoringRanges()->count();
        $template->scoringRanges()->create($data);

        return back()->with('success', 'Nuevo rango de puntuación agregado correctamente.');
    }

    public function updateRange(Request $request, ScoringRange $range)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'min_score' => ['required', 'numeric', 'min:0'],
            'max_score' => ['required', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $range->update($data);
        return back()->with('success', 'Rango de puntuación actualizado correctamente.');
    }

    public function destroyRange(ScoringRange $range)
    {
        $range->delete();
        return back()->with('success', 'Rango de puntuación eliminado correctamente.');
    }
}
