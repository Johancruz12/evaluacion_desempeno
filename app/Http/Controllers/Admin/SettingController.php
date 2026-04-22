<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $pdfEnabled = Setting::bool('pdf_enabled', true);

        return view('admin.settings.index', compact('pdfEnabled'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pdf_enabled' => 'nullable|in:0,1',
        ]);

        Setting::set('pdf_enabled', $request->has('pdf_enabled') ? '1' : '0');

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}
