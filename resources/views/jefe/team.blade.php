@extends('layouts.app')
@section('title', 'Mi Equipo')

@section('content')
@php
$user = auth()->user();
$statusMap = [
    'pendiente'   => ['Pendiente',   'bg-amber-100 text-amber-700 border-amber-200'],
    'en_progreso' => ['En progreso', 'bg-blue-100 text-blue-700 border-blue-200'],
    'completada'  => ['Completada',  'bg-emerald-100 text-emerald-700 border-emerald-200'],
    'revisada'    => ['Revisada',    'bg-purple-100 text-purple-700 border-purple-200'],
];
$totalMiembros  = $teamData->count();
$sinEvaluacion  = $teamData->filter(fn($m) => $m['total_evaluations'] === 0)->count();
$enProgreso     = $teamData->filter(fn($m) => $m['has_in_progress'])->count();
$completadas    = $teamData->filter(fn($m) => $m['completed_count'] > 0)->count();
$sinCuenta      = $teamData->filter(fn($m) => is_null($m['local_user']))->count();
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="anim-slide-left">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">Mi Equipo</h1>
                <p class="text-slate-500 text-sm mt-0.5">
                    {{ $area?->name ?? 'Sin área asignada' }}
                    @if($usingSalomon)
                    <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>
                        Datos Salomón
                    </span>
                    @else
                    <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                        Base de datos local
                    </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Error de conexión a Salomón (no rompe la página) --}}
    @if(!empty($salomonError))
    <div class="anim-fade-up bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-sm">
            <p class="font-bold text-amber-800">Mostrando solo datos del archivo importado</p>
            <p class="text-amber-700 text-xs mt-0.5">No se pudo conectar a Salomón. Los miembros mostrados provienen únicamente de la base de datos local.</p>
        </div>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 anim-fade-up">
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $totalMiembros }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Miembros</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $sinEvaluacion }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Sin evaluar</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $enProgreso }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">En progreso</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $completadas }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Completadas</p></div>
        </div>
    </div>

    {{-- Progress bar --}}
    @if($totalMiembros > 0)
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-4">
        @php $pct = $totalMiembros > 0 ? round(($completadas / $totalMiembros) * 100) : 0; @endphp
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-bold text-slate-700">Progreso del equipo</span>
            <span class="text-sm font-extrabold {{ $pct >= 80 ? 'text-emerald-600' : ($pct >= 40 ? 'text-blue-600' : 'text-amber-600') }}">{{ $pct }}%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
            <div class="h-3 rounded-full transition-all duration-700 {{ $pct >= 80 ? 'bg-gradient-to-r from-emerald-400 to-blue-500' : ($pct >= 40 ? 'bg-gradient-to-r from-blue-400 to-indigo-500' : 'bg-gradient-to-r from-amber-400 to-orange-500') }}"
                 style="width: {{ $pct }}%"></div>
        </div>
        <p class="text-xs text-slate-400 mt-2">{{ $completadas }} de {{ $totalMiembros }} empleados han completado su evaluación</p>
    </div>
    @endif

    {{-- Sin cuenta en sistema --}}
    @if($sinCuenta > 0)
    <div class="anim-fade-up bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <div>
            <p class="text-sm font-bold text-amber-800">{{ $sinCuenta }} empleado(s) no tienen cuenta en el sistema</p>
            <p class="text-xs text-amber-600 mt-0.5">Importa estos empleados en la sección <strong>Empleados</strong> para que puedan acceder y realizar sus evaluaciones.</p>
        </div>
    </div>
    @endif

    {{-- Team table --}}
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400"></div>
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
                Empleados a cargo
            </h2>
        </div>

        @if($teamData->isEmpty())
        <div class="py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/></svg>
            </div>
            <p class="text-slate-700 font-bold">Sin empleados registrados</p>
            <p class="text-slate-400 text-sm mt-1">
                @if(!$area?->salomon_codigo)
                El área no tiene código Salomón configurado. Contacta al administrador.
                @else
                No se encontraron empleados activos en esta área en Salomón.
                @endif
            </p>
        </div>
        @else

        {{-- Table header (desktop) --}}
        <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-3 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
            <div class="col-span-4">Empleado</div>
            <div class="col-span-3">Cargo</div>
            <div class="col-span-2">Cédula</div>
            <div class="col-span-2">Estado</div>
            <div class="col-span-1 text-right">Acciones</div>
        </div>

        <div class="divide-y divide-slate-50">
        @foreach($teamData as $member)
        @php
            $localUser  = $member['local_user'];
            $latestEval = $member['latest_evaluation'];
            $initials   = strtoupper(substr($member['nombre_completo'], 0, 1)) . (strpos($member['nombre_completo'], ' ') !== false ? strtoupper(substr($member['nombre_completo'], strpos($member['nombre_completo'], ' ') + 1, 1)) : '');

            if ($member['completed_count'] > 0) {
                $avatarClass = 'bg-gradient-to-br from-emerald-400 to-blue-500';
            } elseif ($member['has_in_progress']) {
                $avatarClass = 'bg-gradient-to-br from-blue-400 to-indigo-500';
            } elseif ($member['has_pending']) {
                $avatarClass = 'bg-gradient-to-br from-amber-400 to-orange-500';
            } elseif (is_null($localUser)) {
                $avatarClass = 'bg-gradient-to-br from-rose-300 to-rose-400';
            } else {
                $avatarClass = 'bg-gradient-to-br from-slate-300 to-slate-400';
            }
        @endphp
        <div class="flex flex-col md:grid md:grid-cols-12 md:gap-4 items-start md:items-center px-6 py-4 hover:bg-slate-50/70 transition-colors group">

            {{-- Empleado --}}
            <div class="col-span-4 flex items-center gap-3 min-w-0 w-full">
                <div class="w-10 h-10 rounded-full {{ $avatarClass }} flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $member['nombre_completo'] }}</p>
                    @if(is_null($localUser))
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-500 border border-rose-200 mt-0.5">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Sin cuenta
                    </span>
                    @endif
                </div>
            </div>

            {{-- Cargo --}}
            <div class="col-span-3 ml-13 md:ml-0 mt-1 md:mt-0">
                <p class="text-xs text-slate-500 truncate">{{ $member['cargo'] ?? ($localUser?->positionType?->name ?? '—') }}</p>
            </div>

            {{-- Cédula --}}
            <div class="col-span-2 ml-13 md:ml-0">
                <p class="text-xs text-slate-400 font-mono">{{ $member['cedula'] ?? '—' }}</p>
            </div>

            {{-- Estado evaluación --}}
            <div class="col-span-2 ml-13 md:ml-0 mt-1 md:mt-0">
                @if(is_null($localUser))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border bg-slate-50 text-slate-400 border-slate-200">No registrado</span>
                @elseif($member['total_evaluations'] === 0)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border bg-slate-50 text-slate-500 border-slate-200">Sin evaluación</span>
                @else
                    @php [$sl, $sc] = $statusMap[$latestEval?->status ?? 'pendiente'] ?? ['—','bg-slate-100 text-slate-500 border-slate-200']; @endphp
                    <div class="flex flex-col gap-0.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $sc }}">{{ $sl }}</span>
                        @if($latestEval && ($latestEval->final_score || $latestEval->total_auto_score))
                        @php $sc2 = $latestEval->final_score ?? $latestEval->total_auto_score; @endphp
                        <span class="text-xs font-extrabold {{ $sc2 >= 91 ? 'text-emerald-600' : ($sc2 >= 71 ? 'text-blue-600' : ($sc2 >= 50 ? 'text-amber-600' : 'text-rose-600')) }}">
                            {{ number_format($sc2, 1) }} pts
                        </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Acciones --}}
            <div class="col-span-1 ml-13 md:ml-0 mt-2 md:mt-0 flex items-center justify-end gap-1.5">
                @if($latestEval)
                <a href="{{ route('evaluations.export-pdf', $latestEval) }}"
                   class="w-8 h-8 rounded-xl bg-rose-100 border border-rose-200 hover:bg-gradient-to-r hover:from-rose-500 hover:to-pink-500 flex items-center justify-center text-rose-600 hover:text-white hover:border-rose-500 transition-all duration-300 shadow-sm"
                   title="Descargar PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </a>
                <a href="{{ route('evaluations.show', $latestEval) }}"
                   class="w-8 h-8 rounded-xl bg-indigo-100 border border-indigo-200 hover:bg-gradient-to-r hover:from-indigo-500 hover:to-purple-500 flex items-center justify-center text-indigo-600 hover:text-white hover:border-indigo-500 transition-all duration-300 shadow-sm"
                   title="Ver evaluación">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                @elseif($localUser)
                <span class="text-[10px] text-slate-400 text-right">Sin<br>evaluación</span>
                @endif
            </div>
        </div>
        @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
