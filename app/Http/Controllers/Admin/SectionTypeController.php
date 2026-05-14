<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SectionTypeController extends Controller
{
    public function index()
    {
        $types = SectionType::orderBy('order')->orderBy('id')->get();
        return view('admin.section-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Auto-generate slug from label if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['label'], '_');
        }

        // Ensure unique slug
        $base = $data['slug'];
        $i = 1;
        while (SectionType::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $base . '_' . (++$i);
        }

        $data['behavior']  = 'calificable'; // custom types are always calificable
        $data['is_system'] = false;
        $data['order']     = $data['order'] ?? (SectionType::max('order') + 1);

        SectionType::create($data);
        return back()->with('success', 'Tipo de sección creado correctamente.');
    }

    public function update(Request $request, SectionType $sectionType)
    {
        $data = $this->validateData($request, $sectionType->id);

        // System types: only allow label / appearance / order / is_active changes
        if ($sectionType->is_system) {
            unset($data['slug'], $data['behavior']);
        }

        $sectionType->update($data);
        return back()->with('success', 'Tipo de sección actualizado.');
    }

    public function destroy(SectionType $sectionType)
    {
        if ($sectionType->is_system) {
            return back()->withErrors(['general' => 'Los tipos del sistema no pueden eliminarse.']);
        }

        // Block delete if in use
        $inUse = \DB::table('evaluation_sections')->where('type', $sectionType->slug)->exists();
        if ($inUse) {
            return back()->withErrors(['general' => 'No se puede eliminar: hay secciones usando este tipo.']);
        }

        $sectionType->delete();
        return back()->with('success', 'Tipo de sección eliminado.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'slug'         => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', Rule::unique('section_types', 'slug')->ignore($ignoreId)],
            'label'        => ['required', 'string', 'max:120'],
            'icon'         => ['nullable', 'string', 'max:8'],
            'gradient'     => ['nullable', 'string', 'max:100'],
            'badge_class'  => ['nullable', 'string', 'max:100'],
            'border_class' => ['nullable', 'string', 'max:60'],
            'bg_class'     => ['nullable', 'string', 'max:60'],
            'is_active'    => ['nullable', 'boolean'],
            'order'        => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
