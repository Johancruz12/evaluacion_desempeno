<x-auth-card title="Recuperar contraseña" heading="Recuperar contraseña" sub="Verifica tu identidad con un código" :step="1">

    <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 mb-5">
        <p class="text-sky-800 text-xs leading-relaxed">
            Ingresa tu <strong>cédula</strong> y <strong>teléfono</strong> para verificar tu identidad,
            y el <strong>correo</strong> al que quieres que llegue el código de 6 dígitos.
        </p>
    </div>

    <form method="POST" action="{{ route('password.otp.send') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Cédula</label>
            <input type="text" name="login" value="{{ old('login') }}" required autofocus inputmode="numeric"
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                   placeholder="Ej: 1070588425">
            @error('login')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Teléfono</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required inputmode="tel"
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                   placeholder="Ej: 3001234567">
            @error('phone')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required inputmode="email"
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                   placeholder="usuario@junical.com.co">
            <p class="text-[11px] text-slate-500 mt-1">El código llegará a este correo. Debe ser tu correo institucional (@junical.com.co).</p>
            @error('email')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-blue-500 hover:bg-blue-400 text-white font-semibold py-3.5 mt-2 transition-colors">
            Enviar código
        </button>
    </form>
</x-auth-card>
