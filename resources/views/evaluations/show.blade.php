@extends('layouts.app')
@section('title', 'Evaluación')

@php
    $user        = auth()->user();
    $isEmployee  = $evaluation->employee_id === $user->id && !$user->isAdmin();
    // Jefe evaluando a un empleado de su área (puede calificar)
    $isJefeEvaluatingEmployee = $user->isJefeArea() && !$user->isAdmin()
        && $evaluation->employee_id !== $user->id
        && $evaluation->employee?->area_id === $user->area_id;
    // isEvaluator: puede ver y editar columna "Calificación jefe"
    $isEvaluator = $user->isAdmin() || $isJefeEvaluatingEmployee;
    // Employee: locked once status is completada or revisada
    // Evaluator: locked only when revisada
    $isReadOnly  = ($isEmployee && in_array($evaluation->status, ['completada', 'revisada']))
                || ($isEvaluator && $evaluation->status === 'revisada');
    $canBuild    = $evaluation->status !== 'revisada' && $user->isAdmin();
    $autoEvalComplete = $evaluation->hasCompletedAutoEvaluation();
    $responsesMap = $evaluation->responses->keyBy('criteria_id');

    $scaleLabels = [1 => 'Deficiente', 2 => 'Regular', 3 => 'Cumple', 4 => 'Supera', 5 => 'Sobresale'];

    $sectionMeta = [
        'competencias_org'   => ['icon'=>'🧠','label'=>'Comp. Organizacionales','gradient'=>'from-blue-500 to-blue-600','bg'=>'bg-blue-50','border'=>'border-blue-200','badge'=>'bg-blue-100 text-blue-700','ring'=>'ring-blue-500/20'],
        'competencias_cargo' => ['icon'=>'💼','label'=>'Comp. del Cargo','gradient'=>'from-sky-500 to-sky-600','bg'=>'bg-sky-50','border'=>'border-sky-200','badge'=>'bg-sky-100 text-sky-700','ring'=>'ring-sky-500/20'],
        'responsabilidades'  => ['icon'=>'✅','label'=>'Responsabilidades','gradient'=>'from-amber-500 to-amber-600','bg'=>'bg-amber-50','border'=>'border-amber-200','badge'=>'bg-amber-100 text-amber-700','ring'=>'ring-amber-500/20'],
        'rango'              => ['icon'=>'📊','label'=>'Tabla de Rangos','gradient'=>'from-slate-500 to-slate-600','bg'=>'bg-slate-50','border'=>'border-slate-200','badge'=>'bg-slate-100 text-slate-700','ring'=>'ring-slate-500/20'],
    ];

    $completedCount = $responsesMap->filter(fn($r) => $isEmployee ? $r->auto_score !== null : $r->evaluator_score !== null)->count();
    $rangoCount = 0;
    foreach ($evaluation->template?->sections ?? [] as $_sec) {
        if ($_sec->type === 'rango') $rangoCount += $_sec->criteria->count();
    }
    $totalCount = $responsesMap->count() - $rangoCount;

    $scoresJson = [];
    foreach ($responsesMap as $id => $r) {
        $scoresJson[$id] = [
            'auto'      => $r->auto_score      ? (int)$r->auto_score      : null,
            'evaluator' => $r->evaluator_score ? (int)$r->evaluator_score : null,
            'comment'   => $r->comment ?? '',
        ];
    }

    $evalDataPayload = [
        'evalId'      => $evaluation->id,
        'templateId'  => $evaluation->template_id,
        'isEmployee'  => $isEmployee,
        'isEvaluator' => $isEvaluator,
        'isJefeEvaluatingEmployee' => $isJefeEvaluatingEmployee,
        'isReadOnly'  => $isReadOnly,
        'canBuild'    => $canBuild,
        'csrf'        => csrf_token(),
        'scores'      => $scoresJson,
        'totals'      => [
            'auto'      => (float)($evaluation->total_auto_score ?? 0),
            'evaluator' => (float)($evaluation->total_evaluator_score ?? 0),
            'final'     => (float)($evaluation->final_score ?? 0),
        ],
        'routes' => [
            'reorder'     => route('evaluations.builder.reorder', $evaluation),
            'toggle'      => '/evaluations/'.$evaluation->id.'/builder/toggle',
            'addSection'  => route('evaluations.builder.add-section', $evaluation),
            'addCriteria' => '/evaluations/'.$evaluation->id.'/builder/add-criteria',
            'rmCriteria'  => '/evaluations/'.$evaluation->id.'/builder/criteria',
            'saveScore'   => '/evaluations/'.$evaluation->id.'/score',
            'liveState'   => route('evaluations.live-state', $evaluation),
        ],
        'statusUrl' => route('evaluations.complete', $evaluation),
        'saveUrl'   => route('evaluations.save-responses', $evaluation),
    ];
@endphp

