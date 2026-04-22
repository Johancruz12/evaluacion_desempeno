@extends('layouts.app')
@section('title', 'Supervisión de Jefes y Coordinadores')

@section('content')
@php
$statusMap = [
    'pendiente'   => ['Pendiente',   'bg-amber-100 text-amber-700 border-amber-200'],
    'en_progreso' => ['En progreso', 'bg-blue-100 text-blue-700 border-blue-200'],
    'completada'  => ['Completada',  'bg-emerald-100 text-emerald-700 border-emerald-200'],
    'revisada'    => ['Revisada',    'bg-purple-100 text-purple-700 border-purple-200'],
];
$totalJefes     = $jefesData->flatten(1)->count();
$conCuenta      = $jefesData->flatten(1)->filter(fn($j) => !is_null($j['local_user']))->count();
$conEvaluacion  = $jefesData->flatten(1)->filter(fn($j) => $j['jefe_eval_count'] > 0)->count();
$completados    = $jefesData->flatten(1)->filter(fn($j) => $j['jefe_completed'] > 0)->count();
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="anim-slide-left">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30 flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">Jefes y Coordinadores</h1>
                <p class="text-slate-500 text-sm mt-0.5">Supervisión de líderes con personal a cargo — datos desde Salomón</p>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 anim-fade-up">
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $totalJefes }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Total jefes</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $conCuenta }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Con cuenta</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $conEvaluacion }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Con evaluación</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 px-5 py-4 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><p class="text-2xl font-extrabold text-slate-800">{{ $completados }}</p><p class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Completaron</p></div>
        </div>
    </div>

    {{-- No Salomón data warning --}}
    @if($jefesData->isEmpty())
    <div class="anim-fade-up bg-amber-50 border border-amber-200 rounded-2xl px-6 py-8 text-center">
        <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-amber-800 font-bold text-base">Sin datos de Salomón</p>
        <p class="text-amber-600 text-sm mt-1">No se encontraron jefes con personal a cargo en el sistema Salomón. Verifica la conexión a la base de datos.</p>
    </div>
    @else

    {{-- Grouped by area --}}
    @foreach($jefesData as $areaNombre => $jefes)
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ open: true }">
        <div class="h-1 bg-gradient-to-r from-violet-400 via-purple-400 to-indigo-400"></div>

        {{-- Area header --}}
        <button @click="open=!open" class="w-full flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-md">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="text-left">
                    <h3 class="font-bold text-slate-800">{{ $areaNombre }}</h3>
                    <p class="text-xs text-slate-500">{{ $jefes->count() }} jefe(s)/coordinador(es)</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php
                $areaCompleted = $jefes->filter(fn($j) => $j['jefe_completed'] > 0)->count();
                $areaTotal = $jefes->count();
                @endphp
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $areaCompleted === $areaTotal ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $areaCompleted }}/{{ $areaTotal }}
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </button>

        {{-- Jefes list --}}
        <div x-show="open" x-collapse>
            {{-- Table header --}}
            <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-2.5 bg-slate-50 border-y border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                <div class="col-span-3">Nombre</div>
                <div class="col-span-3">Cargo</div>
                <div class="col-span-2">Cédula</div>
                <div class="col-span-1 text-center">Personal</div>
                <div class="col-span-2">Estado eval.</div>
                <div class="col-span-1 text-right">Acciones</div>
            </div>

            <div class="divide-y divide-slate-50">
            @foreach($jefes->sortBy('primer_apellido') as $jefe)
            @php
                $localUser = $jefe['local_user'];
                $localArea = $jefe['local_area'];
                $latestEval = $localUser?->evaluationsAsEmployee->first();
            @endphp
            <div class="flex flex-col md:grid md:grid-cols-12 md:gap-4 md:items-center px-6 py-4 hover:bg-violet-50/30 transition-colors">

                {{-- Nombre --}}
                <div class="col-span-3 flex items-center gap-3 min-w-0">
                    @php
                    $initials = strtoupper(substr($jefe['nombre'], 0, 1));
                    $spacePos = strpos($jefe['nombre'], ' ');
                    if ($spacePos !== false) $initials .= strtoupper(substr($jefe['nombre'], $spacePos + 1, 1));
                    @endphp
                    <div class="w-9 h-9 rounded-full {{ is_null($localUser) ? 'bg-rose-300' : 'bg-gradient-to-br from-violet-400 to-purple-500' }} flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $jefe['nombre'] }}</p>
                        @if(is_null($localUser))
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 text-rose-500 border border-rose-200 mt-0.5">Sin cuenta</span>
                        @endif
                    </div>
                </div>

                {{-- Cargo --}}
                <div class="col-span-3 ml-12 md:ml-0 mt-0.5 md:mt-0">
                    <p class="text-xs text-slate-500 truncate">{{ $jefe['cargo'] }}</p>
                </div>

                {{-- Cédula --}}
                <div class="col-span-2 ml-12 md:ml-0">
                    <p class="text-xs text-slate-400 font-mono">{{ $jefe['cedula'] }}</p>
                </div>

                {{-- Personal a cargo --}}
                <div class="col-span-1 ml-12 md:ml-0 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-extrabold text-sm">
                        {{ $jefe['total_empleados'] }}
                    </span>
                </div>

                {{-- Estado evaluación --}}
                <div class="col-span-2 ml-12 md:ml-0 mt-1 md:mt-0">
                    @if(is_null($localUser))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border bg-slate-50 text-slate-400 border-slate-200">No registrado</span>
                    @elseif($jefe['jefe_eval_count'] === 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border bg-slate-50 text-slate-500 border-slate-200">Sin evaluación</span>
                    @else
                        @php [$sl, $sc] = $statusMap[$latestEval?->status ?? 'pendiente'] ?? ['—','bg-slate-100 text-slate-500 border-slate-200']; @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $sc }}">{{ $sl }}</span>
                    @endif
                </div>

                {{-- Acciones --}}
                <div class="col-span-1 ml-12 md:ml-0 mt-2 md:mt-0 flex justify-end gap-1.5">
                    @if($latestEval)
                    <a href="{{ route('evaluations.export-pdf', $latestEval) }}"
                       class="w-8 h-8 rounded-xl bg-rose-100 border border-rose-200 hover:bg-gradient-to-r hover:from-rose-500 hover:to-pink-500 flex items-center justify-center text-rose-600 hover:text-white hover:border-rose-500 transition-all shadow-sm"
                       title="PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                    <a href="{{ route('evaluations.show', $latestEval) }}"
                       class="w-8 h-8 rounded-xl bg-violet-100 border border-violet-200 hover:bg-gradient-to-r hover:from-violet-500 hover:to-purple-500 flex items-center justify-center text-violet-600 hover:text-white hover:border-violet-500 transition-all shadow-sm"
                       title="Ver evaluación">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    @elseif($localArea)
                    <a href="{{ route('evaluations.index', ['area_id' => $localArea->id]) }}"
                       class="w-8 h-8 rounded-xl bg-blue-100 border border-blue-200 hover:bg-gradient-to-r hover:from-blue-500 hover:to-sky-500 flex items-center justify-center text-blue-600 hover:text-white hover:border-blue-500 transition-all shadow-sm"
                       title="Ver evaluaciones del área">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
            </div>
        </div>
    </div>
    @endforeach
    @endif

</div>
@endsection
