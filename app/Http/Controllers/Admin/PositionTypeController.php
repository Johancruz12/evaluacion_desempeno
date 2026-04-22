<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\PositionType;
use Illuminate\Http\Request;

class PositionTypeController extends Controller
{
    public function index()
    {
        $positionTypes = PositionType::with('area')->withCount('users')->latest()->get();
        $areas = Area::where('is_active', true)->get();
        return view('admin.position-types.index', compact('positionTypes', 'areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'area_id' => ['required', 'exists:areas,id'],
        ]);

        PositionType::create($data);
        return back()->with('success', 'Nuevo tipo de cargo registrado correctamente en el sistema.');
    }

    public function update(Request $request, PositionType $positionType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'area_id' => ['required', 'exists:areas,id'],
            'is_active' => ['boolean'],
        ]);

        $positionType->update($data);
        return back()->with('success', 'Tipo de cargo actualizado correctamente.');
    }

    public function destroy(PositionType $positionType)
    {
        $positionType->delete();
        return back()->with('success', 'Tipo de cargo eliminado del sistema correctamente.');
    }
}
