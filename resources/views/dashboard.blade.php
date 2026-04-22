@extends('layouts.app')
@section('title', 'Inicio')

@section('content')
@php $user = auth()->user(); @endphp

{{-- ═══ ADMIN ═══ --}}
@if($user->isAdmin())

<div class="space-y-6">

    {{-- Welcome --}}
    <div class="anim-slide-left bg-gradient-to-r from-blue-600 via-blue-500 to-sky-500 hero-shimmer rounded-2xl p-6 text-white shadow-lg shadow-blue-500/20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent pointer-events-none"></div>
        <p class="text-blue-200 text-sm relative">{{ now()->isoFormat('dddd, D [de] MMMM YYYY') }}</p>
        <h2 class="text-2xl font-bold mt-1 relative">Hola, {{ explode(' ', $user->name)[0] }} 👋</h2>
        <p class="text-blue-200 text-sm mt-1 relative">Panel de administración · Recursos Humanos</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
        $stats = [
            ['label'=>'Empleados', 'value'=>$totalEmployees ?? 0, 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'blue'],
            ['label'=>'Pendientes', 'value'=>$pendingEvaluations ?? 0, 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'amber'],
            ['label'=>'En Progreso', 'value'=>$inProgressEvaluations ?? 0, 'icon'=>'M13 10V3L4 14h7v7l9-11h-7z', 'color'=>'blue'],
            ['label'=>'Completadas', 'value'=>$completedEvaluations ?? 0, 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'emerald'],
        ];
        $palette = ['blue'=>['bg'=>'bg-blue-50','icon'=>'bg-blue-500','text'=>'text-blue-600'],'amber'=>['bg'=>'bg-amber-50','icon'=>'bg-amber-500','text'=>'text-amber-600'],'sky'=>['bg'=>'bg-sky-50','icon'=>'bg-sky-500','text'=>'text-sky-600'],'emerald'=>['bg'=>'bg-emerald-50','icon'=>'bg-emerald-500','text'=>'text-emerald-600']];
        @endphp
        @foreach($stats as $s)
        @php $p = $palette[$s['color']]; @endphp
        <div class="anim-fade-up stat-card {{ $p['bg'] }} rounded-2xl p-5 border border-white group cursor-default">
            <div class="{{ $p['icon'] }} w-10 h-10 rounded-xl flex items-center justify-center shadow mb-3 group-hover:scale-110 transition-transform duration-300">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                </svg>
            </div>
            <p class="count-anim text-2xl font-bold text-slate-800">{{ $s['value'] }}</p>
            <p class="text-slate-500 text-sm mt-0.5">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Score distribution --}}
    @if(!empty($scoreBreakdown))
    <div class="anim-fade-up">
        <h3 class="text-slate-700 font-semibold mb-3">Distribución de resultados</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($scoreBreakdown as $b)
            <div class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-all duration-300 hover:-translate-y-1 cursor-default">
                <p class="text-xs font-semibold text-slate-500 mb-1">{{ $b['label'] }}</p>
                <p class="text-xl font-bold text-slate-800">{{ $b['count'] }}</p>
                @if($completedEvaluations > 0)
                <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full progress-bar" style="--progress:{{ round($b['count']/$completedEvaluations*100) }}%;background:{{ $b['color'] }}"></div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent evaluations --}}
    @if(!empty($recentEvaluations) && count($recentEvaluations))
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Evaluaciones recientes</h3>
            <a href="{{ route('evaluations.index') }}" class="text-blue-600 text-sm font-medium hover:text-blue-700 btn-bounce inline-flex items-center gap-1 group">
                Ver todas
                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($recentEvaluations as $ev)
            @php
            $statusMap=['pendiente'=>['Pendiente','bg-amber-100 text-amber-700'],'en_progreso'=>['En progreso','bg-blue-100 text-blue-700'],'completada'=>['Completada','bg-emerald-100 text-emerald-700']];
            [$slabel,$sclass] = $statusMap[$ev->status] ?? ['—','bg-slate-100 text-slate-500'];
            @endphp
            <div class="px-6 py-3 flex items-center justify-between row-hover">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                        {{ strtoupper(substr($ev->employee?->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $ev->employee?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $ev->period }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $sclass }}">{{ $slabel }}</span>
                    <a href="{{ route('evaluations.show', $ev) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium hover:underline">Ver</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Quick links --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @php $links = [
            ['href'=>route('admin.employees.index'),'label'=>'Empleados','icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197','bg'=>'bg-sky-50','ico'=>'bg-sky-500'],
            ['href'=>route('admin.areas.index'),'label'=>'Áreas','icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5','bg'=>'bg-sky-50','ico'=>'bg-sky-500'],
            ['href'=>route('admin.templates.index'),'label'=>'Plantillas','icon'=>'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z','bg'=>'bg-indigo-50','ico'=>'bg-indigo-500'],
            ['href'=>route('reports.index'),'label'=>'Reportes','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14','bg'=>'bg-emerald-50','ico'=>'bg-emerald-500'],
        ]; @endphp
        @foreach($links as $l)
        <a href="{{ $l['href'] }}" class="anim-fade-up stat-card {{ $l['bg'] }} rounded-xl p-4 flex items-center gap-3 border border-slate-200/70 hover:border-slate-300 transition-all group">
            <div class="{{ $l['ico'] }} w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                <svg class="w-4.5 h-4.5 text-white w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $l['icon'] }}"/>
                </svg>
            </div>
            <span class="font-semibold text-slate-700 text-sm group-hover:text-slate-900 transition-colors">{{ $l['label'] }}</span>
        </a>
        @endforeach
    </div>
