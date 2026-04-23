<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar contraseña - JUnical</title>
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
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="card w-full max-w-md glass rounded-3xl shadow-2xl shadow-blue-100/80 p-7 sm:p-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-400 flex items-center justify-center text-white shadow-lg shadow-blue-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Cambiar contraseña</h1>
                <p class="text-xs text-slate-500">Actualiza tu acceso para continuar</p>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mb-4">
            @foreach($errors->all() as $e)
                <p class="text-rose-700 text-xs">- {{ $e }}</p>
            @endforeach
        </div>
        @endif

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 mb-4">
            <p class="text-emerald-700 text-xs">{{ session('success') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4"
              x-data="{
                  pwd: '',
                  confirm: '',
                  get hasLower()   { return /[a-z]/.test(this.pwd); },
                  get hasUpper()   { return /[A-Z]/.test(this.pwd); },
                  get hasNumber()  { return /[0-9]/.test(this.pwd); },
                  get hasSpecial() { return /[^A-Za-z0-9]/.test(this.pwd); },
                  get hasLength()  { return this.pwd.length >= 8; },
                  get match()      { return this.confirm.length > 0 && this.pwd === this.confirm; },
                  get allOk()      { return this.hasLower && this.hasUpper && this.hasNumber && this.hasSpecial && this.hasLength && this.match; }
              }">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Contraseña actual</label>
                <input type="password" name="current_password" required autofocus
                       class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                       placeholder="Contraseña actual">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nueva contraseña</label>
                <input type="password" name="password" required x-model="pwd"
                       class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                       placeholder="Mínimo 8 caracteres">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" required x-model="confirm"
                       class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                       placeholder="Repite la nueva contraseña">
            </div>

            {{-- Requisitos en vivo --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-1.5" x-show="pwd.length > 0" x-cloak>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Requisitos</p>
                <template x-for="(r, i) in [
                    { ok: hasLength,  label: 'Mínimo 8 caracteres' },
                    { ok: hasLower,   label: 'Al menos una letra minúscula' },
                    { ok: hasUpper,   label: 'Al menos una letra mayúscula' },
                    { ok: hasNumber,  label: 'Al menos un número' },
                    { ok: hasSpecial, label: 'Al menos un carácter especial (!@#$…)' },
                    { ok: match,      label: 'La confirmación coincide' },
                ]" :key="i">
                    <div class="flex items-center gap-2 text-xs" :class="r.ok ? 'text-emerald-600' : 'text-slate-500'">
                        <svg x-show="r.ok" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="!r.ok" class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                        <span x-text="r.label"></span>
                    </div>
                </template>
            </div>

            <button type="submit"
                    :disabled="!allOk"
                    :class="allOk ? 'bg-blue-500 hover:bg-blue-400 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'"
                    class="w-full rounded-xl text-white font-semibold py-3.5 mt-2 transition-colors">
                Guardar nueva contraseña
            </button>
        </form>
    </div>
</body>
</html>
