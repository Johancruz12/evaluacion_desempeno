<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JUnical') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *{font-family:'Inter',sans-serif}

        :root{
            --brand-50:#f5f9ff;
            --brand-100:#eaf2ff;
            --brand-200:#d8e7ff;
            --brand-300:#c3daff;
            --brand-400:#9bc3ff;
            --brand-500:#6da8ff;
            --brand-600:#478eff;
            --brand-700:#2f76ea;
            --brand-800:#285fb9;
            --brand-900:#244a8d;
        }

        body{background:linear-gradient(180deg,#f7fbff 0%,#f3f8ff 60%,#ffffff 100%)}

        #sidebar{
            background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%) !important;
            border-right:1px solid #dbeafe;
            box-shadow:4px 0 25px rgba(59,130,246,.06);
        }
        #sidebar .border-b,#sidebar .border-t{border-color:#dbeafe !important}
        #sidebar .from-gray-950,#sidebar .via-gray-950,#sidebar .to-gray-900{background:transparent !important}

        #sidebar .sidebar-link:hover{transform:translateX(3px)}
        #sidebar .sidebar-link[class*='from-blue-500']{
            box-shadow:0 8px 22px rgba(59,130,246,.25) !important;
        }

        header.sticky{background:#fff;border-color:#dbeafe;box-shadow:0 4px 12px rgba(59,130,246,.08)}

        .stat-card{background:#fff;border-color:#dbeafe}
        .stat-card:hover{box-shadow:0 18px 36px rgba(59,130,246,.12)}
        .row-hover:hover{background:linear-gradient(90deg,#eef5ff,transparent)}

        /* ── Sidebar ── */
        .sidebar-link{transition:all .25s cubic-bezier(.4,0,.2,1)}
        .sidebar-link:hover{transform:translateX(4px)}

        /* ── Page entrance ── */
        @keyframes pageIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .page-content{animation:pageIn .45s cubic-bezier(.4,0,.2,1) forwards}

        /* ── Toast ── */
        @keyframes toastIn{0%{opacity:0;transform:translateX(80px) scale(.95)}100%{opacity:1;transform:translateX(0) scale(1)}}
        @keyframes toastOut{0%{opacity:1;transform:translateX(0)}100%{opacity:0;transform:translateX(80px)}}
        .toast{animation:toastIn .4s cubic-bezier(.34,1.56,.64,1) forwards}
        .toast.hiding{animation:toastOut .3s ease forwards}

        /* ── Staggered fade-up (cards, rows) ── */
        @keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
        .anim-fade-up{opacity:0;animation:fadeUp .5s cubic-bezier(.4,0,.2,1) forwards}
        .anim-fade-up:nth-child(1){animation-delay:.05s}
        .anim-fade-up:nth-child(2){animation-delay:.1s}
        .anim-fade-up:nth-child(3){animation-delay:.12s}
        .anim-fade-up:nth-child(4){animation-delay:.16s}
        .anim-fade-up:nth-child(5){animation-delay:.2s}
        .anim-fade-up:nth-child(6){animation-delay:.24s}
        .anim-fade-up:nth-child(7){animation-delay:.28s}
        .anim-fade-up:nth-child(8){animation-delay:.32s}

        /* ── Scale pop ── */
        @keyframes scalePop{from{opacity:0;transform:scale(.88)}to{opacity:1;transform:scale(1)}}
        .anim-pop{animation:scalePop .4s cubic-bezier(.34,1.56,.64,1) forwards}

        /* ── Count up number ── */
        @keyframes countUp{from{opacity:0;transform:scale(.8) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}
        .count-anim{animation:countUp .6s .15s cubic-bezier(.34,1.56,.64,1) both}

        /* ── Stat cards ── */
        .stat-card{transition:transform .3s cubic-bezier(.4,0,.2,1),box-shadow .3s ease}
        .stat-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(0,0,0,.08)}
        .stat-card:active{transform:translateY(-1px);box-shadow:0 8px 20px rgba(0,0,0,.06)}

        /* ── Hero gradient shimmer ── */
        @keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
        .hero-shimmer{background-size:200% 100%;animation:shimmer 6s ease-in-out infinite}

        /* ── Slide-in from left ── */
        @keyframes slideInLeft{from{opacity:0;transform:translateX(-24px)}to{opacity:1;transform:translateX(0)}}
        .anim-slide-left{animation:slideInLeft .5s cubic-bezier(.4,0,.2,1) forwards}

        /* ── Slide-in from right ── */
        @keyframes slideInRight{from{opacity:0;transform:translateX(24px)}to{opacity:1;transform:translateX(0)}}
        .anim-slide-right{animation:slideInRight .5s cubic-bezier(.4,0,.2,1) forwards}

        /* ── Pulse glow ring ── */
        @keyframes pulseRing{0%{box-shadow:0 0 0 0 rgba(59,130,246,.3)}70%{box-shadow:0 0 0 10px rgba(59,130,246,0)}100%{box-shadow:0 0 0 0 rgba(59,130,246,0)}}
        .pulse-ring{animation:pulseRing 2s ease infinite}

        /* ── Table row hover ── */
        .row-hover{transition:all .2s ease}
        .row-hover:hover{background:linear-gradient(90deg,rgba(59,130,246,.04),transparent);transform:translateX(2px)}

        /* ── Button micro-interactions ── */
        .btn-bounce{transition:all .2s cubic-bezier(.4,0,.2,1)}
        .btn-bounce:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(59,130,246,.2)}
        .btn-bounce:active{transform:translateY(0) scale(.97)}

        /* ── Modal backdrop ── */
        @keyframes modalBg{from{backdrop-filter:blur(0);background:transparent}to{backdrop-filter:blur(6px);background:rgba(0,0,0,.5)}}
        @keyframes modalIn{from{opacity:0;transform:scale(.92) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
        .modal-overlay{animation:modalBg .3s ease forwards}
        .modal-content{animation:modalIn .35s cubic-bezier(.34,1.56,.64,1) forwards}

        /* ── Progress bar fill ── */
        @keyframes progressFill{from{width:0}to{width:var(--progress)}}
        .progress-bar{animation:progressFill 1s .3s cubic-bezier(.4,0,.2,1) forwards;width:0}

        /* ── Floating labels ── */
        @keyframes floatLabel{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
        .anim-label{animation:floatLabel .3s ease forwards}

        /* ── Skeleton loading ── */
        @keyframes skeletonPulse{0%{background-position:-200% 0}100%{background-position:200% 0}}
        .skeleton{background:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);background-size:200% 100%;animation:skeletonPulse 1.5s ease infinite;border-radius:8px}

        /* ── Sidebar icon bounce on active ── */
        .sidebar-link.active svg{animation:scalePop .4s cubic-bezier(.34,1.56,.64,1)}

        /* ── Badge notification dot ── */
        @keyframes notifPulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.3);opacity:.7}}
        .notif-dot{animation:notifPulse 2s ease infinite}

        /* ── Scrollbar ── */
        ::-webkit-scrollbar{width:5px;height:5px}
        ::-webkit-scrollbar-track{background:#f1f5f9}
        ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
        ::-webkit-scrollbar-thumb:hover{background:#94a3b8}

        /* ── Accordion ── */
        .accordion-body{max-height:0;overflow:hidden;transition:max-height .4s cubic-bezier(.4,0,.2,1)}
        .accordion-body.open{max-height:9999px}

        /* ── Ripple effect ── */
        .ripple{position:relative;overflow:hidden}
        .ripple::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at var(--x,50%) var(--y,50%),rgba(255,255,255,.3) 0%,transparent 60%);opacity:0;transition:opacity .5s ease}
        .ripple:active::after{opacity:1;transition:opacity 0s}

        /* ── Focus ring ── */
        input:focus,select:focus,textarea:focus{transition:box-shadow .2s ease,border-color .2s ease}

        /* ── Smooth all transitions ── */
        a,button{transition:all .2s cubic-bezier(.4,0,.2,1)}
    </style>
</head>
<body class="h-full bg-white flex">

{{-- Sidebar overlay (mobile) --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

{{-- ═══ SIDEBAR ═══ --}}
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white flex flex-col z-30 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-out shadow-xl shadow-blue-500/5">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-4 h-16 border-b border-blue-100 flex-shrink-0">
        <img src="{{ asset('branding/clinica-junical-icon.png') }}" alt="Clínica Junical" class="w-9 h-9 object-contain flex-shrink-0">
        <div>
            <p class="text-slate-800 font-bold text-sm">Clínica Junical</p>
            <p class="text-blue-500 text-[10px] font-semibold tracking-wide">Evaluación de Desempeño</p>
        </div>
    </div>

    {{-- User card --}}
    <div class="px-4 py-3 border-b border-blue-100 flex-shrink-0">
        <div class="flex items-center gap-3 bg-blue-50/80 rounded-xl p-3 hover:bg-blue-50 transition-colors border border-blue-100/50">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-md shadow-blue-300/30">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-slate-800 text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-blue-500 text-xs truncate">{{ auth()->user()->roles->first()?->description ?? 'Sin rol' }}</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">

        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest px-3 pb-2">Principal</p>

        <a href="{{ route('dashboard') }}"
           class="sidebar-link group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
           {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <svg class="w-4.5 h-4.5 flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Inicio</span>
        </a>

        <a href="{{ route('evaluations.index') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
           {{ request()->routeIs('evaluations.*') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>Evaluaciones</span>
        </a>

        @if(auth()->user()->isJefeArea() || auth()->user()->isAdmin())
        <a href="{{ route('jefe.team') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
           {{ request()->routeIs('jefe.*') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Mi Equipo</span>
        </a>
        @endif

        @if(auth()->user()->isAdmin())
        <a href="{{ route('reports.index') }}"
           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
           {{ request()->routeIs('reports.*') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Reportes</span>
        </a>

        <div class="pt-4">
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest px-3 pb-2">Administración</p>

            <a href="{{ route('admin.templates.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
               {{ request()->routeIs('admin.templates.*') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <span>Plantillas</span>
            </a>

            <a href="{{ route('admin.areas.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium mt-0.5
               {{ request()->routeIs('admin.areas.*') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Áreas</span>
            </a>

            <a href="{{ route('admin.position-types.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium mt-0.5
               {{ request()->routeIs('admin.position-types.*') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Cargos</span>
            </a>

            <a href="{{ route('admin.employees.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium mt-0.5
               {{ request()->routeIs('admin.employees.*') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Empleados</span>
            </a>

            <a href="{{ route('admin.settings.index') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium mt-0.5
               {{ request()->routeIs('admin.settings.*') ? 'bg-gradient-to-r from-blue-500 to-sky-500 text-white shadow-lg shadow-blue-400/30' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Configuración</span>
            </a>

            @if(auth()->user()?->isSuperAdmin())
            <div class="mt-4 pt-3 border-t border-amber-200/50">
                <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-amber-600 mb-1">Super Admin</p>
                <a href="{{ route('admin.roles.index') }}"
                   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium mt-0.5
                   {{ request()->routeIs('admin.roles.*') ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-400/30' : 'text-slate-600 hover:bg-amber-50 hover:text-amber-700' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Roles y Permisos</span>
                </a>
            </div>
            @endif


        </div>
        @endif
    </nav>

    {{-- Logout --}}
    <div class="p-3 border-t border-blue-100 flex-shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>

{{-- ═══ MAIN ═══ --}}
<div class="flex-1 flex flex-col min-w-0 lg:pl-64 transition-all duration-300">

    {{-- Top bar --}}
    <header class="sticky top-0 z-10 bg-white border-b border-gray-200 shadow-sm px-4 lg:px-8 h-16 flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <div class="flex-1 min-w-0">
            @hasSection('header')
                @yield('header')
            @else
                <h1 class="text-gray-800 font-semibold text-lg truncate">@yield('title', 'Dashboard')</h1>
            @endif
        </div>
        <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
            {{-- Notification bell --}}
            <div x-data="notifBell()" x-init="fetchCount()" class="relative">
                <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Notificaciones">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span x-show="count > 0" x-text="count > 99 ? '99+' : count" x-cloak
                          class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold bg-rose-500 text-white ring-2 ring-white notification-dot"></span>
                </a>
            </div>
            @if(auth()->user()->area)
                <span class="text-gray-600 text-sm">{{ auth()->user()->area->name }}</span>
                <span class="text-gray-300">·</span>
            @endif
            <span class="inline-flex items-center gap-1.5 bg-gradient-to-r from-blue-50 to-sky-50 text-blue-700 px-3 py-1.5 rounded-full text-xs font-semibold border border-blue-200 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                {{ auth()->user()->roles->first()?->description ?? 'Sin rol' }}
            </span>
        </div>
    </header>

    {{-- Toast: success --}}
    @if(session('success'))
    <div id="toast-ok" class="toast fixed top-20 right-5 z-50 flex items-start gap-3 bg-white border border-emerald-200 shadow-xl shadow-emerald-100/50 rounded-2xl p-4 max-w-xs">
        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-slate-800">Listo</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ session('success') }}</p>
        </div>
        <button onclick="document.getElementById('toast-ok').remove()" class="text-slate-300 hover:text-slate-500 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif

    {{-- Toast: errors --}}
    @if($errors->any())
    <div id="toast-err" class="toast fixed top-20 right-5 z-50 flex items-start gap-3 bg-white border border-rose-200 shadow-xl shadow-rose-100/50 rounded-2xl p-4 max-w-xs">
        <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-slate-800">Revisa los datos</p>
            @foreach($errors->all() as $err)<p class="text-xs text-rose-600 mt-0.5">• {{ $err }}</p>@endforeach
        </div>
        <button onclick="document.getElementById('toast-err').remove()" class="text-slate-300 hover:text-slate-500 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif

    {{-- Page content --}}
    <main class="flex-1 px-4 lg:px-8 py-6 page-content">
        @yield('content')
    </main>
</div>

<script>
function toggleSidebar(){
    const sb=document.getElementById('sidebar'),ov=document.getElementById('sidebar-overlay');
    sb.classList.toggle('-translate-x-full');
    ov.classList.toggle('hidden');
}
function dismissToast(id,delay){
    setTimeout(()=>{
        const t=document.getElementById(id);
        if(t){t.classList.add('hiding');setTimeout(()=>t.remove(),300)}
    },delay);
}
dismissToast('toast-ok',4500);
dismissToast('toast-err',7000);

/* Intersection Observer for scroll-triggered animations */
document.addEventListener('DOMContentLoaded',()=>{
    const obs=new IntersectionObserver((entries)=>{
        entries.forEach(e=>{
            if(e.isIntersecting){e.target.classList.add('anim-visible');obs.unobserve(e.target)}
        });
    },{threshold:0.1,rootMargin:'0px 0px -40px 0px'});
    document.querySelectorAll('.anim-on-scroll').forEach(el=>obs.observe(el));
});

/* Notification bell */
function notifBell() {
    return {
        count: 0,
        fetchCount() {
            fetch('{{ route("notifications.unread-count") }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => { this.count = d.count || 0; })
            .catch(() => {});
            // Refresh every 60 seconds
            setTimeout(() => this.fetchCount(), 60000);
        }
    };
}
</script>
@stack('scripts')
</body>
</html>
