<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    /** Add a single period manually. */
    public function store(Request $request)
    {
        $request->validate([
            'label' => ['required', 'string', 'max:20', 'unique:periods,label'],
            'type'  => ['required', 'in:trimestral,semestral,anual'],
            'year'  => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $maxOrder = Period::where('year', $request->year)->max('sort_order') ?? 0;

        Period::create([
            'label'      => strtoupper(trim($request->label)),
            'type'       => $request->type,
            'year'       => (int) $request->year,
            'sort_order' => $maxOrder + 1,
            'is_active'  => true,
        ]);

        return back()->with('success', "Período \"{$request->label}\" agregado.");
    }

    /** Delete a period (only if no evaluations use it). */
    public function destroy(Period $period)
    {
        $inUse = \App\Models\Evaluation::where('period', $period->label)->exists();
        if ($inUse) {
            return back()->withErrors(['period_delete' => "No se puede eliminar \"{$period->label}\" porque hay evaluaciones que lo usan. Desactívalo en su lugar."]);
        }

        $label = $period->label;
        $period->delete();

        return back()->with('success', "Período \"{$label}\" eliminado.");
    }

    /** Toggle a period active/inactive. */
    public function toggle(Period $period)
    {
        $period->update(['is_active' => ! $period->is_active]);
        $state = $period->is_active ? 'activado' : 'desactivado';

        return back()->with('success', "Período \"{$period->label}\" {$state}.");
    }

    /** Auto-generate all periods for a given year + type. */
    public function generate(Request $request)
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'type' => ['required', 'in:trimestral,semestral,anual'],
        ]);

        $count = Period::generateForYear((int) $request->year, $request->type);

        if ($count === 0) {
            return back()->with('info', "Todos los períodos para {$request->year} ({$request->type}) ya existen.");
        }

        return back()->with('success', "{$count} período(s) generado(s) para {$request->year}.");
    }
}
