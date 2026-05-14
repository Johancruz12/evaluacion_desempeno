<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetController extends Controller
{
    public function __construct(protected OtpService $otp)
    {
    }

    // ─────────────────────────────────────────────────────────────
    // Paso 1 — Pedir cédula + teléfono
    // ─────────────────────────────────────────────────────────────
    public function showRequest()
    {
        return view('auth.password.request');
    }

    public function sendCode(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', 'ends_with:@junical.com.co'],
        ], [
            'login.required' => 'Ingresa tu cédula.',
            'phone.required' => 'Ingresa tu número de teléfono.',
            'email.required' => 'Ingresa el correo donde quieres recibir el código.',
            'email.email'    => 'El correo no tiene un formato válido.',
            'email.ends_with' => 'Debes ingresar un correo institucional (@junical.com.co).',
        ]);

        $key = 'otp-send:' . $request->ip() . ':' . $data['login'];
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['login' => 'Demasiados intentos. Espera unos minutos antes de volver a intentar.'])
                ->onlyInput('login', 'phone');
        }
        RateLimiter::hit($key, 600); // 10 min

        $user = $this->otp->resolveUser($data['login'], $data['phone']);

        if (!$user) {
            return back()->withErrors([
                'login' => 'La cédula y el teléfono no coinciden con ningún usuario registrado.',
            ])->onlyInput('login', 'phone');
        }

        $otp = $this->otp->generateAndSend($user, $data['phone'], $data['email'], 'reset', $request->ip());

        // Guardar identidad y correo destino en sesión para los siguientes pasos
        $request->session()->put('otp.login',  $user->login);
        $request->session()->put('otp.email',  $data['email']);
        $request->session()->put('otp.purpose', 'reset');

        $statusMsg = 'Enviamos un código de 6 dígitos a ' . $this->maskEmail($data['email']) . '. Ingrésalo a continuación.';

        return redirect()->route('password.otp.verify.show')->with('status', $statusMsg);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(3, mb_strlen($local)));
        return $visible . str_repeat('*', max(0, mb_strlen($local) - 3)) . '@' . $domain;
    }

    // ─────────────────────────────────────────────────────────────
    // Paso 2 — Verificar código
    // ─────────────────────────────────────────────────────────────
    public function showVerify(Request $request)
    {
        if (!$request->session()->has('otp.login')) {
            return redirect()->route('password.otp.request');
        }
        return view('auth.password.verify');
    }

    public function verifyCode(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.digits' => 'El código debe ser de 6 dígitos.',
        ]);

        $login = $request->session()->get('otp.login');
        if (!$login) {
            return redirect()->route('password.otp.request');
        }

        if (!$this->otp->verify($login, $data['code'], 'reset')) {
            return back()->withErrors(['code' => 'Código incorrecto o expirado.']);
        }

        return redirect()->route('password.otp.reset.show')->with('status',
            'Identidad verificada. Define tu nueva contraseña.');
    }

    // ─────────────────────────────────────────────────────────────
    // Paso 3 — Establecer nueva contraseña
    // ─────────────────────────────────────────────────────────────
    public function showReset(Request $request)
    {
        $login = $request->session()->get('otp.login');
        if (!$login || !$this->otp->hasRecentlyVerified($login, 'reset')) {
            return redirect()->route('password.otp.request')
                ->withErrors(['login' => 'La verificación expiró. Inicia el proceso nuevamente.']);
        }
        return view('auth.password.reset');
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación no coincide.',
            'password.regex'     => 'La contraseña debe incluir minúscula, mayúscula, número y carácter especial.',
        ]);

        $login = $request->session()->get('otp.login');
        if (!$login || !$this->otp->hasRecentlyVerified($login, 'reset')) {
            return redirect()->route('password.otp.request')
                ->withErrors(['login' => 'La verificación expiró. Inicia el proceso nuevamente.']);
        }

        $user = \App\Models\User::where('login', $login)->first();
        if (!$user) {
            return redirect()->route('password.otp.request');
        }

        if ($data['password'] === $user->login) {
            return back()->withErrors(['password' => 'La nueva contraseña no puede ser igual a tu cédula.']);
        }

        $user->update([
            'password' => $data['password'],
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $request->session()->forget(['otp.login', 'otp.purpose']);

        return redirect()->route('login')->with('status',
            'Contraseña actualizada correctamente. Inicia sesión con tu nueva contraseña.');
    }
}
