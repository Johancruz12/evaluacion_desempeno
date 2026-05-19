@extends('layouts.app')
@section('title', 'Evaluaciones')

@section('content')
@php
$user = auth()->user();
$canCreate = $user->canCreateEvaluations();
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
        @php
            $activeAreaName = $areas->firstWhere('id', request('area_id'))?->name;
            $statusLabels = ['pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada','revisada'=>'Revisada'];
            $activeStatus = request('status') ? ($statusLabels[request('status')] ?? null) : null;
            $hasFilters = $activeAreaName || $activeStatus;
        @endphp
        <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-5">
            <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
            <div class="px-5 py-4 space-y-3">
                <form method="GET" action="{{ route('evaluations.index') }}"
                      class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-3 items-end">

                    {{-- Área combobox --}}
                    <div x-data="{
                            open: false,
                            query: '{{ $activeAreaName ?? '' }}',
                            selectedId: '{{ request('area_id', '') }}',
                            selectedName: '{{ $activeAreaName ?? '' }}',
                            areas: {{ Js::from($areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values()) }},
                            get filtered() {
                                if (!this.query) return this.areas;
                                const q = this.query.toLowerCase();
                                return this.areas.filter(a => a.name.toLowerCase().includes(q));
                            },
                            select(area) { this.selectedId = area.id; this.selectedName = area.name; this.query = area.name; this.open = false; },
                            clear() { this.selectedId = ''; this.selectedName = ''; this.query = ''; this.open = false; }
                         }"
                         @click.outside="open = false"
                         class="relative">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Filtrar por área</label>
                        <input type="hidden" name="area_id" :value="selectedId">

                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center"
                                  :class="selectedId ? 'text-blue-500' : 'text-slate-400'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </span>

                            <input type="text"
                                   x-model="query"
                                   @focus="open = true"
                                   @input="open = true; selectedId = ''; selectedName = ''"
                                   @keydown.escape="open = false"
                                   @keydown.enter.prevent="if (filtered.length === 1) select(filtered[0])"
                                   placeholder="Buscar y seleccionar…"
                                   autocomplete="off"
                                   class="w-full text-sm rounded-xl border bg-slate-50 transition-all outline-none"
                                   style="padding: 0.625rem 2.25rem 0.625rem 2.25rem; line-height: 1.25rem;"
                                   :class="selectedId
                                       ? 'border-blue-500 bg-blue-50/60 text-blue-800 font-semibold ring-2 ring-blue-100'
                                       : 'border-slate-200 text-slate-700 hover:border-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100'">

                            <button x-show="query || selectedId" x-cloak type="button" @click="clear()"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition-colors p-0.5 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                                 class="absolute z-50 top-full mt-1.5 left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto">
                                <div x-show="filtered.length === 0" class="px-4 py-3 text-sm text-slate-400 italic">Sin resultados</div>
                                <template x-for="area in filtered" :key="area.id">
                                    <button type="button" @click="select(area)"
                                            class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center gap-2"
                                            :class="area.id == selectedId
                                                ? 'bg-blue-50 text-blue-700 font-semibold'
                                                : 'text-slate-700 hover:bg-slate-50'">
                                        <svg x-show="area.id == selectedId" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 flex-shrink-0">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span x-text="area.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Estado --}}
                    <div class="relative">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Filtrar por estado</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center"
                                  :class="'{{ request('status') }}' ? 'text-blue-500' : 'text-slate-400'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                            </span>
                            <select name="status"
                                    class="w-full text-sm rounded-xl border bg-slate-50 text-slate-700 hover:border-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 transition-all outline-none appearance-none cursor-pointer
                                           {{ request('status') ? 'border-blue-500 bg-blue-50/60 text-blue-800 font-semibold ring-2 ring-blue-100' : 'border-slate-200' }}"
                                    style="padding: 0.625rem 2.25rem 0.625rem 2.25rem; line-height: 1.25rem;">
                                <option value="">Todos los estados</option>
                                <option value="pendiente"   {{ request('status') == 'pendiente'   ? 'selected' : '' }}>Pendiente</option>
                                <option value="en_progreso" {{ request('status') == 'en_progreso' ? 'selected' : '' }}>En progreso</option>
                                <option value="completada"  {{ request('status') == 'completada'  ? 'selected' : '' }}>Completada</option>
                                <option value="revisada"    {{ request('status') == 'revisada'    ? 'selected' : '' }}>Revisada</option>
                            </select>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    {{-- Botón Filtrar --}}
                    <div>
                        <button type="submit"
                                class="btn-bounce inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-semibold rounded-xl transition-all text-sm shadow-sm shadow-blue-500/30 w-full md:w-auto"
                                style="padding: 0.625rem 1.25rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                            </svg>
                            Aplicar filtros
                        </button>
                    </div>
                </form>

                {{-- Chips de filtros activos --}}
                @if($hasFilters)
                <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Filtros activos:</span>

                    @if($activeAreaName)
                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-3 py-1 text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                        </svg>
                        Área: {{ $activeAreaName }}
                        <a href="{{ route('evaluations.index', array_filter(['status' => request('status')])) }}"
                           class="text-blue-400 hover:text-rose-500 transition-colors -mr-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    </span>
                    @endif

                    @if($activeStatus)
                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-1 text-xs font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Estado: {{ $activeStatus }}
                        <a href="{{ route('evaluations.index', array_filter(['area_id' => request('area_id')])) }}"
                           class="text-emerald-400 hover:text-rose-500 transition-colors -mr-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    </span>
                    @endif

                    <a href="{{ route('evaluations.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-rose-500 transition-colors ml-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14z"/>
                        </svg>
                        Limpiar todo
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Master select-all bar (always visible when there are evaluations) --}}
        @if($canCreate && $groupedEvaluations->isNotEmpty())
        <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-3">
            <div class="flex items-center justify-between px-5 py-3">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="checkbox" class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                           @change="
                               const boxes = document.querySelectorAll('.eval-checkbox');
                               if ($event.target.checked) {
                                   selected = [...boxes].map(c => c.value);
                                   boxes.forEach(c => c.checked = true);
                               } else {
                                   selected = [];
                                   boxes.forEach(c => c.checked = false);
                               }
                           "
                           :checked="selected.length > 0 && selected.length === document.querySelectorAll('.eval-checkbox').length">
                    <div>
                        <p class="text-sm font-bold text-slate-800">Seleccionar todas las evaluaciones</p>
                        <p class="text-xs text-slate-500">
                            <span x-text="selected.length"></span> de
                            <span>{{ collect($groupedEvaluations)->sum(fn($t) => collect($t['areas'])->sum(fn($a) => $a['evaluations']->count())) }}</span>
                            visibles seleccionadas
                        </p>
                    </div>
                </label>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="
                                const boxes = document.querySelectorAll('.eval-checkbox');
                                selected = [...boxes].map(c => c.value);
                                boxes.forEach(c => c.checked = true);
                            "
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border border-blue-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Marcar todas
                    </button>
                    <button type="button" x-show="selected.length > 0"
                            @click="selected=[]; document.querySelectorAll('.eval-checkbox').forEach(c=>c.checked=false)"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors border border-slate-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Limpiar
                    </button>
                </div>
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
                    {{-- Un solo input oculto llenado síncronamente al submit (evita el delay de Alpine x-for) --}}
                    <form method="POST" action="{{ route('evaluations.bulk-reset') }}" id="bulk-reset-form"
                          @submit.prevent="
                              if (selected.length === 0) return;
                              AppConfirm({
                                  title: 'Reiniciar ' + selected.length + ' evaluaciones',
                                  message: 'Se eliminarán todas las respuestas y calificaciones, y las evaluaciones volverán a estado pendiente. Esta acción no se puede deshacer.',
                                  variant: 'warning',
                                  confirmText: 'Sí, reiniciar'
                              }).then(ok => {
                                  if (!ok) return;
                                  $refs.bulkIdsInput.value = selected.join(',');
                                  $el.submit();
                              });
                          ">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="evaluation_ids_csv" x-ref="bulkIdsInput">
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
                                        @if($ev->due_date)
                                        @php
                                            $isDuePast = $ev->due_date->isPast() && !in_array($ev->status, ['completada','cerrada','revisada']);
                                            $isDueSoon = !$isDuePast && $ev->due_date->diffInDays(now()) <= 3;
                                        @endphp
                                        <p class="text-[10px] font-semibold mt-0.5 {{ $isDuePast ? 'text-rose-500' : ($isDueSoon ? 'text-amber-500' : 'text-slate-400') }}">
                                            ⏰ Cierre: {{ $ev->due_date->format('d/m/Y') }}{{ $isDuePast ? ' — vencida' : '' }}
                                        </p>
                                        @endif
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

                    <form method="POST" action="{{ route('evaluations.store') }}" x-data="{
                            selectedTpl: null,
                            audience: '{{ $defaultAudience }}',
                            templates: {{ Js::from($templates->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'area_names' => $t->areas->pluck('name')->toArray(), 'is_global' => $t->areas->isEmpty(), 'sections_count' => $t->sections_count])) }},
                            preview: null,
                            previewLoading: false,
                            fetchPreview() {
                                if (!this.selectedTpl) { this.preview = null; return; }
                                this.previewLoading = true;
                                const url = new URL('{{ route('evaluations.preview') }}', window.location.origin);
                                url.searchParams.set('template_id', this.selectedTpl.id);
                                url.searchParams.set('target_audience', this.audience);
                                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                                    .then(r => r.json()).then(data => { this.preview = data; this.previewLoading = false; })
                                    .catch(() => { this.preview = null; this.previewLoading = false; });
                            }
                        }" x-init="$watch('selectedTpl', () => fetchPreview()); $watch('audience', () => fetchPreview())" class="space-y-6">
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

                        {{-- Preview: employee count --}}
                        <div x-show="selectedTpl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500 flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <template x-if="previewLoading">
                                        <p class="text-sm text-emerald-700 animate-pulse">Calculando empleados…</p>
                                    </template>
                                    <template x-if="!previewLoading && preview !== null">
                                        <div>
                                            <p class="text-sm font-bold text-emerald-800">
                                                Se asignará a <span x-text="preview.count"></span> empleado<span x-show="preview.count !== 1">s</span>
                                                <span x-show="preview.areas && preview.areas.length > 0"> de <span x-text="preview.areas.length"></span> área<span x-show="preview.areas.length !== 1">s</span></span>
                                            </p>
                                            <div class="flex flex-wrap gap-1.5 mt-2" x-show="preview.areas && preview.areas.length > 0">
                                                <template x-for="area in preview.areas" :key="area.name">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                        <span x-text="area.name"></span>
                                                        <span class="font-bold" x-text="'(' + area.count + ')'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                            <p x-show="preview.count === 0" class="text-xs text-amber-600 mt-1 font-medium">⚠ No hay empleados activos que coincidan con los criterios seleccionados.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Period --}}
                        <div x-show="selectedTpl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-r from-blue-500 to-sky-500 flex items-center justify-center text-white text-xs font-bold shadow-md">3</div>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Período de evaluación</label>
                            </div>
                            @if($availablePeriods->isEmpty())
                            <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-3 text-sm">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                No hay períodos disponibles. <a href="{{ route('admin.settings.index') }}" class="font-semibold underline ml-1">Configura períodos en Ajustes →</a>
                            </div>
                            @else
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Período</label>
                                <select name="period" required
                                        class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                                    <option value="">Selecciona un período…</option>
                                    @foreach($availablePeriods->groupBy('year')->sortKeysDesc() as $yr => $yPeriods)
                                    <optgroup label="{{ $yr }}">
                                        @foreach($yPeriods as $p)
                                        <option value="{{ $p->label }}" {{ old('period') == $p->label ? 'selected' : '' }}>
                                            {{ $p->label }} — {{ ['trimestral'=>'Trimestral','semestral'=>'Semestral','anual'=>'Anual'][$p->type] ?? $p->type }}
                                        </option>
                                        @endforeach
                                    </optgroup>
                                    @endforeach
                                </select>
                                @error('period')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif
                        </div>

                        {{-- Step 3: Date --}}
                        <div x-show="selectedTpl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-r from-slate-300 to-slate-400 flex items-center justify-center text-white text-xs font-bold">4</div>
                                <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Fechas</label>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">Fecha de evaluación <span class="text-slate-400">(opcional)</span></label>
                                    <input type="date" name="evaluation_date" value="{{ old('evaluation_date', now()->toDateString()) }}"
                                           class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">
                                        Fecha límite de cierre
                                        <span class="text-rose-400 font-semibold">*</span>
                                    </label>
                                    <input type="date" name="due_date" value="{{ old('due_date') }}" required
                                           min="{{ now()->addDay()->toDateString() }}"
                                           class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                                    <p class="text-xs text-slate-400 mt-1">Al vencer esta fecha, la evaluación se cerrará automáticamente.</p>
                                </div>
                            </div>
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


</div>

@push('scripts')
<script>
document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('[id^="modal-"]').forEach(m => m.classList.add('hidden')); });
</script>
@endpush
@endsection
