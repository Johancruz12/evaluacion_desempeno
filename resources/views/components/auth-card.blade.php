@props(['title' => 'Contraseña', 'heading' => '', 'sub' => '', 'step' => null])
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - JUnical</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *{font-family:'Inter',sans-serif}
        body{background:
            radial-gradient(circle at 8% 15%, rgba(147,197,253,.45), transparent 34%),
            radial-gradient(circle at 88% 20%, rgba(186,230,253,.35), transparent 34%),
            linear-gradient(155deg,#f7fbff 0%,#eef5ff 48%,#ffffff 100%)}
        @keyframes in{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
        .card{animation:in .5s ease both}
        input:focus{box-shadow:0 0 0 3px rgba(59,130,246,.18)!important;border-color:#60a5fa!important}
        .glass{background:rgba(255,255,255,.82);backdrop-filter:blur(10px);border:1px solid rgba(191,219,254,.85)}
        .dot{width:8px;height:8px;border-radius:50%;background:#cbd5e1}
        .dot.active{background:#3b82f6;box-shadow:0 0 0 4px rgba(59,130,246,.18)}
        .dot.done{background:#10b981}
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="card w-full max-w-md glass rounded-3xl shadow-2xl shadow-blue-100/80 p-7 sm:p-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center text-white shadow-lg shadow-blue-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-12V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-800">{{ $heading }}</h1>
                @if($sub)<p class="text-xs text-slate-500">{{ $sub }}</p>@endif
            </div>
        </div>

        @if($step)
        <div class="flex items-center gap-2 mb-5">
            <span class="dot {{ $step >= 1 ? ($step==1?'active':'done') : '' }}"></span>
            <span class="dot {{ $step >= 2 ? ($step==2?'active':'done') : '' }}"></span>
            <span class="dot {{ $step >= 3 ? ($step==3?'active':'done') : '' }}"></span>
            <span class="text-[11px] text-slate-500 ml-2">Paso {{ $step }} de 3</span>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mb-4">
            @foreach($errors->all() as $e)
                <p class="text-rose-700 text-xs">• {{ $e }}</p>
            @endforeach
        </div>
        @endif

        @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mb-4">
            <p class="text-emerald-700 text-xs">{{ session('status') }}</p>
        </div>
        @endif

        {{ $slot }}

        <div class="mt-5 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-500">
                ← Volver al inicio de sesión
            </a>
        </div>
    </div>
</body>
</html>
