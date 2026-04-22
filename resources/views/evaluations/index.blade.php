@extends('layouts.app')
@section('title', 'Evaluaciones')

@section('content')
@php
$user = auth()->user();
$canCreate = $user->canCreateEvaluations();
$canManageTemplates = $user->canEditEvaluationTemplates();
$isJefe = $user->isJefeArea();
$statusMap = [
    'pendiente'   => ['Pendiente',   'bg-amber-100 text-amber-700 border-amber-200'],
    'en_progreso' => ['En progreso', 'bg-blue-100 text-blue-700 border-blue-200'],
    'completada'  => ['Completada',  'bg-emerald-100 text-emerald-700 border-emerald-200'],
    'revisada'    => ['Revisada',    'bg-purple-100 text-purple-700 border-purple-200'],
];
$totalEvaluations = $evaluations->count();
$countPending   = $evaluations->where('status', 'pendiente')->count();
$countProgress  = $evaluations->where('status', 'en_progreso')->count();
$countCompleted = $evaluations->whereIn('status', ['completada','revisada'])->count();
@endphp

<div x-data="{ tab: '{{ $isJefe ? 'equipo' : ($canCreate && old('template_id') ? 'crear' : 'lista') }}' }" class="space-y-6">

    {{-- Header --}}
    <div class="anim-slide-left">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center shadow-lg shadow-blue-500/25 flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Evaluaciones</h1>
                <p class="text-slate-500 text-xs mt-0.5">Gestión integral del desempeño laboral</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 anim-fade-up">
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 stat-card">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $totalEvaluations }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider">Total</p></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 stat-card">
            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center"><svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $countPending }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider">Pendientes</p></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 stat-card">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $countProgress }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider">En progreso</p></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 stat-card">
            <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $countCompleted }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider">Completadas</p></div>
        </div>
    </div>

    {{-- Tab buttons --}}
    <div class="flex gap-1 bg-white rounded-2xl p-1.5 border border-slate-200 shadow-sm w-fit anim-fade-up flex-wrap">
        @if($isJefe)
        <button @click="tab='equipo'" :class="tab==='equipo' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Mi equipo
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="tab==='equipo' ? 'bg-white/25' : 'bg-blue-100 text-blue-700'">{{ $teamData->count() }}</span>
            </span>
        </button>
        @endif

        <button @click="tab='lista'" :class="tab==='lista' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                {{ $isJefe ? 'Todas' : 'Evaluaciones' }}
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="tab==='lista' ? 'bg-white/25' : 'bg-blue-100 text-blue-700'">{{ $totalEvaluations }}</span>
            </span>
        </button>

        @if($canCreate)
        <button @click="tab='crear'" :class="tab==='crear' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Asignar evaluación
            </span>
        </button>
        @endif

        @if($canManageTemplates)
        <button @click="tab='plantillas'" :class="tab==='plantillas' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                Plantillas
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="tab==='plantillas' ? 'bg-white/25' : 'bg-blue-100 text-blue-700'">{{ $templatesManage->count() }}</span>
            </span>
        </button>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- TAB: MI EQUIPO (Jefe dashboard)                       --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if($isJefe)
    <div x-show="tab==='equipo'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">

        {{-- My own evaluation --}}
        @if($myEvaluations->isNotEmpty())
        <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400"></div>
            <div class="px-5 py-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Mi evaluación</h3>
                        <p class="text-xs text-slate-500">Completa tu autoevaluación aquí</p>
                    </div>
                </div>
                @foreach($myEvaluations as $myEval)
                @php [$msl, $msc] = $statusMap[$myEval->status] ?? ['—','bg-slate-100 text-slate-500 border-slate-200']; @endphp
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">{{ $myEval->template?->name ?? 'Sin plantilla' }}</p>
                            <p class="text-xs text-slate-400">{{ $myEval->period }} · {{ $myEval->period_type }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($myEval->total_auto_score)
                        <span class="text-base font-extrabold {{ $myEval->total_auto_score >= 91 ? 'text-emerald-600' : ($myEval->total_auto_score >= 71 ? 'text-blue-600' : ($myEval->total_auto_score >= 50 ? 'text-amber-600' : 'text-rose-600')) }}">
                            {{ number_format($myEval->total_auto_score, 1) }}
                        </span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $msc }}">{{ $msl }}</span>
                        <a href="{{ route('evaluations.show', $myEval) }}"
                           class="btn-bounce inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            Llenar
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Team members list --}}
        <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center text-white shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Empleados de mi área</h3>
                            <p class="text-xs text-slate-500">{{ $user->area?->name ?? 'Sin área' }} · {{ $teamData->count() }} miembro(s)</p>
                        </div>
                    </div>
                    {{-- Progress summary --}}
                    @php
                    $teamWithEvals = $teamData->filter(fn($t) => $t['total_evaluations'] > 0);
                    $teamCompleted = $teamData->filter(fn($t) => $t['completed_count'] > 0);
                    $progressPercent = $teamData->count() > 0 ? round(($teamCompleted->count() / $teamData->count()) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $teamCompleted->count() }}/{{ $teamData->count() }}
                        </span>
                    </div>
                </div>

                {{-- Progress bar --}}
                @if($teamData->isNotEmpty())
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-slate-500">Progreso del equipo</span>
                        <span class="text-xs font-extrabold {{ $progressPercent >= 80 ? 'text-emerald-600' : ($progressPercent >= 40 ? 'text-blue-600' : 'text-amber-600') }}">{{ $progressPercent }}%</span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $progressPercent >= 80 ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : ($progressPercent >= 40 ? 'bg-gradient-to-r from-blue-400 to-sky-500' : 'bg-gradient-to-r from-amber-400 to-orange-500') }}" style="width: {{ $progressPercent }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">{{ $teamCompleted->count() }} de {{ $teamData->count() }} empleados han finalizado su evaluación</p>
                </div>
                @endif

                @if($teamData->isEmpty())
                <div class="py-8 text-center">
                    <p class="text-slate-400 text-sm">No tienes empleados asignados en tu área</p>
                </div>
                @else
                <div class="space-y-1">
                    @foreach($teamData->sortByDesc('has_pending') as $member)
                    @php
                    $latestEval = $member['latest_evaluation'];
                    $memberUser = $member['user'];
                    @endphp
                    <div class="flex items-center justify-between px-3 py-3 rounded-xl hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm
                                {{ $member['completed_count'] > 0 ? 'bg-gradient-to-br from-emerald-400 to-blue-500' : ($member['has_in_progress'] ? 'bg-gradient-to-br from-blue-400 to-indigo-500' : ($member['has_pending'] ? 'bg-gradient-to-br from-amber-400 to-orange-500' : 'bg-gradient-to-br from-slate-300 to-slate-400')) }}">
                                {{ strtoupper(substr($memberUser->person?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($memberUser->person?->last_name ?? '', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $memberUser->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $memberUser->positionType?->name ?? 'Sin cargo' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($member['total_evaluations'] === 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border bg-slate-50 text-slate-500 border-slate-200">Sin evaluación</span>
                            @else
                                @php
                                $isFinalized = in_array($latestEval?->status, ['completada', 'revisada']);
                                $teamStatusLabel = $isFinalized ? 'Finalizado' : 'En proceso';
                                $teamStatusClass = $isFinalized
                                    ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                    : 'bg-blue-100 text-blue-700 border-blue-200';
                                @endphp
                                @if($latestEval && ($latestEval->final_score || $latestEval->total_auto_score))
                                @php $sc = $latestEval->final_score ?? $latestEval->total_auto_score; @endphp
                                <span class="text-sm font-extrabold {{ $sc >= 91 ? 'text-emerald-600' : ($sc >= 71 ? 'text-blue-600' : ($sc >= 50 ? 'text-amber-600' : 'text-rose-600')) }}">
                                    {{ number_format($sc, 1) }}
                                </span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $teamStatusClass }}">{{ $teamStatusLabel }}</span>
                            @endif

                            @if($latestEval)
                            <a href="{{ route('evaluations.export-pdf', $latestEval) }}"
                               class="w-8 h-8 rounded-xl bg-rose-100 border border-rose-200 hover:bg-gradient-to-r hover:from-rose-500 hover:to-pink-500 flex items-center justify-center text-rose-600 hover:text-white hover:border-rose-500 transition-all duration-300 shadow-sm opacity-0 group-hover:opacity-100"
                               title="Descargar PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            <a href="{{ route('evaluations.show', $latestEval) }}"
                               class="w-8 h-8 rounded-xl bg-blue-100 border border-blue-200 hover:bg-gradient-to-r hover:from-blue-500 hover:to-sky-500 flex items-center justify-center text-blue-600 hover:text-white hover:border-blue-500 transition-all duration-300 shadow-sm opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- TAB: LISTA DE EVALUACIONES                            --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div x-show="tab==='lista'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         x-data="{ selected: [], selectAll: false, toggleAll() { if(this.selectAll) { this.selected = [...document.querySelectorAll('.eval-checkbox')].map(c => c.value); } else { this.selected = []; } }, toggleOne(id) { const idx = this.selected.indexOf(id); if(idx > -1) { this.selected.splice(idx,1); this.selectAll=false; } else { this.selected.push(id); } } }">

        {{-- Area filter (only for admins and jefes) --}}
        @if($user->isAdmin() || $isJefe)
        <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
            <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
            <div class="p-4">
                <form method="GET" action="{{ route('evaluations.index') }}" class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                    <div class="flex-1 min-w-0 w-full sm:w-auto">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Filtrar por área</label>
                        <select name="area_id" class="w-full sm:w-64 px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                            <option value="">Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-0 w-full sm:w-auto">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Filtrar por estado</label>
                        <select name="status" class="w-full sm:w-48 px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_progreso" {{ request('status') == 'en_progreso' ? 'selected' : '' }}>En progreso</option>
                            <option value="completada" {{ request('status') == 'completada' ? 'selected' : '' }}>Completada</option>
                            <option value="revisada" {{ request('status') == 'revisada' ? 'selected' : '' }}>Revisada</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-bounce inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-600 text-white font-semibold px-5 py-3 rounded-xl transition-all text-sm border border-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Filtrar
                        </button>
                        @if(request('area_id') || request('status'))
                        <a href="{{ route('evaluations.index') }}" class="inline-flex items-center gap-1.5 px-4 py-3 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 rounded-xl transition-colors border border-slate-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Limpiar
                        </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Bulk action bar --}}
        @if($canCreate)
        <div x-show="selected.length > 0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak
             class="anim-fade-up bg-amber-50 rounded-2xl border border-amber-200 shadow-sm overflow-hidden mb-5">
            <div class="flex items-center justify-between px-5 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-900"><span x-text="selected.length"></span> evaluación(es) seleccionada(s)</p>
                        <p class="text-xs text-amber-700">Se reiniciarán a estado pendiente y se limpiarán sus respuestas.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="selected=[]; selectAll=false; document.querySelectorAll('.eval-checkbox').forEach(c=>c.checked=false)"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 rounded-xl transition-colors border border-slate-300 shadow-sm">
                        Cancelar
                    </button>
                    <form method="POST" action="{{ route('evaluations.bulk-reset') }}" id="bulk-reset-form"
                          onsubmit="return confirm('⚠️ ¿Reiniciar las evaluaciones seleccionadas?\n\nSe eliminarán todas las respuestas y calificaciones, y las evaluaciones volverán a estado pendiente. Los empleados deberán completarlas nuevamente.\n\nEsta acción no se puede deshacer.')">
                        @csrf
                        @method('PATCH')
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="evaluation_ids[]" :value="id">
                        </template>
                        <button type="submit"
                                class="btn-bounce inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm border border-amber-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Resetear seleccionadas
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Grouped Evaluations --}}
        <div class="space-y-4">
            @forelse($groupedEvaluations as $templateGroup)
            <div x-data="{ openTemplate: true }" class="anim-fade-up bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
                <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
                <button @click="openTemplate=!openTemplate" class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="text-left">
                            <h3 class="font-bold text-slate-800 text-sm">{{ $templateGroup['template_name'] }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ collect($templateGroup['areas'])->sum(fn($a) => $a['evaluations']->count()) }} evaluación(es) en {{ count($templateGroup['areas']) }} área(s)</p>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300" :class="openTemplate ? 'bg-blue-100 text-blue-600 rotate-180' : 'text-slate-400'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>
                <div x-show="openTemplate" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2">
                    @foreach($templateGroup['areas'] as $areaGroup)
                    <div x-data="{ openArea: true }" class="border-t border-slate-100">
                        <button @click="openArea=!openArea" class="w-full flex items-center justify-between px-5 py-3 bg-gradient-to-r from-slate-50 to-white hover:from-slate-100 hover:to-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-semibold text-slate-700 text-sm">{{ $areaGroup['area_name'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $areaGroup['evaluations']->count() }} empleado(s)</p>
                                </div>
                            </div>
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center transition-all duration-300" :class="openArea ? 'text-slate-600 rotate-180' : 'text-slate-400'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                        <div x-show="openArea" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            @foreach($areaGroup['evaluations'] as $ev)
                            @php
                            [$slabel, $sclass] = $statusMap[$ev->status] ?? ['—','bg-slate-100 text-slate-500 border-slate-200'];
                            $score = $ev->final_score ?? $ev->total_auto_score;
                            $scoreColor = $score >= 91 ? 'text-emerald-600' : ($score >= 71 ? 'text-blue-600' : ($score >= 50 ? 'text-amber-600' : 'text-rose-600'));
                            $periodTypeLabels = ['trimestral'=>'Trim.','semestral'=>'Sem.','anual'=>'Anual'];
                            $ptLabel = $periodTypeLabels[$ev->period_type] ?? $ev->period_type;
                            $isOwn = $ev->employee_id === auth()->id();
                            @endphp
                            <div class="flex items-center justify-between px-8 py-3 border-b border-slate-50 last:border-0 hover:bg-blue-50/30 transition-colors group {{ $isOwn ? 'bg-indigo-50/30' : '' }}">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    @if($canCreate)
                                    <input type="checkbox" class="eval-checkbox w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 flex-shrink-0"
                                           value="{{ $ev->id }}" @change="toggleOne('{{ $ev->id }}')" :checked="selected.includes('{{ $ev->id }}')">
                                    @endif
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $isOwn ? 'from-indigo-400 to-purple-500' : 'from-blue-400 to-sky-500' }} flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm">
                                        {{ strtoupper(substr($ev->employee?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate">
                                            {{ $ev->employee?->name ?? '—' }}
                                            @if($isOwn)<span class="text-indigo-500 text-xs font-bold">(Tú)</span>@endif
                                        </p>
                                        <p class="text-xs text-slate-400">{{ $ev->period }} · {{ $ptLabel }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    @if($score)
                                    <span class="hidden sm:inline-flex text-base font-extrabold {{ $scoreColor }}">{{ number_format($score,1) }}</span>
                                    @endif
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $sclass }}">{{ $slabel }}</span>
                                                <a href="{{ route('evaluations.export-pdf', $ev) }}"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-rose-100 border border-rose-200 text-rose-700 hover:bg-rose-600 hover:border-rose-600 hover:text-white transition-all duration-200 shadow-sm"
                                       title="Descargar PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                    <a href="{{ route('evaluations.show', $ev) }}"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl {{ $isOwn ? 'bg-indigo-100 border border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:border-indigo-600' : 'bg-blue-100 border border-blue-200 text-blue-700 hover:bg-blue-600 hover:border-blue-600' }} hover:text-white transition-all duration-200 shadow-sm">
                                        @if($isOwn)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        @endif
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
                <div class="py-16 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
                    </div>
                    <p class="text-slate-700 font-bold text-base">No hay evaluaciones</p>
                    <p class="text-slate-400 text-sm mt-1">Aún no se han creado evaluaciones de desempeño</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- TAB: ASIGNAR EVALUACIÓN (simplified — no area select) --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if($canCreate)
    <div x-show="tab==='crear'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="max-w-3xl">
            <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400"></div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-800">Asignar evaluación</h2>
                            <p class="text-slate-500 text-xs mt-0.5">Selecciona una plantilla y el período. Las áreas se toman de la plantilla.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('evaluations.store') }}" x-data="{selectedTpl: null, audience: 'todos', templates: {{ Js::from($templates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'area_names' => $t->areas->pluck('name')->toArray(), 'is_global' => $t->areas->isEmpty(), 'sections_count' => $t->sections_count])) }}}" class="space-y-6">
                        @csrf

                        {{-- Step 1: Template --}}
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-r from-blue-500 to-sky-500 flex items-center justify-center text-white text-xs font-bold shadow-md">1</div>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Plantilla de evaluación</label>
                            </div>
                            <select name="template_id" required
                                    x-on:change="selectedTpl = templates.find(t => t.id === parseInt($event.target.value)) || null"
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                                <option value="">Seleccionar plantilla…</option>
                                @foreach($templates as $t)
                                <option value="{{ $t->id }}" {{ old('template_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                    @if($t->areas->count())
                                        ({{ $t->areas->pluck('name')->take(2)->join(', ') }}{{ $t->areas->count() > 2 ? ' +'.($t->areas->count()-2) : '' }})
                                    @else
                                        (Global — todas las áreas)
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Areas info (read-only, shows which areas will get the evaluation) --}}
                        <div x-show="selectedTpl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="bg-gradient-to-r from-blue-50 to-sky-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm font-bold text-blue-800">Áreas que serán evaluadas</p>
                                </div>
                                <div x-show="selectedTpl && selectedTpl.is_global" class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945"/></svg>
                                        Global — Todas las áreas activas
                                    </span>
                                </div>
                                <div x-show="selectedTpl && !selectedTpl.is_global" class="flex flex-wrap gap-1.5">
                                    <template x-for="area in (selectedTpl?.area_names || [])" :key="area">
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200" x-text="area"></span>
                                    </template>
                                </div>
                                <p class="text-xs text-blue-600 mt-2">
                                    <strong>Todos</strong> los empleados activos de estas áreas recibirán la evaluación automáticamente.
                                </p>
                            </div>
                        </div>

                        {{-- Step 1.5: Target audience --}}
                        <div x-show="selectedTpl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-r from-violet-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold shadow-md">2</div>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">¿A quién se asigna la evaluación?</label>
                            </div>
                            <input type="hidden" name="target_audience" :value="audience">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="relative cursor-pointer" @click="audience='todos'">
                                    <div :class="audience==='todos' ? 'border-violet-400 bg-violet-50 shadow-md shadow-violet-100' : 'border-slate-200 hover:border-violet-200'"
                                         class="rounded-xl border-2 p-4 transition-all duration-200">
                                        <div class="flex items-center gap-3">
                                            <div :class="audience==='todos' ? 'bg-violet-500' : 'bg-slate-200'" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/></svg>
                                            </div>
                                            <div>
                                                <p :class="audience==='todos' ? 'text-violet-800' : 'text-slate-700'" class="text-sm font-bold">Todos</p>
                                                <p class="text-xs text-slate-400">Empleados y jefes</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer" @click="audience='empleados'">
                                    <div :class="audience==='empleados' ? 'border-blue-400 bg-blue-50 shadow-md shadow-blue-100' : 'border-slate-200 hover:border-blue-200'"
                                         class="rounded-xl border-2 p-4 transition-all duration-200">
                                        <div class="flex items-center gap-3">
                                            <div :class="audience==='empleados' ? 'bg-blue-500' : 'bg-slate-200'" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </div>
                                            <div>
                                                <p :class="audience==='empleados' ? 'text-blue-800' : 'text-slate-700'" class="text-sm font-bold">Solo empleados</p>
                                                <p class="text-xs text-slate-400">Sin jefes / coordinadores</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer" @click="audience='jefes'">
                                    <div :class="audience==='jefes' ? 'border-indigo-400 bg-indigo-50 shadow-md shadow-indigo-100' : 'border-slate-200 hover:border-indigo-200'"
                                         class="rounded-xl border-2 p-4 transition-all duration-200">
                                        <div class="flex items-center gap-3">
                                            <div :class="audience==='jefes' ? 'bg-indigo-500' : 'bg-slate-200'" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138"/></svg>
                                            </div>
                                            <div>
                                                <p :class="audience==='jefes' ? 'text-indigo-800' : 'text-slate-700'" class="text-sm font-bold">Solo jefes</p>
                                                <p class="text-xs text-slate-400">Jefes y coordinadores</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Step 3: Period --}}
                        <div x-show="selectedTpl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-r from-blue-500 to-sky-500 flex items-center justify-center text-white text-xs font-bold shadow-md">3</div>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Período de evaluación</label>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Tipo</label>
                                    <select name="period_type" id="period-type" required onchange="updatePeriods()"
                                            class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                                        <option value="">Tipo de período…</option>
                                        <option value="trimestral" {{ old('period_type')=='trimestral'?'selected':'' }}>Trimestral</option>
                                        <option value="semestral"  {{ old('period_type')=='semestral'?'selected':'' }}>Semestral</option>
                                        <option value="anual"      {{ old('period_type')=='anual'?'selected':'' }}>Anual</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Período</label>
                                    <select name="period" id="period-select" required
                                            class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                                        <option value="">— elige tipo primero —</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Date --}}
                        <div x-show="selectedTpl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-r from-slate-300 to-slate-400 flex items-center justify-center text-white text-xs font-bold">4</div>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Fecha <span class="text-slate-400 font-normal">(opcional)</span></label>
                            </div>
                            <input type="date" name="evaluation_date" value="{{ old('evaluation_date', now()->toDateString()) }}"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                        </div>

                        {{-- Submit --}}
                        <div x-show="selectedTpl" x-transition class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="tab='lista'; selectedTpl=null"
                                    class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="btn-bounce inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-2.5 rounded-xl transition-all text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span x-text="audience==='jefes' ? 'Asignar a jefes/coordinadores' : (audience==='empleados' ? 'Asignar a empleados' : 'Asignar a todos')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- TAB: PLANTILLAS                                       --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if($canManageTemplates)
    <div x-show="tab==='plantillas'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">

        <div class="flex items-center justify-between mb-4">
            <p class="text-slate-500 text-sm font-medium">{{ $templatesManage->count() }} plantilla(s) disponibles</p>
            <button onclick="document.getElementById('modal-new-template').classList.remove('hidden')"
                    class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva plantilla
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($templatesManage as $t)
            <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group">
                <div class="h-1 bg-gradient-to-r {{ $t->is_active ? 'from-blue-400 via-sky-400 to-indigo-400' : 'from-slate-300 to-slate-200' }}"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0 pr-2">
                            <h3 class="font-bold text-slate-800 text-sm leading-snug group-hover:text-blue-700 transition-colors">{{ $t->name }}</h3>
                            @if($t->areas->count())
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($t->areas->take(2) as $area)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-600 border border-blue-200">{{ $area->name }}</span>
                                @endforeach
                                @if($t->areas->count() > 2)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">+{{ $t->areas->count() - 2 }}</span>
                                @endif
                            </div>
                            @else
                            <p class="text-xs text-blue-500 mt-1">Global — todas las áreas</p>
                            @endif
                        </div>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold flex-shrink-0 border {{ $t->is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                            {{ $t->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                    <div class="flex gap-3 text-xs text-slate-400 mb-4 py-3 border-y border-slate-100">
                        <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span class="font-semibold text-slate-600">{{ $t->sections_count }}</span> secciones</span>
                        <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg><span class="font-semibold text-slate-600">{{ $t->evaluations_count }}</span> evaluaciones</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.templates.edit', $t) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition-all border border-blue-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            Configurar
                        </a>
                        <form method="POST" action="{{ route('admin.templates.destroy', $t) }}" onsubmit="return confirm('⚠️ ¿Está seguro de eliminar la plantilla «{{ $t->name }}»?\n\nSe perderán todas sus secciones, criterios y rangos configurados. Esta acción no se puede deshacer.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-9 h-9 rounded-xl bg-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center transition-all duration-200 border border-rose-200 hover:border-rose-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-2xl border-2 border-dashed border-slate-200 py-16 text-center">
                <p class="text-slate-700 font-bold text-base">Sin plantillas</p>
                <p class="text-slate-400 text-sm mt-1">Crea tu primera plantilla de evaluación</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Modal: Nueva plantilla --}}
    <div id="modal-new-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-sky-500 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva plantilla
                    </h3>
                    <button onclick="document.getElementById('modal-new-template').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.templates.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Áreas asignadas</label>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="document.querySelectorAll('.modal-tpl-area-cb').forEach(cb=>cb.checked=true)" class="text-[11px] text-blue-600 hover:text-blue-700 font-semibold px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">Todas</button>
                            <span class="text-slate-300 text-xs">|</span>
                            <button type="button" onclick="document.querySelectorAll('.modal-tpl-area-cb').forEach(cb=>cb.checked=false)" class="text-[11px] text-slate-500 hover:text-slate-700 font-semibold px-2 py-1 rounded-lg hover:bg-slate-50 transition-colors">Ninguna</button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 p-3 border border-slate-200 rounded-xl bg-slate-50/50 max-h-40 overflow-y-auto">
                        @foreach($areasForCreate as $area)
                        <label class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border bg-white border-slate-200 text-slate-600 hover:border-blue-200 cursor-pointer transition-all text-sm hover:shadow-sm">
                            <input type="checkbox" name="area_ids[]" value="{{ $area->id }}" class="modal-tpl-area-cb w-3.5 h-3.5 rounded text-blue-600 focus:ring-blue-500">
                            <span>{{ $area->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">Las áreas seleccionadas aquí definen a quiénes se les asignará la evaluación. Sin áreas = global.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-new-template').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="btn-bounce px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-all">Crear y configurar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('[id^="modal-"]').forEach(m => m.classList.add('hidden')); });

const currentYear = {{ now()->year }};
function updatePeriods() {
    const type = document.getElementById('period-type')?.value;
    const sel  = document.getElementById('period-select');
    if (!sel) return;
    sel.innerHTML = '';
    if (!type) { sel.innerHTML = '<option value="">— elige tipo primero —</option>'; return; }
    let opts = [];
    if (type === 'trimestral') {
        opts = [`${currentYear}-T1`,`${currentYear}-T2`,`${currentYear}-T3`,`${currentYear}-T4`];
    } else if (type === 'semestral') {
        opts = [`${currentYear}-S1`,`${currentYear}-S2`];
    } else if (type === 'anual') {
        opts = [`${currentYear}`];
    }
    opts.forEach(o => {
        const el = document.createElement('option');
        el.value = o; el.textContent = o;
        if ('{{ old('period') }}' === o) el.selected = true;
        sel.appendChild(el);
    });
}
document.addEventListener('DOMContentLoaded', function() {
    const pt = document.getElementById('period-type');
    if (pt && pt.value) updatePeriods();
});
</script>
@endpush
@endsection
