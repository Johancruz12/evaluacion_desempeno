<x-auth-card title="Cambiar contraseña" heading="Cambiar contraseña" sub="Verificación adicional por seguridad" :step="1">

    <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 mb-5">
        <p class="text-sky-800 text-xs leading-relaxed">
            Ingresa tu <strong>teléfono</strong> para verificar tu identidad y el <strong>correo</strong>
            al que quieres que llegue el código de 6 dígitos.
        </p>
    </div>

    <form method="POST" action="{{ route('password.otp.change.send') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Teléfono</label>
            <input type="tel" name="phone" value="{{ old('phone', optional(auth()->user()->person)->phone) }}" required inputmode="tel"
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                   placeholder="Ej: 3001234567">
            @error('phone')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email', optional(auth()->user()->person)->email) }}" required inputmode="email"
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                   placeholder="usuario@junical.com.co">
            <p class="text-[11px] text-slate-500 mt-1">El código llegará a este correo. Debe ser tu correo institucional (@junical.com.co).</p>
            @error('email')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-blue-500 hover:bg-blue-400 text-white font-semibold py-3.5 mt-2 transition-colors">
            Enviar código
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('dashboard') }}" class="text-xs text-slate-500 hover:text-blue-600">← Cancelar</a>
    </div>
</x-auth-card>
