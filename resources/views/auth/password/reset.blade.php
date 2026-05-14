<x-auth-card title="Nueva contraseña" heading="Define tu nueva contraseña" sub="Mínimo 8 caracteres, con mayúscula, número y símbolo" :step="3">

    <form method="POST" action="{{ route('password.otp.reset') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nueva contraseña</label>
            <input type="password" name="password" required autofocus
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                   placeholder="••••••••">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" required
                   class="w-full px-3 py-3 rounded-xl border border-blue-200 bg-white text-slate-800 text-sm focus:outline-none"
                   placeholder="••••••••">
        </div>

        <ul class="text-[11px] text-slate-500 space-y-0.5 pl-4 list-disc">
            <li>Al menos 8 caracteres</li>
            <li>Una mayúscula y una minúscula</li>
            <li>Un número y un carácter especial</li>
            <li>No puede ser igual a tu cédula</li>
        </ul>

        <button type="submit" class="w-full rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold py-3.5 mt-2 transition-colors">
            Cambiar contraseña
        </button>
    </form>
</x-auth-card>
