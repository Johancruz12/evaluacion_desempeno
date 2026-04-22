@extends('layouts.app')
@section('title', 'Empleados y Accesos')

@push('styles')
<style>
    /* ── Base ── */
    .glass{background:rgba(255,255,255,.92);backdrop-filter:blur(14px);border:1px solid rgba(226,232,240,.7)}

    /* ── Animations ── */
    @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
    @keyframes scaleIn{from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)}}
    @keyframes slideRight{from{opacity:0;transform:translateX(-16px)}to{opacity:1;transform:translateX(0)}}
    @keyframes pulseGlow{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.4)}50%{box-shadow:0 0 0 8px rgba(16,185,129,0)}}
    @keyframes progressFill{from{width:0}to{width:var(--pct)}}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
    @keyframes modalIn{from{opacity:0;transform:scale(.94) translateY(12px)}to{opacity:1;transform:scale(1) translateY(0)}}
    @keyframes uploadPulse{0%,100%{opacity:.6}50%{opacity:1}}

    .anim-fade-up{animation:fadeUp .5s cubic-bezier(.4,0,.2,1) both}
    .anim-slide-right{animation:slideRight .4s cubic-bezier(.4,0,.2,1) both}
    .anim-float{animation:float 3s ease-in-out infinite}
    .modal-enter{animation:modalIn .3s cubic-bezier(.34,1.56,.64,1) forwards}
    .progress-fill{animation:progressFill .9s .2s cubic-bezier(.4,0,.2,1) both}
    .pulse-glow{animation:pulseGlow 2s ease-in-out infinite}
    .upload-progress{animation:uploadPulse 1.5s ease-in-out infinite}

    /* ── Drop zone ── */
    .drop-zone{position:relative;border:2px dashed #94a3b8;border-radius:1.25rem;transition:all .35s cubic-bezier(.4,0,.2,1);background:linear-gradient(135deg,#f8fafc,#f1f5f9)}
    .drop-zone:hover,.drop-zone.drag-over{border-color:#3b82f6;background:linear-gradient(135deg,#eff6ff,#dbeafe);transform:scale(1.003)}
    .drop-zone .drop-icon{transition:all .35s cubic-bezier(.34,1.56,.64,1)}
    .drop-zone:hover .drop-icon,.drop-zone.drag-over .drop-icon{transform:scale(1.12) translateY(-4px)}

    /* ── Area card ── */
    .area-card{transition:all .3s cubic-bezier(.4,0,.2,1)}
    .area-card:hover{box-shadow:0 20px 40px rgba(59,130,246,.1)}
    .area-header{cursor:pointer;transition:background .2s ease}
    .area-header:hover{background:rgba(59,130,246,.03)}

    /* ── Employee row ── */
    .emp-row{transition:all .2s cubic-bezier(.4,0,.2,1)}
    .emp-row:hover{background:linear-gradient(90deg,#eef5ff 0%,#f8fafc 60%,transparent 100%);transform:translateX(2px)}
    .emp-row:hover .emp-actions{opacity:1;transform:translateX(0)}
    .emp-actions{opacity:0;transform:translateX(6px);transition:all .2s cubic-bezier(.4,0,.2,1)}

    /* ── Badges ── */
    .badge-active{background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#166534;border:1px solid #86efac}
    .badge-inactive{background:linear-gradient(135deg,#fef2f2,#fecaca);color:#991b1b;border:1px solid #fca5a5}
    .badge-role{background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#5b21b6;border:1px solid #c4b5fd}
    .badge-role-jefe{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e;border:1px solid #fcd34d}
    .badge-role-admin{background:linear-gradient(135deg,#fce7f3,#fbcfe8);color:#9d174d;border:1px solid #f9a8d4}

    /* ── Stat card ── */
    .stat-card-mini{transition:all .3s cubic-bezier(.4,0,.2,1)}
    .stat-card-mini:hover{transform:translateY(-4px);box-shadow:0 16px 32px rgba(0,0,0,.08)}

    /* ── Tab ── */
    .tab-btn{position:relative;transition:all .25s ease}
    .tab-btn::after{content:'';position:absolute;bottom:0;left:50%;width:0;height:2.5px;background:linear-gradient(90deg,#3b82f6,#6366f1);border-radius:9px;transition:all .3s cubic-bezier(.4,0,.2,1);transform:translateX(-50%)}
    .tab-btn.active::after{width:70%}
    .tab-btn.active{color:#1e40af}

    /* ── Modal ── */
    .modal-backdrop{background:rgba(15,23,42,.5);backdrop-filter:blur(6px)}

    /* ── Tooltip ── */
    [data-tip]{position:relative}
    [data-tip]::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%) scale(.9);background:#1e293b;color:#fff;font-size:.68rem;padding:5px 10px;border-radius:8px;white-space:nowrap;pointer-events:none;opacity:0;transition:all .2s cubic-bezier(.4,0,.2,1);z-index:50}
    [data-tip]:hover::after{opacity:1;transform:translateX(-50%) scale(1)}

    /* ── Pagination ── */
    .page-btn{transition:all .2s ease}
    .page-btn:hover:not(.active):not(.disabled){background:#eef5ff;color:#1e40af}
    .page-btn.active{background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;box-shadow:0 4px 12px rgba(59,130,246,.3)}
</style>
@endpush

@section('content')
<div x-data="employeesApp()" class="space-y-6 max-w-[1440px]">

    {{-- ═══════ HEADER ═══════ --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 anim-fade-up">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                Empleados y Accesos
            </h1>
            <p class="text-slate-500 text-sm mt-1.5 ml-[52px] flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 pulse-glow"></span>
                <span class="font-semibold text-slate-700">{{ $totalUsers }}</span> registrados &middot;
                <span class="text-emerald-600 font-semibold">{{ $activeUsers }}</span> activos &middot;
                <span class="text-rose-500 font-semibold">{{ $inactiveUsers }}</span> inactivos
            </p>
        </div>
        <button @click="openCreateModal()"
                class="inline-flex items-center gap-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm active:scale-[.97]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo empleado
        </button>
    </div>

    {{-- ═══════ STATS BAR ═══════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 anim-fade-up" style="animation-delay:.1s">
        {{-- Total card --}}
        <div class="stat-card-mini glass rounded-xl p-4 cursor-default">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total activos</p>
                <span class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-800">{{ $activeUsers }}</p>
            <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 progress-fill" style="--pct:100%"></div>
            </div>
        </div>
        @php $gradients = ['from-emerald-400 to-emerald-600','from-violet-400 to-violet-600','from-amber-400 to-amber-600','from-rose-400 to-rose-600','from-sky-400 to-sky-600','from-pink-400 to-pink-600','from-blue-400 to-blue-600']; @endphp
        @foreach($areaStats as $stat)
        @php $pct = $activeUsers > 0 ? round(($stat->total / $activeUsers) * 100) : 0; $g = $gradients[$loop->index % count($gradients)]; @endphp
        <div class="stat-card-mini glass rounded-xl p-4 cursor-default" style="animation-delay:{{ ($loop->index + 1) * .06 }}s">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate pr-2">{{ $stat->area?->name ?? 'Sin area' }}</p>
                <span class="text-[10px] font-bold text-slate-400">{{ $pct }}%</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-800">{{ $stat->total }}</p>
            <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r {{ $g }} progress-fill" style="--pct:{{ $pct }}%"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══════ TABS ═══════ --}}
    <div class="glass rounded-2xl overflow-hidden anim-fade-up" style="animation-delay:.15s">
        <div class="flex border-b border-slate-100 px-1 pt-1">
            <button @click="currentTab = 'areas'" :class="currentTab === 'areas' && 'active'" class="tab-btn flex items-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Por Areas
            </button>
            <button @click="currentTab = 'list'" :class="currentTab === 'list' && 'active'" class="tab-btn flex items-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Lista completa
            </button>
            <button @click="currentTab = 'import'" :class="currentTab === 'import' && 'active'" class="tab-btn flex items-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Importar Excel
            </button>
        </div>

        {{-- ─── TAB 1: POR AREAS (accordion) ─── --}}
        <div x-show="currentTab === 'areas'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-5 space-y-4">
            @php $areaColors = ['blue','emerald','violet','amber','rose','cyan','indigo','pink','teal']; @endphp
            @foreach($allUsersGrouped as $areaId => $areaUsers)
            @php
                $area = $areaUsers->first()->area;
                $areaName = $area ? $area->name : 'Sin Area';
                $c = $areaColors[$loop->index % count($areaColors)];
            @endphp
            <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="area-card glass rounded-xl overflow-hidden anim-slide-right" style="animation-delay:{{ $loop->index * .06 }}s">
                <div @click="open = !open" class="area-header flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-{{ $c }}-400 to-{{ $c }}-600 flex items-center justify-center shadow-lg shadow-{{ $c }}-500/20 flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">{{ $areaName }}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $areaUsers->count() }} empleado(s) activo(s)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-{{ $c }}-50 text-{{ $c }}-700 border border-{{ $c }}-200">
                            {{ $areaUsers->count() }}
                        </span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">
                    <div class="border-t border-slate-100">
                        @foreach($areaUsers as $u)
                        @php
                            $initials = strtoupper(substr($u->person?->first_name ?? $u->name, 0, 1) . substr($u->person?->last_name ?? '', 0, 1));
                            $avatarColors = ['bg-blue-500','bg-emerald-500','bg-violet-500','bg-amber-500','bg-rose-500','bg-sky-500','bg-indigo-500','bg-pink-500'];
                            $ac = $avatarColors[($u->id ?? 0) % count($avatarColors)];
                        @endphp
                        <div class="emp-row flex items-center justify-between gap-3 px-5 py-3 border-b border-slate-50 last:border-0">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-full {{ $ac }} flex items-center justify-center text-white font-bold text-[11px] flex-shrink-0 shadow-sm">{{ $initials }}</div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 text-sm truncate">{{ $u->person?->first_name }} {{ $u->person?->last_name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $u->person?->document_number }} &middot; {{ $u->positionType?->name ?? 'Sin cargo' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @foreach($u->roles as $r)
                                    @if($r->slug === 'director_rh')
                                    <span class="badge-role-admin px-2 py-0.5 rounded-full text-[10px] font-bold hidden sm:inline">{{ $r->description }}</span>
                                    @elseif(str_contains($r->slug, 'jefe'))
                                    <span class="badge-role-jefe px-2 py-0.5 rounded-full text-[10px] font-bold hidden sm:inline">{{ $r->description }}</span>
                                    @else
                                    <span class="badge-role px-2 py-0.5 rounded-full text-[10px] font-bold hidden sm:inline">{{ $r->description }}</span>
                                    @endif
                                @endforeach
                                @if($u->is_active)
                                <span class="badge-active px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>
                                @else
                                <span class="badge-inactive px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Inactivo</span>
                                @endif
                                @php
                                    $uData = json_encode(['login'=>$u->login,'first_name'=>$u->person?->first_name,'last_name'=>$u->person?->last_name,'document_type'=>$u->person?->document_type,'document_number'=>$u->person?->document_number,'email'=>$u->person?->email,'phone'=>$u->person?->phone,'area_id'=>$u->area_id,'position_type_id'=>$u->position_type_id,'employee_code'=>$u->employee_code,'is_active'=>$u->is_active,'role_ids'=>$u->roles->pluck('id')->toArray()]);
                                @endphp
                                <div class="emp-actions">
                                    <button type="button" @click="openEditModal({{ $u->id }}, {{ $uData }})" data-tip="Editar" class="w-8 h-8 rounded-lg bg-blue-100 hover:bg-blue-600 flex items-center justify-center text-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
            @if($allUsersGrouped->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-4 anim-float">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-500">No hay empleados activos</p>
                <p class="text-xs text-slate-400 mt-1">Importa un archivo Excel para comenzar</p>
            </div>
            @endif
        </div>

        {{-- ─── TAB 2: LISTA COMPLETA (table with filters) ─── --}}
        <div x-show="currentTab === 'list'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            {{-- Search & Filters --}}
            <div class="px-5 py-4 border-b border-slate-100">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="list">
                    <div class="relative flex-1 min-w-[220px]">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, cedula, email..."
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 transition-all">
                    </div>
                    <select name="area" class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 min-w-[160px]">
                        <option value="">Todas las areas</option>
                        @foreach($areas as $a)
                        <option value="{{ $a->id }}" {{ request('area') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 min-w-[130px]">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-all active:scale-[.97]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Buscar
                    </button>
                    @if(request()->hasAny(['search','area','status']))
                    <a href="{{ route('admin.users.index') }}?tab=list" class="px-3 py-2.5 text-sm text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">Limpiar</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <div class="hidden md:grid grid-cols-12 gap-3 px-5 py-3 bg-slate-50/60 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <div class="col-span-4">Empleado</div>
                    <div class="col-span-2">Area / Cargo</div>
                    <div class="col-span-2">Login</div>
                    <div class="col-span-2">Rol(es)</div>
                    <div class="col-span-1">Estado</div>
                    <div class="col-span-1 text-right">Accion</div>
                </div>
                @forelse($users as $u)
                @php
                    $initials = strtoupper(substr($u->person?->first_name ?? $u->name, 0, 1) . substr($u->person?->last_name ?? '', 0, 1));
                    $avatarColors = ['bg-blue-500','bg-emerald-500','bg-violet-500','bg-amber-500','bg-rose-500','bg-sky-500','bg-indigo-500','bg-pink-500'];
                    $ac = $avatarColors[($u->id ?? 0) % count($avatarColors)];
                @endphp
                <div class="emp-row border-b border-slate-50 last:border-0 grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3 items-center px-5 py-3.5 anim-slide-right" style="animation-delay:{{ min($loop->index * .03, .5) }}s">
                    <div class="col-span-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full {{ $ac }} flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm">{{ $initials }}</div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800 text-sm truncate">{{ $u->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $u->person?->document_number }} &middot; {{ $u->person?->email ?? 'Sin email' }}</p>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-slate-700 truncate">{{ $u->area?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $u->positionType?->name ?? '—' }}</p>
                    </div>
                    <div class="col-span-2">
                        <code class="text-xs bg-slate-100 px-2 py-1 rounded-lg text-slate-600 font-mono">{{ $u->login }}</code>
                    </div>
                    <div class="col-span-2 flex flex-wrap gap-1">
                        @foreach($u->roles as $r)
                            @if($r->slug === 'director_rh')
                            <span class="badge-role-admin px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $r->description }}</span>
                            @elseif(str_contains($r->slug, 'jefe'))
                            <span class="badge-role-jefe px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $r->description }}</span>
                            @else
                            <span class="badge-role px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $r->description }}</span>
                            @endif
                        @endforeach
                    </div>
                    <div class="col-span-1">
                        @if($u->is_active)
                        <span class="badge-active px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>
                        @else
                        <span class="badge-inactive px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Inactivo</span>
                        @endif
                    </div>
                    @php
                        $uData = json_encode(['login'=>$u->login,'first_name'=>$u->person?->first_name,'last_name'=>$u->person?->last_name,'document_type'=>$u->person?->document_type,'document_number'=>$u->person?->document_number,'email'=>$u->person?->email,'phone'=>$u->person?->phone,'area_id'=>$u->area_id,'position_type_id'=>$u->position_type_id,'employee_code'=>$u->employee_code,'is_active'=>$u->is_active,'role_ids'=>$u->roles->pluck('id')->toArray()]);
                    @endphp
                    <div class="col-span-1 flex justify-end emp-actions">
                        <button type="button" @click="openEditModal({{ $u->id }}, {{ $uData }})" data-tip="Editar" class="w-8 h-8 rounded-lg bg-blue-100 hover:bg-blue-600 flex items-center justify-center text-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 transition-all duration-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                    </div>
                </div>
                @empty
                <div class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-4 anim-float">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-500">No se encontraron usuarios</p>
                    <p class="text-xs text-slate-400 mt-1">Prueba ajustando los filtros o cargando un archivo Excel</p>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 flex justify-center">
                <div class="glass rounded-xl px-2 py-1.5 inline-flex items-center gap-1 text-sm">
                    @if($users->onFirstPage())
                    <span class="page-btn w-9 h-9 rounded-lg flex items-center justify-center text-slate-300 cursor-not-allowed disabled">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                    @else
                    <a href="{{ $users->appends(['tab'=>'list'])->previousPageUrl() }}" class="page-btn w-9 h-9 rounded-lg flex items-center justify-center text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    @endif
                    @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
                        @if($page == $users->currentPage())
                        <span class="page-btn active w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs">{{ $page }}</span>
                        @else
                        <a href="{{ $url }}&tab=list" class="page-btn w-9 h-9 rounded-lg flex items-center justify-center text-slate-600 font-medium text-xs">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($users->hasMorePages())
                    <a href="{{ $users->appends(['tab'=>'list'])->nextPageUrl() }}" class="page-btn w-9 h-9 rounded-lg flex items-center justify-center text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @else
                    <span class="page-btn w-9 h-9 rounded-lg flex items-center justify-center text-slate-300 cursor-not-allowed disabled">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                    @endif
                    <span class="pl-2 text-xs text-slate-400 font-medium">{{ $users->currentPage() }} / {{ $users->lastPage() }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- ─── TAB 3: IMPORTAR EXCEL ─── --}}
        <div x-show="currentTab === 'import'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-5 space-y-6">
            {{-- Format guide --}}
            <div class="rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50/80 to-indigo-50/40 p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0 shadow-md shadow-blue-500/20">
                        <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Formato esperado del archivo Excel</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">El archivo debe tener las siguientes columnas en la primera fila (encabezados). El orden no importa.</p>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Doc</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Documento</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Apellido y Nombres</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Cargo</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Area</th>
                                <th class="px-4 py-2.5 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs text-slate-600">
                            <tr class="border-b border-slate-50">
                                <td class="px-4 py-2">CC</td>
                                <td class="px-4 py-2 font-mono">1098765432</td>
                                <td class="px-4 py-2">PEREZ RODRIGUEZ JUAN CARLOS</td>
                                <td class="px-4 py-2">Auxiliar Contable</td>
                                <td class="px-4 py-2">Contabilidad</td>
                                <td class="px-4 py-2"><span class="badge-active px-2 py-0.5 rounded-full text-[10px] font-bold">Activo</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2">TI</td>
                                <td class="px-4 py-2 font-mono">1234567890</td>
                                <td class="px-4 py-2">GARCIA LOPEZ MARIA FERNANDA</td>
                                <td class="px-4 py-2">Enfermera Jefe</td>
                                <td class="px-4 py-2">Enfermeria</td>
                                <td class="px-4 py-2"><span class="badge-active px-2 py-0.5 rounded-full text-[10px] font-bold">Activo</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Upload form --}}
            <form method="POST" action="{{ route('admin.users.import-excel') }}" enctype="multipart/form-data" x-ref="uploadForm" @submit="uploading = true">
                @csrf
                <div class="drop-zone p-10 text-center cursor-pointer"
                     :class="{ 'drag-over': dragOver }"
                     @dragover.prevent="dragOver = true"
                     @dragleave.prevent="dragOver = false"
                     @drop.prevent="handleDrop($event)"
                     @click="$refs.fileInput.click()">
                    <input type="file" name="employees_file" accept=".xlsx,.xls,.csv" required x-ref="fileInput" class="hidden" @change="fileName = $event.target.files[0]?.name ?? ''">
                    <template x-if="!fileName">
                        <div>
                            <div class="drop-icon mx-auto w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center mb-5 border border-blue-100">
                                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Arrastra tu archivo aqui</p>
                            <p class="text-sm text-slate-500 mt-1">o <span class="text-blue-600 font-semibold underline underline-offset-2">seleccionalo desde tu equipo</span></p>
                            <p class="text-xs text-slate-400 mt-3">Formatos aceptados: .xlsx, .xls, .csv</p>
                        </div>
                    </template>
                    <template x-if="fileName">
                        <div class="flex items-center justify-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center border border-emerald-200">
                                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-bold text-slate-800" x-text="fileName"></p>
                                <p class="text-xs text-emerald-600 font-semibold mt-0.5">Archivo listo para cargar</p>
                            </div>
                            <button type="button" @click.stop="fileName=''; $refs.fileInput.value=''" class="ml-2 w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-100 flex items-center justify-center text-slate-400 hover:text-rose-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <div class="flex justify-end mt-5">
                    <button type="submit" :disabled="!fileName || uploading"
                            class="inline-flex items-center gap-2.5 px-6 py-3 bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-bold rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed active:scale-[.97]">
                        <span x-show="!uploading" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Cargar y sincronizar
                        </span>
                        <span x-show="uploading" class="flex items-center gap-2 upload-progress">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Procesando...
                        </span>
                    </button>
                </div>
            </form>

            {{-- Info badges --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-blue-50/70 border border-blue-100">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-800">Login automatico</p>
                        <p class="text-[11px] text-blue-600 mt-0.5 leading-relaxed">El login se genera con el numero de documento</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50/70 border border-amber-100">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-amber-800">Contrasena = Documento</p>
                        <p class="text-[11px] text-amber-600 mt-0.5 leading-relaxed">Se reinicia la contrasena al numero de documento</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl bg-violet-50/70 border border-violet-100">
                    <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-violet-800">Areas y Cargos</p>
                        <p class="text-[11px] text-violet-600 mt-0.5 leading-relaxed">Se crean automaticamente si no existen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ MODAL: Create / Edit ═══════ --}}
    <template x-teleport="body">
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-1" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop" @click.self="showModal = false" style="display:none">
            <div class="modal-enter bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                {{-- Modal header --}}
                <div class="sticky top-0 bg-white/95 backdrop-blur-sm border-b border-slate-100 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-md" :class="editingUserId ? 'bg-gradient-to-br from-amber-400 to-amber-600 shadow-amber-500/20' : 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-blue-500/20'">
                            <svg x-show="!editingUserId" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <svg x-show="editingUserId" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800" x-text="editingUserId ? 'Editar empleado' : 'Nuevo empleado'"></h3>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="editingUserId ? 'Actualiza los datos de este usuario' : 'Completa la informacion del nuevo usuario'"></p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                {{-- Modal form --}}
                <form :action="editingUserId ? '{{ url('admin/users') }}/' + editingUserId : '{{ route('admin.users.store') }}'" method="POST" class="p-6 space-y-6">
                    @csrf
                    <template x-if="editingUserId"><input type="hidden" name="_method" value="PUT"></template>

                    {{-- Personal info --}}
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-blue-100 flex items-center justify-center text-blue-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                            Informacion personal
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Tipo Doc</label>
                                <select name="document_type" x-model="form.document_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all">
                                    <option value="CC">CC - Cedula</option>
                                    <option value="TI">TI - Tarjeta Identidad</option>
                                    <option value="CE">CE - Cedula Extranjeria</option>
                                    <option value="PA">PA - Pasaporte</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">N Documento</label>
                                <input type="text" name="document_number" x-model="form.document_number" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Nombres</label>
                                <input type="text" name="first_name" x-model="form.first_name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Apellidos</label>
                                <input type="text" name="last_name" x-model="form.last_name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Email</label>
                                <input type="email" name="email" x-model="form.email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all" placeholder="correo@empresa.com">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Telefono</label>
                                <input type="text" name="phone" x-model="form.phone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all" placeholder="300 123 4567">
                            </div>
                        </div>
                    </div>

                    {{-- Access --}}
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-amber-100 flex items-center justify-center text-amber-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg></span>
                            Acceso al sistema
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Usuario (login)</label>
                                <input type="text" name="login" x-model="form.login" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1" x-text="editingUserId ? 'Nueva contrasena (vacio = no cambiar)' : 'Contrasena'"></label>
                                <input type="password" name="password" :required="!editingUserId" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- Organization --}}
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-emerald-100 flex items-center justify-center text-emerald-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span>
                            Organizacion
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Area</label>
                                <select name="area_id" x-model="form.area_id" @change="loadPositionTypes()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all">
                                    <option value="">Sin area</option>
                                    @foreach($areas as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Cargo</label>
                                <select name="position_type_id" x-model="form.position_type_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all">
                                    <option value="">Sin cargo</option>
                                    <template x-for="pt in positionOptions" :key="pt.id"><option :value="pt.id" x-text="pt.name" :selected="pt.id == form.position_type_id"></option></template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Codigo empleado</label>
                                <input type="text" name="employee_code" x-model="form.employee_code" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 focus:bg-white transition-all" placeholder="EMP-007">
                            </div>
                        </div>
                    </div>

                    {{-- Roles --}}
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-violet-100 flex items-center justify-center text-violet-500"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></span>
                            Roles
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($roles as $r)
                            <label class="group flex items-center gap-2 cursor-pointer select-none bg-slate-50 hover:bg-blue-50 px-3.5 py-2 rounded-xl border border-slate-200 hover:border-blue-300 transition-all text-sm">
                                <input type="checkbox" name="role_ids[]" value="{{ $r->id }}" :checked="form.role_ids.includes({{ $r->id }})" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 transition-all">
                                <span class="text-slate-600 group-hover:text-blue-700 transition-colors">{{ $r->description }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Active toggle --}}
                    <template x-if="editingUserId">
                        <div class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <input type="hidden" name="is_active" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" :checked="form.is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                            <span class="text-sm text-slate-600 font-medium">Usuario activo</span>
                        </div>
                    </template>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-all active:scale-[.97]">
                            <span x-text="editingUserId ? 'Guardar cambios' : 'Crear empleado'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function employeesApp() {
    return {
        currentTab: new URLSearchParams(window.location.search).get('tab') || 'areas',
        showModal: false,
        editingUserId: null,
        fileName: '',
        dragOver: false,
        uploading: false,
        positionOptions: [],
        form: {
            first_name: '', last_name: '', document_type: 'CC', document_number: '',
            email: '', phone: '', login: '', area_id: '', position_type_id: '',
            employee_code: '', is_active: true, role_ids: []
        },
        openCreateModal() {
            this.editingUserId = null;
            this.form = {
                first_name: '', last_name: '', document_type: 'CC', document_number: '',
                email: '', phone: '', login: '', area_id: '', position_type_id: '',
                employee_code: '', is_active: true, role_ids: []
            };
            this.positionOptions = [];
            this.showModal = true;
        },
        openEditModal(userId, data) {
            this.editingUserId = userId;
            this.form = {
                first_name: data.first_name || '',
                last_name: data.last_name || '',
                document_type: data.document_type || 'CC',
                document_number: data.document_number || '',
                email: data.email || '',
                phone: data.phone || '',
                login: data.login || '',
                area_id: data.area_id ? String(data.area_id) : '',
                position_type_id: data.position_type_id ? String(data.position_type_id) : '',
                employee_code: data.employee_code || '',
                is_active: data.is_active ?? true,
                role_ids: data.role_ids || []
            };
            if (this.form.area_id) { this.loadPositionTypes(true); } else { this.positionOptions = []; }
            this.showModal = true;
        },
        async loadPositionTypes(keepSelected = false) {
            const areaId = this.form.area_id;
            if (!areaId) { this.positionOptions = []; this.form.position_type_id = ''; return; }
            try {
                const res = await fetch('/admin/api/areas/' + areaId + '/position-types');
                this.positionOptions = await res.json();
                if (!keepSelected) this.form.position_type_id = '';
            } catch (e) { this.positionOptions = []; }
        },
        handleDrop(event) {
            this.dragOver = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.$refs.fileInput.files = files;
                this.fileName = files[0].name;
            }
        }
    }
}
</script>
@endpush
