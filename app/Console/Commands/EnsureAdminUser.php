<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Person;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnsureAdminUser extends Command
{
    /**
     * Ejecutar en producción:
     *   php artisan admin:ensure
     *   php artisan admin:ensure --cedula=1070588425
     *
     * Crea (o repara) el usuario administrador con login = cédula y password = cédula,
     * le asigna el rol "Administrador" (slug interno director_rh) y marca must_change_password = true.
     * Es idempotente: puede ejecutarse varias veces sin duplicar datos.
     */
    protected $signature = 'admin:ensure
                            {--cedula=1070588425 : Cédula del admin a crear/reparar}
                            {--first_name=Administrador : Nombre}
                            {--last_name=Salomón : Apellido}';

    protected $description = 'Crea o repara el usuario Administrador principal usando la cédula como login y contraseña inicial.';

    public function handle(): int
    {
        $cedula    = trim($this->option('cedula'));
        $firstName = $this->option('first_name');
        $lastName  = $this->option('last_name');

        if ($cedula === '') {
            $this->error('La cédula no puede estar vacía.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($cedula, $firstName, $lastName) {
            // 1) Roles del sistema (los 3 son requeridos por el código)
            $role = Role::firstOrCreate(
                ['slug' => 'director_rh'],
                ['description' => 'Administrador']
            );
            // Asegurar que la etiqueta visible esté actualizada a "Administrador"
            if ($role->description !== 'Administrador') {
                $role->update(['description' => 'Administrador']);
            }

            Role::firstOrCreate(
                ['slug' => 'jefe_area'],
                ['description' => 'Jefe de Área']
            );
            Role::firstOrCreate(
                ['slug' => 'empleado'],
                ['description' => 'Empleado']
            );

            // 2) Persona
            $person = Person::firstOrCreate(
                ['document_number' => $cedula],
                [
                    'document_type' => 'CC',
                    'first_name'    => $firstName,
                    'last_name'     => $lastName,
                ]
            );

            // 3) Usuario
            $user = User::firstOrNew(['login' => $cedula]);
            $user->person_id            = $person->id;
            $user->password             = $cedula; // cast 'hashed' encripta automáticamente
            $user->is_active            = true;
            $user->must_change_password = true;
            $user->is_super_admin       = true; // marca persistente en BD
            if (!$user->employee_code) {
                $user->employee_code = 'ADM-' . substr($cedula, -4);
            }

            // Asignar área RH si existe (sin romper en ambientes con otro nombre)
            if (!$user->area_id) {
                $area = Area::where('name', 'like', '%recursos%humanos%')->first()
                     ?? Area::first();
                if ($area) {
                    $user->area_id = $area->id;
                    $posType = PositionType::where('area_id', $area->id)->first();
                    if ($posType) {
                        $user->position_type_id = $posType->id;
                    }
                }
            }

            $user->save();

            // 4) Asegurar rol
            if (!$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }

            $this->info('');
            $this->info('✅ Administrador listo:');
            $this->line('   Usuario:     ' . $cedula);
            $this->line('   Contraseña:  ' . $cedula);
            $this->line('   Rol:         Administrador');
            $this->line('   Primer ingreso: se solicitará cambio de contraseña.');
            $this->info('');
        });

        return self::SUCCESS;
    }
}
