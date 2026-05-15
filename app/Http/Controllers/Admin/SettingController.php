<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $pdfEnabled           = Setting::bool('pdf_enabled', true);
        $defaultPeriodType    = Setting::get('default_period_type', 'trimestral');
        $defaultAudience      = Setting::get('default_target_audience', 'todos');

        return view('admin.settings.index', compact('pdfEnabled', 'defaultPeriodType', 'defaultAudience'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pdf_enabled'              => 'nullable|in:0,1',
            'default_period_type'      => 'nullable|in:trimestral,semestral,anual',
            'default_target_audience'  => 'nullable|in:todos,empleados,jefes',
        ]);

        Setting::set('pdf_enabled',              $request->has('pdf_enabled') ? '1' : '0');
        Setting::set('default_period_type',      $request->input('default_period_type', 'trimestral'));
        Setting::set('default_target_audience',  $request->input('default_target_audience', 'todos'));

        return back()->with('success', 'Configuración guardada correctamente.');
    }
}
