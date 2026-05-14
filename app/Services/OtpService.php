<?php

namespace App\Services;

use App\Mail\OtpCodeMail;
use App\Models\PasswordOtp;
use App\Models\Person;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public const CODE_TTL_MINUTES = 10;
    public const MAX_ATTEMPTS     = 5;
    public const RESEND_COOLDOWN  = 60; // segundos

    private string $lastCode = '';

    /**
     * Localiza al usuario por cédula y verifica que el teléfono coincida.
     * Si el dato local está vacío, consulta Salomón y sincroniza automáticamente.
     * Devuelve el User o null.
     */
    public function resolveUser(string $cedula, string $phone): ?User
    {
        $cedula        = trim($cedula);
        $phoneIngresado = $this->normalizePhone($phone);

        $user = User::with('person')->where('login', $cedula)->first();
        if (!$user) {
            return null;
        }

        // Obtener teléfono almacenado localmente
        $storedLocal = $this->normalizePhone((string) optional($user->person)->phone);

        // Si el dato local está vacío, consultar Salomón y sincronizar
        if ($storedLocal === '') {
            try {
                $salomon  = app(SalomonService::class);
                $fromSal  = $salomon->getPhoneByCedula($cedula);
                if ($fromSal) {
                    $storedLocal = $this->normalizePhone($fromSal);
                    // Guardar en la BD local para próximas veces
                    if ($user->person) {
                        $user->person->update(['phone' => $fromSal]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[OTP] No se pudo sincronizar teléfono desde Salomón', [
                    'login' => $cedula,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($storedLocal === '' || $storedLocal !== $phoneIngresado) {
            return null;
        }

        return $user;
    }

    /**
     * Genera un OTP de 6 dígitos, lo guarda hasheado y lo envía al correo indicado por el usuario.
     * La validación de identidad (cédula + teléfono) ya fue hecha antes de llamar este método.
     */
    public function generateAndSend(User $user, string $phone, string $targetEmail, string $purpose = 'reset', ?string $ip = null): PasswordOtp
    {
        // Eliminar OTPs anteriores no usados para evitar confusión
        PasswordOtp::where('login', $user->login)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->lastCode = $code;

        $otp = PasswordOtp::create([
            'login'        => $user->login,
            'phone'        => $this->normalizePhone($phone),
            'target_email' => strtolower(trim($targetEmail)),
            'code_hash'    => Hash::make($code),
            'purpose'      => $purpose,
            'expires_at'   => Carbon::now()->addMinutes(self::CODE_TTL_MINUTES),
            'ip'           => $ip,
        ]);

        $this->deliver($user, $code, $targetEmail);

        return $otp;
    }

    /** Devuelve el último código generado en esta request (solo para modo dev). */
    public function getLastCode(): string
    {
        return $this->lastCode;
    }

    /**
     * Verifica el código contra el OTP activo más reciente del usuario.
     * Devuelve true si es válido y marca el OTP como usado.
     */
    public function verify(string $cedula, string $code, string $purpose = 'reset'): bool
    {
        $otp = PasswordOtp::where('login', trim($cedula))
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        if (!$otp) {
            return false;
        }

        if ($otp->isExpired()) {
            return false;
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        $otp->increment('attempts');

        if (!Hash::check($code, $otp->code_hash)) {
            return false;
        }

        $otp->update(['used_at' => now()]);
        return true;
    }

    /**
     * ¿Hay un OTP usado (verificado) reciente disponible para confirmar el cambio?
     */
    public function hasRecentlyVerified(string $cedula, string $purpose = 'reset'): bool
    {
        $otp = PasswordOtp::where('login', trim($cedula))
            ->where('purpose', $purpose)
            ->whereNotNull('used_at')
            ->orderByDesc('used_at')
            ->first();

        if (!$otp) return false;

        // Solo válido si fue verificado en los últimos 10 minutos
        return $otp->used_at->gt(now()->subMinutes(self::CODE_TTL_MINUTES));
    }

    /**
     * Entrega el código al correo indicado por el usuario.
     * El correo destino ya fue validado/aceptado en el formulario.
     */
    protected function deliver(User $user, string $code, string $targetEmail): void
    {
        $minutes = self::CODE_TTL_MINUTES;
        $name    = optional($user->person)->first_name ?? 'Usuario';

        // Canal 1 — Log (siempre, útil para soporte/debug)
        Log::info('[OTP] Código generado', [
            'login'        => $user->login,
            'target_email' => $targetEmail,
            'code'         => $code,
            'ttl_min'      => $minutes,
        ]);

        // Canal 2 — Email al correo que el usuario indicó
        if ($targetEmail) {
            try {
                Mail::to($targetEmail)->send(new OtpCodeMail($code, $minutes, $name));
            } catch (\Throwable $e) {
                Log::warning('[OTP] No se pudo enviar correo', [
                    'to'    => $targetEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Canal 3 — SMS real: stub. Cuando contraten proveedor, aquí va el SDK.
        // app(SmsProvider::class)->send($user->person->phone, "Tu código JUnical: $code (válido $minutes min)");
    }

    protected function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
