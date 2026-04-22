@extends('layouts.app')
@section('title', 'Reportes')

@section('content')
<div class="space-y-6">

    <div class="anim-slide-left flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center shadow-lg shadow-blue-500/25 flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Reportes de Evaluaciones — {{ $year }}</h1>
            <p class="text-slate-500 text-xs mt-0.5">Análisis y estadísticas del desempeño laboral</p>
        </div>
    </div>

    @if(session('success'))
        <div class="anim-fade-up flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.index') }}" class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Área</label>
                <select name="area_id" class="w-full border border-slate-200 rounded-xl text-sm px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white transition-all">
                    <option value="">Todas las áreas</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ ($filters['area_id'] ?? '') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Estado</label>
                <select name="status" class="w-full border border-slate-200 rounded-xl text-sm px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white transition-all">
                    <option value="">Todos los estados</option>
                    <option value="pendiente"   {{ ($filters['status'] ?? '') === 'pendiente'   ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_progreso" {{ ($filters['status'] ?? '') === 'en_progreso' ? 'selected' : '' }}>En progreso</option>
                    <option value="completada"  {{ ($filters['status'] ?? '') === 'completada'  ? 'selected' : '' }}>Completada</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tipo de período</label>
                <select name="period_type" class="w-full border border-slate-200 rounded-xl text-sm px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white transition-all">
                    <option value="">Todos</option>
                    <option value="trimestral" {{ ($filters['period_type'] ?? '') === 'trimestral' ? 'selected' : '' }}>Trimestral</option>
                    <option value="semestral"  {{ ($filters['period_type'] ?? '') === 'semestral'  ? 'selected' : '' }}>Semestral</option>
                    <option value="anual"      {{ ($filters['period_type'] ?? '') === 'anual'      ? 'selected' : '' }}>Anual</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Año</label>
                <input type="number" name="year" value="{{ $year }}" min="2020" max="{{ now()->year }}"
                       class="w-full border border-slate-200 rounded-xl text-sm px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white transition-all">
            </div>
        </div>
        <div class="flex flex-wrap justify-end mt-4 gap-2">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 min-w-[112px] whitespace-nowrap shrink-0 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 rounded-xl transition-colors border border-slate-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Limpiar
            </a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 min-w-[120px] whitespace-nowrap shrink-0 bg-white hover:bg-slate-50 border border-slate-900 text-slate-900 rounded-xl text-sm font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filtrar
            </button>
        </div>
    </form>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="anim-fade-up stat-card bg-white rounded-2xl border border-slate-200 p-5 text-center shadow-sm">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Total</p>
            <p class="count-anim text-3xl font-bold text-slate-800 mt-1">{{ $totalEvaluations }}</p>
        </div>
        <div class="anim-fade-up stat-card bg-white rounded-2xl border border-slate-200 p-5 text-center shadow-sm">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Completadas</p>
            <p class="count-anim text-3xl font-bold text-emerald-600 mt-1">{{ $completed }}</p>
        </div>
        <div class="anim-fade-up stat-card bg-white rounded-2xl border border-slate-200 p-5 text-center shadow-sm">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Pendientes</p>
            <p class="count-anim text-3xl font-bold text-amber-600 mt-1">{{ $pending + $inProgress }}</p>
        </div>
        <div class="anim-fade-up stat-card bg-white rounded-2xl border border-slate-200 p-5 text-center shadow-sm">
            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Puntaje promedio</p>
            <p class="count-anim text-3xl font-bold text-blue-600 mt-1">{{ $avgScore ? number_format((float)$avgScore, 1) : '—' }}</p>
        </div>
    </div>

    {{-- Breakdown by interpretation --}}
    @if(array_sum($breakdown) > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4">Distribución por resultado</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @php
                $bColors = [
                    'Sobrepasa las expectativas' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                    'Buen desempeño'             => 'bg-blue-50 border-blue-200 text-blue-700',
                    'Cumple las expectativas'    => 'bg-amber-50 border-amber-200 text-amber-700',
                    'Requiere mejora'            => 'bg-rose-50 border-rose-200 text-rose-700',
                ];
            @endphp
            @foreach($breakdown as $label => $count)
            <div class="anim-fade-up p-4 rounded-xl border text-center hover:-translate-y-1 transition-transform duration-300 shadow-sm {{ $bColors[$label] ?? 'border-slate-200' }}">
                <p class="text-2xl font-bold">{{ $count }}</p>
                <p class="text-xs font-medium mt-1">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Table --}}
    @if($evaluations->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14"/></svg>
            </div>
            <p class="text-slate-700 font-bold text-base">Sin resultados</p>
            <p class="text-slate-400 text-sm mt-1">No se encontraron evaluaciones con los filtros seleccionados</p>
        </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Detalle de evaluaciones ({{ $evaluations->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left font-semibold text-slate-500 text-xs uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-500 text-xs uppercase tracking-wider">Empleado</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-500 text-xs uppercase tracking-wider">Área / Cargo</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-500 text-xs uppercase tracking-wider">Período</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-500 text-xs uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-500 text-xs uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-500 text-xs uppercase tracking-wider">Auto</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-500 text-xs uppercase tracking-wider">Jefe</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-500 text-xs uppercase tracking-wider">Final</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-500 text-xs uppercase tracking-wider">Resultado</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-500 text-xs uppercase tracking-wider">Ver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($evaluations as $ev)
                    @php $interp = $ev->final_score ? $ev->getInterpretation() : null; @endphp
                    <tr class="row-hover">
                        <td class="px-4 py-3 text-slate-400 text-xs">#{{ $ev->id }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $ev->employee?->name ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-slate-600 text-xs">{{ $ev->employee?->area?->name ?? '—' }}</p>
                            <p class="text-slate-500 text-xs">{{ $ev->employee?->positionType?->name ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $ev->period }}</td>
                        <td class="px-4 py-3 text-center text-xs">
                            @if($ev->period_type) <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full font-semibold border border-blue-200">{{ ucfirst($ev->period_type) }}</span>
                            @else <span class="text-slate-400">—</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold border
                                {{ $ev->status === 'completada' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' :
                                   ($ev->status === 'en_progreso' ? 'bg-blue-100 text-blue-700 border-blue-200' :
                                   ($ev->status === 'revisada' ? 'bg-purple-100 text-purple-700 border-purple-200' : 'bg-amber-100 text-amber-700 border-amber-200')) }}">
                                {{ ['pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada','revisada'=>'Revisada'][$ev->status] ?? $ev->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600 font-medium">{{ $ev->total_auto_score ? number_format((float)$ev->total_auto_score,1) : '—' }}</td>
                        <td class="px-4 py-3 text-center text-slate-600 font-medium">{{ $ev->total_evaluator_score ? number_format((float)$ev->total_evaluator_score,1) : '—' }}</td>
                        <td class="px-4 py-3 text-center font-semibold {{ $interp ? ($interp['color'] === 'green' ? 'text-emerald-600' : ($interp['color'] === 'blue' ? 'text-blue-600' : ($interp['color'] === 'yellow' ? 'text-amber-600' : 'text-rose-600'))) : 'text-slate-400' }}">
                            {{ $ev->final_score ? number_format((float)$ev->final_score,1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-xs">
                            @if($interp)
                                <span class="font-semibold {{ $interp['color'] === 'green' ? 'text-emerald-600' : ($interp['color'] === 'blue' ? 'text-blue-600' : ($interp['color'] === 'yellow' ? 'text-amber-600' : 'text-rose-600')) }}">
                                    {{ $interp['label'] }}
                                </span>
                            @else <span class="text-slate-400">—</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('evaluations.show', $ev) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-100 hover:bg-blue-600 text-blue-600 hover:text-white transition-all duration-200 border border-blue-200 hover:border-blue-600" title="Ver evaluación">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