</div>

{{-- ═══ JEFE ═══ --}}
@elseif($user->isJefeArea())
@php
$statusMap = [
    'pendiente'   => ['Pendiente',   'bg-amber-100 text-amber-700'],
    'en_progreso' => ['En progreso', 'bg-blue-100 text-blue-700'],
    'completada'  => ['Completada',  'bg-emerald-100 text-emerald-700'],
    'revisada'    => ['Revisada',    'bg-purple-100 text-purple-700'],
];
@endphp

<div class="space-y-6">
    <div class="anim-slide-left bg-gradient-to-r from-blue-600 via-blue-500 to-sky-500 hero-shimmer rounded-2xl p-6 text-white shadow-lg shadow-blue-500/20 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent pointer-events-none"></div>
        <p class="text-blue-200 text-sm relative">{{ now()->isoFormat('dddd, D [de] MMMM YYYY') }}</p>
        <h2 class="text-2xl font-bold mt-1 relative">Hola, {{ explode(' ', $user->name)[0] }} 👋</h2>
        <p class="text-blue-200 text-sm mt-1 relative">{{ $user->area?->name ?? 'Mi área' }}</p>
    </div>

    <div class="grid grid-cols-3 gap-4">
        @php $s2=[['label'=>'Pendientes','value'=>$pendingCount??0,'color'=>'amber'],['label'=>'En Progreso','value'=>$inProgressCount??0,'color'=>'blue'],['label'=>'Completadas','value'=>$completedCount??0,'color'=>'emerald']]; @endphp
        @foreach($s2 as $s)
        @php $p=$palette[$s['color']]??['bg'=>'bg-slate-50','icon'=>'bg-slate-400','text'=>'text-slate-600']; @endphp
        <div class="anim-fade-up stat-card {{ $p['bg'] }} rounded-2xl p-5 border border-white text-center cursor-default">
            <p class="count-anim text-3xl font-bold text-slate-800">{{ $s['value'] }}</p>
            <p class="text-slate-500 text-sm mt-1">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    @if($user->canCreateEvaluations())
    <div class="flex justify-end anim-slide-right">
        <a href="{{ route('evaluations.create') }}"
           class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva Evaluación
        </a>
    </div>
    @endif

    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">Equipo vigente desde Salomón</h3>
                <p class="text-xs text-slate-400 mt-0.5">Personas activas de tu área y estado de evaluación</p>
            </div>
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                Total: {{ $salomonTeamSummary['total'] ?? 0 }}
            </span>
        </div>

        @if(!empty($salomonSyncWarning))
        <div class="mx-6 mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-sm text-amber-700">{{ $salomonSyncWarning }}</p>
        </div>
        @endif

        <div class="px-6 py-4 grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Con evaluación asignada</p>
                <p class="text-lg font-bold text-slate-800">{{ $salomonTeamSummary['withEvaluation'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Sin evaluación asignada</p>
                <p class="text-lg font-bold text-slate-800">{{ $salomonTeamSummary['withoutEvaluation'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs text-emerald-600">Autoevaluación realizada</p>
                <p class="text-lg font-bold text-emerald-700">{{ $salomonTeamSummary['autoDone'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Pendiente por autoevaluar</p>
                <p class="text-lg font-bold text-slate-800">{{ ($salomonTeamSummary['withEvaluation'] ?? 0) - ($salomonTeamSummary['autoDone'] ?? 0) }}</p>
            </div>
        </div>

        @if(!empty($salomonTeam) && count($salomonTeam))
        <div class="overflow-x-auto border-t border-slate-100">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Empleado</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Cédula</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500">Cargo</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500">Evaluación</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500">Autoevaluación</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($salomonTeam as $member)
                    @php
                        $statusCfg = $statusMap[$member['evaluation_status'] ?? ''] ?? ['No asignada', 'bg-slate-100 text-slate-600'];
                    @endphp
                    <tr class="row-hover">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $member['nombre_completo'] ?: 'Sin nombre' }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $member['cedula'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $member['cargo_nombre'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusCfg[1] }}">
                                {{ $member['has_evaluation'] ? $statusCfg[0] : 'No asignada' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($member['has_evaluation'])
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $member['auto_done'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $member['auto_done'] ? 'Sí' : 'No' }}
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Sin asignar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($member['evaluation_id'])
                            <a href="{{ route('evaluations.show', $member['evaluation_id']) }}" class="text-blue-600 text-xs font-medium hover:underline">Ver</a>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 pb-6 text-sm text-slate-500">No se encontraron empleados vigentes en Salomón para tu área.</div>
        @endif
    </div>

    @if(!empty($teamEvaluations) && count($teamEvaluations))
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Evaluaciones de mi equipo</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($teamEvaluations as $ev)
            @php [$slabel,$sclass] = $statusMap[$ev->status] ?? ['—','bg-slate-100 text-slate-500']; @endphp
            <div class="px-6 py-3 flex items-center justify-between row-hover">
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $ev->employee?->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $ev->period }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $sclass }}">{{ $slabel }}</span>
                    <a href="{{ route('evaluations.show', $ev) }}" class="text-blue-600 text-xs font-medium hover:underline">Ver</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- ═══ EMPLEADO ═══ --}}
@else

<div class="space-y-6">
    <div class="anim-slide-left bg-gradient-to-r from-slate-800 via-slate-700 to-slate-600 hero-shimmer rounded-2xl p-6 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent pointer-events-none"></div>
        <p class="text-slate-400 text-sm relative">{{ now()->isoFormat('dddd, D [de] MMMM YYYY') }}</p>
        <h2 class="text-2xl font-bold mt-1 relative">Hola, {{ explode(' ', $user->name)[0] }} 👋</h2>
        <p class="text-slate-400 text-sm mt-1 relative">{{ $user->area?->name ?? '' }} · {{ $user->positionType?->name ?? '' }}</p>
    </div>

    <div class="grid grid-cols-3 gap-4">
        @php $s3=[['label'=>'Mis evaluaciones','value'=>$pendingCount??0+($inProgressCount??0),'color'=>'blue'],['label'=>'En Progreso','value'=>$inProgressCount??0,'color'=>'sky'],['label'=>'Completadas','value'=>$completedCount??0,'color'=>'emerald']]; @endphp
        @foreach($s3 as $s)
        @php $p=$palette[$s['color']]??['bg'=>'bg-slate-50','icon'=>'bg-slate-400','text'=>'text-slate-600']; @endphp
        <div class="anim-fade-up stat-card {{ $p['bg'] }} rounded-2xl p-5 border border-white text-center cursor-default">
            <p class="count-anim text-3xl font-bold text-slate-800">{{ $s['value'] }}</p>
            <p class="text-slate-500 text-sm mt-1">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    @if(!empty($myEvaluations) && count($myEvaluations))
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Mis evaluaciones</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @php $statusMap=['pendiente'=>['Pendiente','bg-amber-100 text-amber-700'],'en_progreso'=>['En progreso','bg-blue-100 text-blue-700'],'completada'=>['Completada','bg-emerald-100 text-emerald-700']]; @endphp
            @foreach($myEvaluations as $ev)
            @php [$slabel,$sclass] = $statusMap[$ev->status] ?? ['—','bg-slate-100 text-slate-500']; @endphp
            <div class="px-6 py-3 flex items-center justify-between row-hover">
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $ev->template?->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $ev->period }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $sclass }}">{{ $slabel }}</span>
                    <a href="{{ route('evaluations.show', $ev) }}"
                       class="text-blue-600 text-xs font-medium hover:underline">
                        {{ $ev->status === 'pendiente' ? 'Iniciar →' : 'Ver' }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="anim-pop bg-white rounded-2xl border border-slate-200 p-12 text-center">
        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 pulse-ring">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <p class="text-slate-600 font-medium">Sin evaluaciones asignadas</p>
        <p class="text-slate-400 text-sm mt-1">Tu jefe te asignará una evaluación próximamente</p>
    </div>
    @endif
</div>
@endif
@endsection