@section('content')
<div x-data="evalBuilder()" x-init="init()" class="relative max-w-6xl mx-auto space-y-6">

    {{-- ── HERO HEADER CARD ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="h-1.5 bg-gradient-to-r from-blue-500 via-sky-500 to-blue-400"></div>
        <div class="p-5 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-600 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-blue-500/20">
                        {{ strtoupper(substr($evaluation->employee?->person?->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($evaluation->employee?->person?->last_name ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <a href="{{ route('evaluations.index') }}" class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-blue-600 transition-colors mb-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Volver a evaluaciones
                        </a>
                        <h1 class="text-xl font-bold text-slate-800 leading-tight">{{ $evaluation->template?->name }}</h1>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $evaluation->employee?->person?->first_name }} {{ $evaluation->employee?->person?->last_name }}
                            </span>
                            @if($evaluation->employee?->area)
                            <span class="text-slate-300">·</span>
                            <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $evaluation->employee->area->name }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $statusConfig = match($evaluation->status) {
                            'pendiente' => ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'class' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => 'Pendiente'],
                            'en_progreso' => ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'class' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'En progreso'],
                            'completada' => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Autoevaluación completada'],
                            'revisada' => ['icon' => 'M5 13l4 4L19 7', 'class' => 'bg-purple-50 text-purple-700 border-purple-200', 'label' => 'Cerrada por RR.HH.'],
                            default => ['icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'class' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => ucfirst($evaluation->status)],
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $statusConfig['class'] }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusConfig['icon'] }}"/></svg>
                        {{ $statusConfig['label'] }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ ucfirst($evaluation->period_type) }} · {{ $evaluation->period }}
                    </span>
                </div>
            </div>
        </div>
        {{-- Score summary bar --}}
        <div class="border-t border-slate-100 bg-slate-50/50 px-5 sm:px-6 py-3">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4 sm:gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Autoevaluación</p>
                            <p class="text-lg font-black text-blue-600 leading-none" x-text="totals.auto.toFixed(1)"></p>
                        </div>
                    </div>
                    @if($isEvaluator)
                    <div class="w-px h-10 bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Jefe</p>
                            <p class="text-lg font-black text-sky-600 leading-none" x-text="totals.evaluator.toFixed(1)"></p>
                        </div>
                    </div>
                    @endif
                    <div class="w-px h-10 bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Final</p>
                            <p class="text-lg font-black text-emerald-600 leading-none" x-text="totals.final.toFixed(1)"></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{-- PDF Export button (only when enabled in system settings) --}}
                    @if(\App\Models\Setting::bool('pdf_enabled', true))
                    <a href="{{ route('evaluations.export-pdf', $evaluation) }}"
                              class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 text-white border border-rose-600 hover:bg-rose-500 rounded-xl text-sm font-semibold transition-all shadow-md shadow-rose-500/20"
                       title="Descargar PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="hidden sm:inline">PDF</span>
                    </a>
                    @endif
                    @if($canBuild)
                    <button @click="editMode = !editMode; if(editMode) $nextTick(() => initSortable())"
                            :class="editMode ? 'bg-blue-700 text-white shadow-lg shadow-blue-500/30 border-blue-700' : 'bg-slate-800 text-white border-slate-800 hover:bg-slate-700'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 border shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span x-text="editMode ? 'Salir del editor' : 'Editar plantilla'"></span>
                    </button>
                    @endif
                    @if($isEvaluator && !$isReadOnly && $evaluation->status === 'completada' && $user->isAdmin())
                    <form method="POST" action="{{ route('evaluations.complete', $evaluation) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('⚠️ ¿Confirmar cierre definitivo de esta evaluación?\n\nUna vez cerrada, ningún participante podrá modificar calificaciones ni respuestas. Esta acción no se puede deshacer.')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-semibold transition-all shadow-md shadow-emerald-500/20 hover:shadow-lg hover:shadow-emerald-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Cerrar evaluación definitivamente
                        </button>
                    </form>
                    @elseif($isEvaluator && !$isReadOnly && $evaluation->status !== 'completada')
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Esperando que el empleado finalice
                    </span>
                    @endif
                    @if($user->isAdmin() && in_array($evaluation->status, ['completada', 'revisada']))
                    <button onclick="document.getElementById('modal-reopen').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 text-white border border-amber-600 hover:bg-amber-500 rounded-xl text-sm font-semibold transition-all shadow-md shadow-amber-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reabrir evaluación
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── ROLE-BASED INSTRUCTION BANNER ── --}}
    @if($isJefeEvaluatingEmployee && $isReadOnly)
    <div class="rounded-2xl border overflow-hidden bg-purple-50 border-purple-200">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-purple-100">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-purple-800">✅ Evaluación cerrada definitivamente</h3>
                <p class="text-xs text-purple-700 mt-1 leading-relaxed">
                    La evaluación de <strong>{{ $evaluation->employee?->name }}</strong> fue cerrada por RR.HH. y no puede modificarse.
                </p>
            </div>
        </div>
    </div>
    @elseif($isJefeEvaluatingEmployee && $evaluation->status !== 'completada')
    <div class="rounded-2xl border overflow-hidden bg-amber-50 border-amber-200">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-amber-100">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-amber-800">⏳ Esperando autoevaluación del empleado</h3>
                <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                    <strong>{{ $evaluation->employee?->name }}</strong> aún no ha completado su autoevaluación. Podrás calificarlo una vez que la finalice.
                </p>
            </div>
        </div>
    </div>
    @elseif($isEmployee && $isReadOnly)
    <div class="rounded-2xl border overflow-hidden bg-emerald-50 border-emerald-200">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-100">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-emerald-800">✅ Autoevaluación finalizada</h3>
                <p class="text-xs text-emerald-700 mt-1 leading-relaxed">
                    Ya enviaste tu autoevaluación. No puedes realizar más cambios. El área de Talento Humano revisará los resultados.
                </p>
            </div>
        </div>
    </div>
    @elseif(!$isReadOnly)
    <div class="rounded-2xl border overflow-hidden {{ $isEmployee ? 'bg-blue-50 border-blue-200' : 'bg-sky-50 border-sky-200' }}">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center {{ $isEmployee ? 'bg-blue-100' : 'bg-sky-100' }}">
                @if($isEmployee)
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                @else
                <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                @if($isEmployee)
                <h3 class="text-sm font-bold text-blue-800">👋 ¡Hola! Es tu turno de autoevaluarte</h3>
                <p class="text-xs text-blue-700 mt-1 leading-relaxed">
                    Haz clic en los <strong>números del 1 al 5</strong> para calificar cada criterio.
                    <span class="font-semibold">1 = Deficiente</span> y <span class="font-semibold">5 = Sobresale</span>.
                    <span class="font-bold text-blue-800 block mt-1">⚠️ Al presionar "Guardar y finalizar" no podrás realizar cambios.</span>
                </p>
                @else
                <h3 class="text-sm font-bold text-sky-800">📋 Califica el desempeño del empleado</h3>
                <p class="text-xs text-sky-700 mt-1 leading-relaxed">
                    Selecciona una puntuación del <strong>1 al 5</strong> en la columna "Jefe" para cada criterio. Puedes agregar observaciones.
                    Los cambios se guardan <strong>automáticamente</strong>. Al terminar, presiona <strong>"Completar evaluación"</strong>.
                </p>
                @endif
            </div>
        </div>
        <div class="px-5 pb-4">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="{{ $isEmployee ? 'text-blue-600' : 'text-sky-600' }} font-semibold">Progreso de calificación</span>
                <span class="{{ $isEmployee ? 'text-blue-700' : 'text-sky-700' }} font-bold"><span x-text="completedCount"></span> / <span x-text="totalCount"></span> criterios</span>
            </div>
            <div class="w-full bg-white/60 rounded-full h-2.5 overflow-hidden">
                <div class="h-2.5 rounded-full transition-all duration-700 ease-out"
                     :class="progress >= 100 ? 'bg-emerald-500' : (progress >= 50 ? 'bg-blue-500' : 'bg-amber-400')"
                     :style="`width: ${progress}%`"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Autosave toast --}}
    <div x-show="saveState === 'saving'" x-transition class="fixed top-20 right-5 z-50 flex items-center gap-2.5 bg-white border border-slate-200 shadow-xl rounded-2xl px-5 py-3 text-sm font-medium text-slate-600 pointer-events-none">
        <svg class="w-5 h-5 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        Guardando…
    </div>
    <div x-show="saveState === 'saved'" x-transition class="fixed top-20 right-5 z-50 flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 shadow-xl rounded-2xl px-5 py-3 text-sm font-bold text-emerald-700 pointer-events-none">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        ¡Guardado correctamente!
    </div>
    {{-- Live update indicator --}}
    <div x-show="liveUpdateState === 'updated'" x-transition class="fixed top-20 right-5 z-50 flex items-center gap-2.5 bg-sky-50 border border-sky-200 shadow-xl rounded-2xl px-5 py-3 text-sm font-bold text-sky-700 pointer-events-none">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Actualización en tiempo real
    </div>
    {{-- Live update indicator --}}
    <div x-show="liveUpdateState === 'updated'" x-transition class="fixed top-20 right-5 z-50 flex items-center gap-2.5 bg-sky-50 border border-sky-200 shadow-xl rounded-2xl px-5 py-3 text-sm font-bold text-sky-700 pointer-events-none">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Actualización en tiempo real
    </div>

    {{-- ── BUILDER PANEL ── --}}
    <div x-show="editMode" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white rounded-2xl border-2 border-dashed border-blue-300 shadow-sm overflow-hidden">
        <div class="bg-blue-50 px-5 py-4 border-b border-blue-200">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-blue-800 flex items-center gap-2.5 text-sm">
                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </span>
                    Editor de plantilla
                </h3>
                <p class="text-xs text-blue-600 bg-blue-100 px-3 py-1 rounded-lg font-medium">Selecciona un tipo de sección para agregarla</p>
            </div>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($sectionMeta as $type => $meta)
                @if($type !== 'rango')
                <button type="button" @click="promptAddSection('{{ $type }}')" :disabled="loading"
                        class="group flex flex-col items-center justify-center gap-3 p-5 rounded-2xl border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 cursor-pointer hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50">
                    <span class="text-3xl group-hover:scale-110 transition-transform">{{ $meta['icon'] }}</span>
                    <span class="text-xs font-bold text-slate-600 group-hover:text-blue-700 text-center leading-tight">{{ $meta['label'] }}</span>
                </button>
                @endif
                @endforeach
                <button type="button" @click="promptAddSection('rango')" :disabled="loading"
                        class="group flex flex-col items-center justify-center gap-3 p-5 rounded-2xl border-2 border-dashed border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 cursor-pointer hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50">
                    <span class="text-3xl group-hover:scale-110 transition-transform">📊</span>
                    <span class="text-xs font-bold text-slate-600 group-hover:text-blue-700 text-center leading-tight">Tabla de Rangos</span>
                </button>
            </div>
            <div x-show="addSectionForm.show" x-transition class="mt-4 bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
                <p class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Nueva sección: <span class="text-blue-600" x-text="addSectionForm.typeLabel"></span>
                </p>
                <div class="flex gap-3">
                    <input type="text" x-model="addSectionForm.name" placeholder="Escribe el nombre de la sección…"
                           class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           @keydown.enter="submitAddSection()" :disabled="loading">
                    <button type="button" @click="submitAddSection()" :disabled="loading"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Agregar
                    </button>
                    <button type="button" @click="addSectionForm.show=false"
                            class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-xl transition-colors">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── VISUAL SCALE LEGEND ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Escala de calificación — haz clic en el número para calificar</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 border border-rose-200">
                <span class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center font-black text-sm">1</span>
                <div><p class="text-xs font-bold text-rose-700">Deficiente</p><p class="text-[10px] text-rose-500">No cumple</p></div>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-50 border border-orange-200">
                <span class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center font-black text-sm">2</span>
                <div><p class="text-xs font-bold text-orange-700">Regular</p><p class="text-[10px] text-orange-500">Necesita mejorar</p></div>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 border border-amber-200">
                <span class="w-8 h-8 rounded-full bg-amber-400 text-white flex items-center justify-center font-black text-sm">3</span>
                <div><p class="text-xs font-bold text-amber-700">Cumple</p><p class="text-[10px] text-amber-500">Nivel esperado</p></div>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 border border-blue-200">
                <span class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-black text-sm">4</span>
                <div><p class="text-xs font-bold text-blue-700">Supera</p><p class="text-[10px] text-blue-500">Arriba del promedio</p></div>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-200">
                <span class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-black text-sm">5</span>
                <div><p class="text-xs font-bold text-emerald-700">Sobresale</p><p class="text-[10px] text-emerald-500">Excepcional</p></div>
            </div>
        </div>
    </div>

    {{-- ── SECTIONS ── --}}
    <div id="sections-container" class="space-y-5">
        @foreach($evaluation->template?->sections->sortBy('order') ?? [] as $section)
        @php $meta = $sectionMeta[$section->type] ?? $sectionMeta['rango']; @endphp
        <div class="section-card bg-white rounded-2xl border shadow-sm overflow-hidden transition-all duration-200 {{ $meta['border'] }}"
             id="section-{{ $section->id }}" data-section-id="{{ $section->id }}"
             :class="!sectionsActive[{{ $section->id }}] ? 'opacity-40 border-dashed !border-slate-300' : ''">

            <div class="flex items-center gap-3 px-5 py-4 {{ $meta['bg'] }} border-b {{ $meta['border'] }}">
                <div x-show="editMode" class="drag-handle cursor-grab active:cursor-grabbing flex-shrink-0 text-slate-300 hover:text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                </div>
                <span class="text-2xl flex-shrink-0">{{ $meta['icon'] }}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-slate-800 text-sm">{{ $section->name }}</h3>
                        <span class="inline-flex px-2.5 py-0.5 rounded-lg text-[10px] font-bold {{ $meta['badge'] }}">{{ $meta['label'] }}</span>
                    </div>
                    @if($section->description)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $section->description }}</p>
                    @endif
                </div>

                @if($section->type !== 'rango')
                @php
                    $secCriteriaIds = $section->criteria->where('is_active',true)->pluck('id')->toArray();
                    $secDone = 0; $secTotal = count($secCriteriaIds);
                    foreach ($secCriteriaIds as $_cid) {
                        $r = $responsesMap[$_cid] ?? null;
                        if ($r && ($isEmployee ? $r->auto_score !== null : $r->evaluator_score !== null)) $secDone++;
                    }
                @endphp
                <div class="flex-shrink-0">
                    @if($secDone == $secTotal && $secTotal > 0)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Completa
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500">{{ $secDone }}/{{ $secTotal }}</span>
                    @endif
                </div>
                @endif

                @if($canBuild)
                <button x-show="editMode" type="button" @click="toggleSection({{ $section->id }})"
                        :title="sectionsActive[{{ $section->id }}] ? 'Ocultar sección' : 'Mostrar sección'"
                        :class="sectionsActive[{{ $section->id }}] ? 'text-amber-500 bg-amber-50 hover:bg-amber-100 border-amber-200' : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border-emerald-200'"
                        class="flex-shrink-0 w-8 h-8 rounded-lg border flex items-center justify-center transition-colors">
                    <svg x-show="sectionsActive[{{ $section->id }}]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M3 3l18 18"/></svg>
                    <svg x-show="!sectionsActive[{{ $section->id }}]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
                @endif

                <button type="button" @click="openSections[{{ $section->id }}] = !openSections[{{ $section->id }}]"
                        class="flex-shrink-0 w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-400 transition-all duration-200"
                        :class="openSections[{{ $section->id }}] ? '' : '-rotate-90'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <div x-show="openSections[{{ $section->id }}]" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if($section->type === 'rango')
                <div class="p-5">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-4 py-3 text-left font-bold text-slate-600 rounded-l-xl">Rango de Puntos</th>
                                <th class="px-4 py-3 text-left font-bold text-slate-600 rounded-r-xl">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($evaluation->template->scoringRanges ?? [] as $range)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $range->min_score }} – {{ $range->max_score }}</td>
                                <td class="px-4 py-3 font-bold {{ $range->color === 'green' ? 'text-emerald-700' : ($range->color === 'blue' ? 'text-blue-700' : ($range->color === 'yellow' ? 'text-amber-700' : 'text-rose-700')) }}">{{ $range->label }}</td>
                            </tr>
                            @endforeach
                            @if(!($evaluation->template->scoringRanges ?? [])->count())
                            <tr><td class="px-4 py-3">91 – 110</td><td class="px-4 py-3 font-bold text-emerald-700">Sobrepasa las expectativas</td></tr>
                            <tr><td class="px-4 py-3">71 – 90</td><td class="px-4 py-3 font-bold text-blue-700">Buen desempeño</td></tr>
                            <tr><td class="px-4 py-3">50 – 70</td><td class="px-4 py-3 font-bold text-amber-700">Cumple las expectativas</td></tr>
                            <tr><td class="px-4 py-3">&lt; 50</td><td class="px-4 py-3 font-bold text-rose-700">Requiere mejora</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @else
                <div class="divide-y divide-slate-100" id="criteria-{{ $section->id }}">
                    @foreach($section->criteria->where('is_active',true)->sortBy('order') as $c)
                    @php $resp = $responsesMap[$c->id] ?? null; @endphp
                    <div class="group px-5 py-4 hover:bg-slate-50/40 transition-colors" id="row-{{ $c->id }}" data-criteria-id="{{ $c->id }}">
                        <div class="flex flex-col xl:flex-row xl:items-center gap-4">
                            <div class="xl:w-4/12">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-7 h-7 rounded-lg {{ $meta['bg'] }} flex items-center justify-center mt-0.5">
                                        <span class="text-xs font-black text-slate-400">{{ $loop->iteration }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800 text-sm leading-snug">{{ $c->name }}</p>
                                        @if($c->description)
                                        <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">{{ $c->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 xl:gap-6 flex-1 flex-wrap xl:flex-nowrap">
                                @if($isEmployee && !$isReadOnly)
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        Mi calificación
                                    </span>
                                    <div class="flex gap-1">
                                        @foreach([1,2,3,4,5] as $v)
                                        <button type="button" @click="setScore({{ $c->id }}, 'auto', {{ $v }})"
                                                :class="scores[{{ $c->id }}]?.auto === {{ $v }} ? scoreActiveClass({{ $v }}) : 'bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600'"
                                                class="w-9 h-9 rounded-full font-bold text-sm transition-all duration-150 hover:scale-110 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500"
                                                title="{{ $scaleLabels[$v] }}">{{ $v }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                @elseif(!$isEmployee)
                                <div class="flex flex-col items-center gap-1.5 min-w-[80px]">
                                    <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Autoevaluación</span>
                                    @if($resp?->auto_score)
                                    @php $as = (int)$resp->auto_score; @endphp
                                    <span id="auto-badge-{{ $c->id }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold {{ $as>=5?'bg-emerald-100 text-emerald-700':($as>=4?'bg-blue-100 text-blue-700':($as>=3?'bg-amber-100 text-amber-700':($as>=2?'bg-orange-100 text-orange-700':'bg-rose-100 text-rose-700'))) }}">
                                        {{ $resp->auto_score }} · {{ $scaleLabels[$as] }}
                                    </span>
                                    @else
                                    <span id="auto-badge-{{ $c->id }}" class="text-xs text-slate-300 italic px-3 py-1.5">Sin calificar</span>
                                    @endif
                                </div>
                                @endif

                                @if($isEvaluator)
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-sky-500 uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
                                        Calificación jefe
                                    </span>
                                    @if(!$isReadOnly)
                                    <div class="flex gap-1">
                                        @foreach([1,2,3,4,5] as $v)
                                        <button type="button" @click="setScore({{ $c->id }}, 'evaluator', {{ $v }})"
                                                :class="scores[{{ $c->id }}]?.evaluator === {{ $v }} ? scoreActiveClass({{ $v }}) : 'bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600'"
                                                class="w-9 h-9 rounded-full font-bold text-sm transition-all duration-150 hover:scale-110 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-sky-500"
                                                title="{{ $scaleLabels[$v] }}">{{ $v }}</button>
                                        @endforeach
                                    </div>
                                    @else
                                    @if($resp?->evaluator_score)
                                    @php $es = (int)$resp->evaluator_score; @endphp
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold {{ $es>=5?'bg-emerald-100 text-emerald-700':($es>=4?'bg-blue-100 text-blue-700':($es>=3?'bg-amber-100 text-amber-700':($es>=2?'bg-orange-100 text-orange-700':'bg-rose-100 text-rose-700'))) }}">
                                        {{ $resp->evaluator_score }} · {{ $scaleLabels[$es] }}
                                    </span>
                                    @else
                                    <span class="text-xs text-slate-300 italic px-3 py-1.5">Sin calificar</span>
                                    @endif
                                    @endif
                                </div>

                                @php
                                    $avg = null;
                                    if($resp?->auto_score!==null && $resp?->evaluator_score!==null) $avg=round(((float)$resp->auto_score+(float)$resp->evaluator_score)/2,1);
                                    elseif($resp?->auto_score!==null) $avg=(float)$resp->auto_score;
                                @endphp
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Promedio</span>
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm border {{ $avg!==null ? (($avg>=4.5?'bg-emerald-50 text-emerald-700 border-emerald-200':($avg>=3.5?'bg-blue-50 text-blue-700 border-blue-200':($avg>=2.5?'bg-amber-50 text-amber-700 border-amber-200':'bg-rose-50 text-rose-700 border-rose-200')))) : 'bg-slate-50 text-slate-300 border-slate-200' }}" id="avg-{{ $c->id }}">
                                        {{ $avg !== null ? $avg : '—' }}
                                    </div>
                                </div>

                                @if(!$isReadOnly)
                                <div class="flex-1 min-w-[150px]">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Observación</span>
                                    <input type="text" name="responses[{{ $c->id }}][comment]" value="{{ $resp?->comment }}"
                                           placeholder="Escribe un comentario…"
                                           class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs text-slate-700 focus:ring-2 focus:ring-blue-400 focus:border-transparent bg-white hover:border-slate-300 transition-colors placeholder-slate-300">
                                </div>
                                @elseif($resp?->comment)
                                <div class="flex-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Observación</span>
                                    <span class="text-xs text-slate-500 italic">{{ $resp->comment }}</span>
                                </div>
                                @endif
                                @endif
                            </div>

                            @if($canBuild)
                            <button x-show="editMode" type="button" @click="removeCriteria({{ $c->id }}, {{ $section->id }})"
                                    class="flex-shrink-0 w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-400 hover:text-rose-600 transition-colors self-center"
                                    title="Eliminar criterio">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if($section->criteria->where('is_active',true)->count() === 0)
                    <div class="px-5 py-8 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-sm text-slate-400">Sin criterios en esta sección</p>
                        @if($canBuild)
                        <p class="text-xs text-slate-300 mt-1">Activa el editor para agregar criterios</p>
                        @endif
                    </div>
                    @endif
                </div>

                @if($canBuild)
                <div x-show="editMode" class="px-5 pb-4 pt-3 border-t border-dashed border-slate-200">
                    <div x-data="{ show: false, name: '', desc: '', submitting: false }">
                        <button type="button" @click="show=!show"
                                class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors bg-blue-50 hover:bg-blue-100 px-3 py-2 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Agregar criterio a esta sección
                        </button>
                        <div x-show="show" x-transition class="mt-3 bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" x-model="name" placeholder="Nombre del criterio *"
                                       class="flex-1 px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       :disabled="submitting"
                                       @keydown.enter="if(name.trim()&&!submitting){submitting=true;$dispatch('add-criteria',{sectionId:{{ $section->id }},name,desc});name='';desc='';setTimeout(()=>{submitting=false},3000)}">
                                <input type="text" x-model="desc" placeholder="Descripción (opcional)"
                                       class="flex-1 px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       :disabled="submitting">
                                <button type="button" :disabled="submitting || !name.trim()"
                                        @click="if(name.trim()&&!submitting){submitting=true;$dispatch('add-criteria',{sectionId:{{ $section->id }},name,desc});name='';desc='';setTimeout(()=>{submitting=false},3000)}"
                                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-colors whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-2">
                                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                    <span x-text="submitting ? 'Agregando…' : 'Agregar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Save button --}}
    @if(!$isReadOnly)
    <form id="save-form" method="POST" action="{{ route('evaluations.save-responses', $evaluation) }}">
        @csrf
        <div id="hidden-scores-container"></div>
        <div class="flex justify-end">
            @if($isEmployee)
            <button type="submit"
                    onclick="return confirm('⚠️ ¿Finalizar y enviar tu autoevaluación?\n\nUna vez enviada, no podrás modificar tus respuestas. Asegúrate de haber revisado todos los criterios.')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-2xl transition-all shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:shadow-emerald-500/30 text-sm hover:-translate-y-0.5 active:translate-y-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar y finalizar evaluación
            </button>
            @else
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl transition-all shadow-lg shadow-blue-500/20 hover:shadow-xl hover:shadow-blue-500/30 text-sm hover:-translate-y-0.5 active:translate-y-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar calificaciones
            </button>
            @endif
        </div>
    </form>
    @endif

    {{-- Observations --}}
    @if($isEvaluator)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-slate-700 text-white flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-slate-800 text-sm">Observaciones generales</h2>
                <p class="text-xs text-slate-400">Solo visible para jefe y RRHH</p>
            </div>
        </div>
        <form method="POST" action="{{ route('evaluations.save-observations', $evaluation) }}" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach(['obs_organizacional'=>'Organizacionales','obs_cargo'=>'Del Cargo','obs_responsabilidades'=>'Responsabilidades'] as $field => $label)
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ $label }}</label>
                    <textarea name="{{ $field }}" rows="4" {{ $isReadOnly?'readonly':'' }}
                              class="w-full border border-slate-200 rounded-xl text-sm px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none {{ $isReadOnly?'bg-slate-50 text-slate-500':'' }}"
                              placeholder="Observaciones sobre {{ strtolower($label) }}…">{{ $evaluation->$field }}</textarea>
                </div>
                @endforeach
            </div>
            @if(!$isReadOnly)
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar observaciones
                </button>
            </div>
            @endif
        </form>
    </div>
    @endif

    {{-- Score Summary --}}
    @if($evaluation->final_score || $evaluation->total_auto_score)
    @php
        $finalScore = (float)$evaluation->final_score;
        $interp = $evaluation->final_score ? $evaluation->getInterpretation() : null;
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h2 class="font-bold text-slate-800 text-sm">Resumen de puntajes</h2>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                <div class="text-center p-5 bg-blue-50 rounded-2xl border border-blue-100">
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Total Autoevaluación</p>
                    <p class="text-4xl font-black text-blue-700 mt-2">{{ number_format((float)$evaluation->total_auto_score,1) }}</p>
                </div>
                @if($isEvaluator)
                <div class="text-center p-5 bg-sky-50 rounded-2xl border border-sky-100">
                    <p class="text-xs font-bold text-sky-500 uppercase tracking-wider">Total Jefe</p>
                    <p class="text-4xl font-black text-sky-700 mt-2">{{ number_format((float)$evaluation->total_evaluator_score,1) }}</p>
                </div>
                @endif
                <div class="text-center p-5 bg-emerald-50 rounded-2xl border border-emerald-100">
                    <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider">Puntaje Final</p>
                    <p class="text-4xl font-black text-emerald-700 mt-2">{{ $evaluation->final_score ? number_format($finalScore,1) : '—' }}</p>
                </div>
            </div>
            @if($interp)
            @php $ic = ['green'=>'bg-emerald-100 text-emerald-800 border-emerald-200','blue'=>'bg-blue-100 text-blue-800 border-blue-200','yellow'=>'bg-amber-100 text-amber-800 border-amber-200','red'=>'bg-rose-100 text-rose-800 border-rose-200']; @endphp
            <div class="p-5 rounded-2xl border {{ $ic[$interp['color']] }} text-center">
                <p class="text-sm font-semibold mb-1">Resultado final</p>
                <p class="text-2xl font-black">{{ $interp['label'] }}</p>
                <p class="text-sm mt-1 opacity-75">Puntaje: {{ number_format($finalScore,1) }} pts</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Development Plan --}}
    @if($isEvaluator)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-blue-50 border-b border-blue-200 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-slate-800 text-sm">Plan de Desarrollo</h2>
                <p class="text-xs text-blue-600">Acciones de mejora para el empleado</p>
            </div>
        </div>
        @if($evaluation->developmentPlans->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left font-bold text-slate-600">Competencia</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600">Actividad</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600">Responsable</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600">Seguimiento</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-600">Notas</th>
                        @if(!$isReadOnly)<th class="px-4 py-3 w-20"></th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($evaluation->developmentPlans as $plan)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $plan->competencia }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $plan->actividad }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $plan->responsable }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $plan->fecha_seguimiento?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-400">{{ $plan->observaciones ?? '—' }}</td>
                        @if(!$isReadOnly)
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('development-plans.destroy',$plan) }}" class="inline" onsubmit="return confirm('⚠️ ¿Eliminar este ítem del plan de desarrollo?\n\nSe perderá la actividad y sus observaciones. Esta acción no se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-white font-semibold bg-rose-100 hover:bg-rose-600 px-2.5 py-1.5 rounded-lg border border-rose-200 hover:border-rose-600 transition-all duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Eliminar
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-8 text-center">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <p class="text-sm text-slate-400">Sin ítems en el plan de desarrollo</p>
            <p class="text-xs text-slate-300 mt-1">Agrega actividades de mejora para el empleado</p>
        </div>
        @endif
        @if(!$isReadOnly)
        <form method="POST" action="{{ route('development-plans.store', $evaluation) }}" class="p-5 border-t border-slate-100 bg-slate-50/50">
            @csrf
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Agregar ítem al plan
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-3">
                <input type="text" name="competencia" required placeholder="Competencia a mejorar *" class="px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                <input type="text" name="actividad" required placeholder="Actividad de mejora *" class="px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                <input type="text" name="responsable" required placeholder="Responsable *" class="px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                <input type="date" name="fecha_seguimiento" class="px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                <input type="text" name="observaciones" placeholder="Observaciones" class="sm:col-span-2 px-3 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Agregar ítem
                </button>
            </div>
        </form>
        @endif
    </div>
    @endif

</div>

{{-- Reopen info banner (when evaluation was reopened) --}}
@if($evaluation->reopened_at)
<div class="max-w-5xl mx-auto mt-4">
    <div class="rounded-2xl border overflow-hidden bg-orange-50 border-orange-200">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-orange-100">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-orange-800">Esta evaluación fue reabierta</p>
                <p class="text-xs text-orange-700 mt-1"><strong>Motivo:</strong> {{ $evaluation->reopen_reason }}</p>
                <p class="text-xs text-orange-700 mt-0.5"><strong>Fecha límite:</strong> {{ $evaluation->reopen_deadline?->format('d/m/Y') }}</p>
                <p class="text-xs text-orange-500 mt-0.5">Reabierta el {{ $evaluation->reopened_at->format('d/m/Y H:i') }} por {{ $evaluation->reopenedByUser?->name ?? 'RR.HH.' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Modal: Reopen evaluation --}}
@if($isEvaluator && in_array($evaluation->status, ['completada', 'revisada']))
<div id="modal-reopen" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reabrir evaluación
                </h3>
                <button onclick="document.getElementById('modal-reopen').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('evaluations.reopen', $evaluation) }}" class="p-6 space-y-4">
            @csrf
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3">
                <p class="text-xs text-orange-700">
                    <strong>Atención:</strong> Al reabrir la evaluación, el empleado podrá modificar sus respuestas nuevamente. Se notificará al empleado y a su jefe.
                </p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Motivo de la reapertura *</label>
                <textarea name="reopen_reason" required minlength="10" maxlength="500" rows="3"
                          class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"
                          placeholder="Explica por qué se necesita reabrir esta evaluación..."></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Fecha límite *</label>
                <input type="date" name="reopen_deadline" required min="{{ now()->addDay()->toDateString() }}"
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-reopen').classList.add('hidden')"
                        class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                <button type="submit"
                        class="btn-bounce px-5 py-2.5 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold rounded-xl transition-all">
                    Reabrir evaluación
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.3/Sortable.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
function evalBuilder() {
    const D = @json($evalDataPayload);
    return {
        editMode: false,
        loading: false,
        saveState: null,
        liveUpdateState: null,
        scores: D.scores,
        totals: D.totals,
        openSections: {},
        sectionsActive: {},
        addSectionForm: { show: false, name: '', type: '', typeLabel: '' },
        completedCount: 0,
        totalCount: 0,
        progress: 0,
        addingCriteria: {},
        _pollTimer: null,

        init() {
            document.querySelectorAll('.section-card').forEach(el => {
                const id = el.dataset.sectionId;
                this.openSections[id] = true;
                this.sectionsActive[id] = !el.classList.contains('opacity-40');
            });
            this.recalc();

            window.addEventListener('add-criteria', e => {
                const key = e.detail.sectionId + ':' + e.detail.name;
                if (this.addingCriteria[key]) return;
                this.addingCriteria[key] = true;
                this.addCriteria(e.detail.sectionId, e.detail.name, e.detail.desc).finally(() => {
                    delete this.addingCriteria[key];
                });
            });

            // Polling en tiempo real — activo mientras la evaluación no esté revisada
            @if(!in_array($evaluation->status, ['revisada']))
            this.startPolling();
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) { this.stopPolling(); } else { this.startPolling(); }
            });
            @endif
        },

        startPolling() {
            if (this._pollTimer) return;
            this._pollTimer = setInterval(() => this.pollForUpdates(), 6000);
        },

        stopPolling() {
            if (this._pollTimer) { clearInterval(this._pollTimer); this._pollTimer = null; }
        },

        async pollForUpdates() {
            try {
                const res = await fetch(D.routes.liveState, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': D.csrf }
                });
                if (!res.ok) return;
                const data = await res.json();

                let changed = false;

                // Actualizar puntajes de LA OTRA PARTE (no los propios que ya están en scores)
                for (const [id, remote] of Object.entries(data.scores)) {
                    if (!this.scores[id]) this.scores[id] = { auto: null, evaluator: null, comment: '' };
                    const field = D.isEmployee ? 'evaluator' : 'auto';
                    if (this.scores[id][field] !== remote[field]) {
                        this.scores[id][field] = remote[field];
                        changed = true;

                        // Actualizar el badge visual del promedio en el DOM
                        const avgEl = document.getElementById('avg-' + id);
                        if (avgEl) {
                            const a = this.scores[id].auto, e = this.scores[id].evaluator;
                            let avg = null;
                            if (a !== null && e !== null) avg = Math.round(((a + e) / 2) * 10) / 10;
                            else if (a !== null) avg = a;
                            avgEl.textContent = avg !== null ? avg : '—';
                        }

                        // Actualizar el badge de autoevaluación visible al jefe (en readonly)
                        if (!D.isEmployee) {
                            const autoEl = document.getElementById('auto-badge-' + id);
                            if (autoEl && remote.auto !== null) {
                                const labels = {1:'Deficiente',2:'Regular',3:'Cumple',4:'Supera',5:'Sobresale'};
                                autoEl.textContent = remote.auto + ' · ' + (labels[remote.auto] || '');
                                autoEl.className = autoEl.className.replace(/bg-\w+-100|text-\w+-700/g, '');
                                const cls = remote.auto>=5?'bg-emerald-100 text-emerald-700':remote.auto>=4?'bg-blue-100 text-blue-700':remote.auto>=3?'bg-amber-100 text-amber-700':remote.auto>=2?'bg-orange-100 text-orange-700':'bg-rose-100 text-rose-700';
                                autoEl.classList.add(...cls.split(' '));
                            }
                        }
                    }
                }

                // Actualizar totales
                if (Math.abs((data.total_auto_score||0) - this.totals.auto) > 0.001 ||
                    Math.abs((data.total_evaluator_score||0) - this.totals.evaluator) > 0.001 ||
                    Math.abs((data.final_score||0) - this.totals.final) > 0.001) {
                    this.totals.auto = data.total_auto_score || 0;
                    this.totals.evaluator = data.total_evaluator_score || 0;
                    this.totals.final = data.final_score || 0;
                    changed = true;
                }

                if (changed) {
                    this.recalc();
                    this.liveUpdateState = 'updated';
                    setTimeout(() => { this.liveUpdateState = null; }, 3000);
                }

                // Si el estado cambia, recargar la página para reflejar el nuevo estado
                if (data.status !== @json($evaluation->status)) {
                    this.stopPolling();
                    window.location.reload();
                }
            } catch(e) { /* silenciar errores de red */ }
        },

        recalc() {
            let done = 0, total = 0;
            const field = D.isEmployee ? 'auto' : 'evaluator';
            for (const [id, s] of Object.entries(this.scores)) {
                const el = document.getElementById('row-' + id);
                if (!el) continue;
                const sec = el.closest('.section-card');
                if (!sec) continue;
                const secType = sec.querySelector('[data-section-type]')?.dataset.sectionType;
                if (secType === 'rango') continue;
                total++;
                if (s[field] !== null) done++;
            }
            this.completedCount = done;
            this.totalCount = total || @json($totalCount);
            this.progress = this.totalCount > 0 ? Math.round((this.completedCount / this.totalCount) * 100) : 0;
        },

        scoreActiveClass(v) {
            const m = {1:'bg-rose-500 text-white shadow-lg shadow-rose-500/30 ring-2 ring-rose-300',2:'bg-orange-500 text-white shadow-lg shadow-orange-500/30 ring-2 ring-orange-300',3:'bg-amber-400 text-white shadow-lg shadow-amber-400/30 ring-2 ring-amber-300',4:'bg-blue-500 text-white shadow-lg shadow-blue-500/30 ring-2 ring-blue-300',5:'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30 ring-2 ring-emerald-300'};
            return m[v]||'';
        },

        async setScore(criteriaId, type, value) {
            if (D.isReadOnly) return;
            if (!this.scores[criteriaId]) this.scores[criteriaId] = {auto:null,evaluator:null,comment:''};
            this.scores[criteriaId][type] = value;
            this.recalc();
            this.saveState = 'saving';
            try {
                const fieldName = type === 'auto' ? 'auto_score' : 'evaluator_score';
                const res = await fetch(D.routes.saveScore + '/' + criteriaId, {
                    method: 'PATCH',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':D.csrf,'Accept':'application/json'},
                    body: JSON.stringify({[fieldName]: value})
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.total_auto_score !== undefined) {
                        this.totals.auto = parseFloat(data.total_auto_score)||0;
                        this.totals.evaluator = parseFloat(data.total_evaluator_score)||0;
                        this.totals.final = parseFloat(data.final_score)||0;
                    }
                    this.saveState = 'saved';
                    setTimeout(() => this.saveState = null, 2000);
                } else {
                    console.error('Save failed:', res.status);
                    this.saveState = null;
                }
            } catch(e) { console.error('Save error:', e); this.saveState = null; }
        },

        promptAddSection(type) {
            const labels = {competencias_org:'Comp. Organizacionales',competencias_cargo:'Comp. del Cargo',responsabilidades:'Responsabilidades',rango:'Tabla de Rangos'};
            this.addSectionForm = { show:true, name:'', type, typeLabel:labels[type]||type };
        },

        async submitAddSection() {
            if (!this.addSectionForm.name.trim() || this.loading) return;
            this.loading = true;
            try {
                const res = await fetch(D.routes.addSection, {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':D.csrf,'Accept':'application/json'},
                    body: JSON.stringify({name:this.addSectionForm.name, type:this.addSectionForm.type})
                });
                if (res.ok) {
                    this.addSectionForm.show = false;
                    window.location.reload();
                } else {
                    const err = await res.json().catch(()=>({}));
                    alert(err.message || 'Error al agregar sección');
                }
            } catch(e) { alert('Error de conexión'); }
            finally { this.loading = false; }
        },

        async addCriteria(sectionId, name, desc) {
            if (this.loading) return;
            this.loading = true;
            try {
                const url = D.routes.addCriteria + '/' + sectionId;
                const res = await fetch(url, {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':D.csrf,'Accept':'application/json'},
                    body: JSON.stringify({name, description:desc||''})
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.duplicate) {
                        alert('Este criterio ya existe en la sección.');
                    }
                    window.location.reload();
                } else {
                    const err = await res.json().catch(()=>({}));
                    alert(err.message || 'Error al agregar criterio');
                }
            } catch(e) { alert('Error de conexión'); }
            finally { this.loading = false; }
        },

        async removeCriteria(criteriaId, sectionId) {
            if (!confirm('⚠️ ¿Eliminar este criterio de la evaluación?\n\nSe eliminarán las respuestas asociadas a este criterio. Esta acción no se puede deshacer.')) return;
            if (this.loading) return;
            this.loading = true;
            try {
                const res = await fetch(D.routes.rmCriteria+'/'+criteriaId, {
                    method:'DELETE',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':D.csrf,'Accept':'application/json'}
                });
                if (res.ok) window.location.reload();
            } catch(e) { alert('Error al eliminar'); }
            finally { this.loading = false; }
        },

        async toggleSection(sectionId) {
            const url = D.routes.toggle + '/' + sectionId;
            const res = await fetch(url, {
                method:'PUT',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':D.csrf,'Accept':'application/json'},
                body: JSON.stringify({section_id: sectionId})
            });
            if (res.ok) {
                this.sectionsActive[sectionId] = !this.sectionsActive[sectionId];
            }
        },

        initSortable() {
            const container = document.getElementById('sections-container');
            if (!container) return;
            new Sortable(container, {
                handle: '.drag-handle',
                animation: 200,
                ghostClass: 'opacity-30',
                onEnd: () => this.saveOrder()
            });
            container.querySelectorAll('[id^="criteria-"]').forEach(el => {
                new Sortable(el, {
                    handle: '.drag-handle',
                    animation: 200,
                    ghostClass: 'opacity-30',
                    onEnd: () => this.saveOrder()
                });
            });
        },

        async saveOrder() {
            const sections = [];
            document.querySelectorAll('.section-card').forEach((el, i) => {
                const sid = el.dataset.sectionId;
                const criteria = [];
                el.querySelectorAll('[data-criteria-id]').forEach((c, j) => {
                    criteria.push({id:c.dataset.criteriaId, order:j});
                });
                sections.push({id:sid, order:i, criteria});
            });
            await fetch(D.routes.reorder, {
                method:'PUT',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':D.csrf,'Accept':'application/json'},
                body: JSON.stringify({sections})
            });
        }
    };
}

document.getElementById('save-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const container = document.getElementById('hidden-scores-container');
    container.innerHTML = '';
    const el = document.querySelector('[x-data="evalBuilder()"]');
    const comp = el ? Alpine.$data(el) : null;
    if (comp && comp.scores) {
        for (const [id, s] of Object.entries(comp.scores)) {
            // Always add criteria_id (required by validation)
            const criteriaInput = document.createElement('input');
            criteriaInput.type = 'hidden';
            criteriaInput.name = `responses[${id}][criteria_id]`;
            criteriaInput.value = id;
            container.appendChild(criteriaInput);
            
            if (s.auto !== null) {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = `responses[${id}][auto_score]`; i.value = s.auto;
                container.appendChild(i);
            }
            if (s.evaluator !== null) {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = `responses[${id}][evaluator_score]`; i.value = s.evaluator;
                container.appendChild(i);
            }
            if (s.comment) {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = `responses[${id}][comment]`; i.value = s.comment;
                container.appendChild(i);
            }
        }
    }
    this.submit();
});
</script>
@endpush
