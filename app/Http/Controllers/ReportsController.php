<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        $filters = $request->only(['area_id', 'status', 'period_type', 'year']);
        $year    = $filters['year'] ?? now()->year;

        $query = Evaluation::with(['template', 'employee.person', 'employee.area', 'employee.positionType', 'evaluator.person'])
            ->whereYear('created_at', $year);

        if (!empty($filters['area_id'])) {
            $query->whereHas('employee', fn ($q) => $q->where('area_id', $filters['area_id']));
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['period_type'])) {
            $query->where('period_type', $filters['period_type']);
        }

        $evaluations = $query->latest()->get();

        // Stats
        $totalEvaluations  = $evaluations->count();
        $completed         = $evaluations->where('status', 'completada')->count();
        $pending           = $evaluations->where('status', 'pendiente')->count();
        $inProgress        = $evaluations->where('status', 'en_progreso')->count();

        $avgScore = $evaluations->whereNotNull('final_score')->avg('final_score');

        // Breakdown by interpretation
        $breakdown = ['Sobrepasa las expectativas' => 0, 'Buen desempeño' => 0, 'Cumple las expectativas' => 0, 'Requiere mejora' => 0];
        foreach ($evaluations->whereNotNull('final_score') as $ev) {
            $interp = $ev->getInterpretation();
            $breakdown[$interp['label']] = ($breakdown[$interp['label']] ?? 0) + 1;
        }

        $areas = Area::where('is_active', true)->get();

        return view('reports.index', compact(
            'evaluations', 'totalEvaluations', 'completed', 'pending', 'inProgress',
            'avgScore', 'breakdown', 'areas', 'filters', 'year'
        ));
    }
}
