<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Person;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\User;
use App\Services\SalomonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $cedula = trim($credentials['login']);
        $password = $credentials['password'];

        // 1. Try existing local user first
        $user = User::where('login', $cedula)->where('is_active', true)->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($user->must_change_password) {
                return redirect()->route('password.change');
            }
            return redirect()->intended('/dashboard');
        }

        // 2. If no local user, try to find in Salomón by cédula
        if (!$user) {
            try {
                $salomon = new SalomonService();
                $employee = $salomon->findEmployeeByCedula($cedula);

                if ($employee && $password === $cedula) {
                    // Auto-register from Salomón: cedula is the initial password
                    $user = $this->registerFromSalomon($employee, $cedula);

                    Auth::login($user, $request->boolean('remember'));
                    $request->session()->regenerate();
                    return redirect()->route('password.change');
                }
            } catch (\Throwable $e) {
                Log::warning('Salomón connection failed during login', ['error' => $e->getMessage()]);
            }
        }

        return back()->withErrors([
            'login' => 'Las credenciales no coinciden. Ingresa tu número de cédula como usuario y contraseña.',
        ])->onlyInput('login');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',        // al menos una minúscula
                'regex:/[A-Z]/',        // al menos una mayúscula
                'regex:/[0-9]/',        // al menos un número
                'regex:/[^A-Za-z0-9]/', // al menos un carácter especial
            ],
        ], [
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.regex'     => 'La contraseña debe incluir al menos una letra minúscula, una mayúscula, un número y un carácter especial.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        // Evitar que el usuario reutilice su cédula como nueva contraseña
        if ($request->password === $user->login) {
            return back()->withErrors([
                'password' => 'La nueva contraseña no puede ser igual a tu número de cédula.',
            ]);
        }

        $user->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);

        return redirect('/dashboard')->with('success', 'Contraseña actualizada correctamente. Tu cuenta está segura.');
    }

    /**
     * Mostrar formulario de "olvidé mi contraseña".
     * El usuario ingresa su cédula; si existe en Salomón (o ya en BD local)
     * le reseteamos la contraseña a su cédula y lo forzamos a cambiarla al entrar.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
        ]);

        $cedula = trim($request->input('login'));

        // 1) Buscar usuario local
        $user = User::where('login', $cedula)->first();

        // 2) Si no hay usuario local, validar que exista en Salomón antes de crear
        if (!$user) {
            try {
                $salomon = new SalomonService();
                $employee = $salomon->findEmployeeByCedula($cedula);

                if (!$employee) {
                    return back()->withErrors([
                        'login' => 'No encontramos tu cédula en el sistema. Contacta a Recursos Humanos.',
                    ])->onlyInput('login');
                }

                // Auto-registrar desde Salomón con cédula como contraseña inicial
                $user = $this->registerFromSalomon($employee, $cedula);

                return redirect()->route('login')->with('status',
                    'Tu usuario fue creado. Ingresa con tu cédula como usuario y contraseña, y luego podrás cambiarla.');
            } catch (\Throwable $e) {
                Log::warning('Salomón connection failed during password reset', ['error' => $e->getMessage()]);
                return back()->withErrors([
                    'login' => 'No fue posible validar tu cédula en este momento. Intenta más tarde o contacta al administrador.',
                ])->onlyInput('login');
            }
        }

        // 3) Usuario local encontrado: validar que también siga existiendo en Salomón
        //    (política de seguridad: solo empleados vigentes pueden recuperar contraseña)
        try {
            $salomon = new SalomonService();
            $employee = $salomon->findEmployeeByCedula($cedula);

            if (!$employee) {
                return back()->withErrors([
                    'login' => 'Tu cédula no está activa en Salomón. Contacta a Recursos Humanos.',
                ])->onlyInput('login');
            }
        } catch (\Throwable $e) {
            Log::warning('Salomón connection failed during password reset', ['error' => $e->getMessage()]);
            // No bloqueamos si Salomón está caído: permitimos el reset igualmente, para no dejar a nadie afuera.
        }

        // 4) Reset: password = cédula, forzar cambio al próximo login
        $user->update([
            'password'             => $cedula,       // cast 'hashed' encripta automáticamente
            'must_change_password' => true,
            'is_active'            => true,
        ]);

        return redirect()->route('login')->with('status',
            'Tu contraseña fue restablecida a tu número de cédula. Ingresa con ella y el sistema te pedirá una nueva.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Create local user from Salomón employee data.
     */
    private function registerFromSalomon(object $employee, string $cedula): User
    {
        // Ensure area exists locally
        $area = null;
        if ($employee->area_codigo) {
            $area = Area::firstOrCreate(
                ['salomon_codigo' => $employee->area_codigo],
                [
                    'name' => trim($employee->area_nombre),
                    'is_active' => true,
                ]
            );
        }

        // Ensure position type exists locally
        $positionType = null;
        if ($employee->cargo_codigo) {
            $positionType = PositionType::firstOrCreate(
                ['salomon_codigo' => $employee->cargo_codigo],
                [
                    'name' => trim($employee->cargo_nombre),
                    'area_id' => $area?->id,
                    'is_active' => true,
                ]
            );
        }

        // Create person
        $person = Person::create([
            'document_number' => $cedula,
            'document_type' => 'CC',
            'first_name' => trim(($employee->primer_nombre ?? '') . ' ' . ($employee->segundo_nombre ?? '')),
            'last_name' => trim(($employee->primer_apellido ?? '') . ' ' . ($employee->segundo_apellido ?? '')),
            'phone' => $employee->telefono ?? null,
            'address' => $employee->direccion ?? null,
        ]);

        // Create user
        $user = User::create([
            'login' => $cedula,
            'password' => $cedula, // Will be hashed by cast
            'person_id' => $person->id,
            'area_id' => $area?->id,
            'position_type_id' => $positionType?->id,
            'employee_code' => 'SAL-' . $employee->trabajador_codigo,
            'is_active' => true,
            'must_change_password' => true,
            'salomon_codigo' => $employee->trabajador_codigo,
        ]);

        // Assign employee role
        $empleadoRole = Role::where('slug', 'empleado')->first();
        if ($empleadoRole) {
            $user->roles()->attach($empleadoRole->id);
        }

        return $user;
    }
}
