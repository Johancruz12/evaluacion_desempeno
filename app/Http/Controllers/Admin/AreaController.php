<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::withCount('positionTypes', 'users')->latest()->get();
        return view('admin.areas.index', compact('areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Area::create($data);
        return back()->with('success', 'Nueva área registrada correctamente en el sistema.');
    }

    public function update(Request $request, Area $area)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $area->update($data);
        return back()->with('success', 'Área actualizada correctamente.');
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return back()->with('success', 'Área eliminada del sistema correctamente.');
    }
}
