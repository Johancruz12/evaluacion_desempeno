<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\EvaluationTemplate;
use App\Models\EvaluationSection;
use App\Models\EvaluationCriteria;
use App\Models\PositionType;
use App\Models\ScoringRange;
use Illuminate\Http\Request;

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
        return view('admin.templates.edit', compact('template', 'positionTypes', 'areas'));
    }

    public function update(Request $request, EvaluationTemplate $template)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'position_type_id' => ['nullable', 'exists:position_types,id'],
            'is_active' => ['boolean'],
            'area_ids' => ['nullable', 'array'],
            'area_ids.*' => ['exists:areas,id'],
        ]);

        $template->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'position_type_id' => $data['position_type_id'] ?? null,
            'is_active' => $data['is_active'] ?? false,
        ]);

        $template->areas()->sync($data['area_ids'] ?? []);

        return back()->with('success', 'Plantilla de evaluación actualizada correctamente.');
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:competencias_org,competencias_cargo,responsabilidades,rango'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['order'] = $template->sections()->count();
        $template->sections()->create($data);

        return back()->with('success', 'Nueva sección agregada a la plantilla correctamente.');
    }

    public function updateSection(Request $request, EvaluationSection $section)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:competencias_org,competencias_cargo,responsabilidades,rango'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $section->update($data);
        return back()->with('success', 'Sección actualizada correctamente.');
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
