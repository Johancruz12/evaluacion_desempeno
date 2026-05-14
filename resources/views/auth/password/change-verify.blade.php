<x-auth-card title="Verificar código" heading="Verificar código" sub="Ingresa el código de 6 dígitos" :step="2">

    @php
        $destEmail = session('otp.change.email', '');
        $parts     = explode('@', $destEmail, 2);
        $local     = $parts[0] ?? '';
        $domain    = $parts[1] ?? '';
        $masked    = mb_substr($local, 0, min(3, mb_strlen($local))) . str_repeat('*', max(0, mb_strlen($local)-3)) . '@' . $domain;
    @endphp

    <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 mb-5">
        <p class="text-sky-800 text-xs leading-relaxed">
            Enviamos un código de 6 dígitos a
            @if($destEmail)<strong>{{ $masked }}</strong>.@endif
            Es válido por <strong>10 minutos</strong>.
        </p>
    </div>

    <form method="POST" action="{{ route('password.otp.change.confirm') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Código</label>
            <input type="text" name="code" required autofocus inputmode="numeric"
                   maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code"
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-2xl tracking-[0.5em] text-center font-bold focus:outline-none"
                   placeholder="••••••">
        </div>

        <button type="submit" class="w-full rounded-xl bg-blue-500 hover:bg-blue-400 text-white font-semibold py-3.5 mt-2 transition-colors">
            Verificar y continuar
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('password.otp.change.request') }}" class="text-xs text-slate-500 hover:text-blue-600">
            ¿No te llegó? Volver a empezar
        </a>
    </div>
</x-auth-card>
