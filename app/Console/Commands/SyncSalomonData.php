<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Person;
use App\Models\PositionType;
use App\Models\User;
use App\Services\SalomonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncSalomonData extends Command
{
    protected $signature = 'salomon:sync {--areas : Sync areas only} {--cargos : Sync positions only} {--empleados : Sync employees only}';
    protected $description = 'Sync areas, positions, and employees from Salomón (SQL Server) into the local database';

    public function handle(): int
    {
        $salomon = new SalomonService();

        $syncAll = !$this->option('areas') && !$this->option('cargos') && !$this->option('empleados');

        if ($syncAll || $this->option('areas')) {
            $this->syncAreas($salomon);
        }

        if ($syncAll || $this->option('cargos')) {
            $this->syncCargos($salomon);
        }

        if ($syncAll || $this->option('empleados')) {
            $this->syncEmpleados($salomon);
        }

        $this->newLine();
        $this->info('✅ Sincronización completada.');

        return Command::SUCCESS;
    }

    private function syncAreas(SalomonService $salomon): void
    {
        $this->info('📦 Sincronizando áreas desde Salomón...');

        $areas = $salomon->getAllAreas();
        $created = 0;
        $updated = 0;

        foreach ($areas as $area) {
            $local = Area::where('salomon_codigo', $area->codigo)->first();

            if ($local) {
                $local->update([
                    'name' => trim($area->nombre),
                    'is_active' => (bool) $area->activo,
                ]);
                $updated++;
            } else {
                Area::create([
                    'name' => trim($area->nombre),
                    'description' => trim($area->descripcion ?? ''),
                    'is_active' => (bool) $area->activo,
                    'salomon_codigo' => $area->codigo,
                ]);
                $created++;
            }
        }

        $this->info("   Áreas: {$created} creadas, {$updated} actualizadas (total: " . count($areas) . ')');
    }

    private function syncCargos(SalomonService $salomon): void
    {
        $this->info('📦 Sincronizando cargos desde Salomón...');

        $cargos = $salomon->getAllCargos();
        $created = 0;
        $updated = 0;

        foreach ($cargos as $cargo) {
            $local = PositionType::where('salomon_codigo', $cargo->codigo)->first();

            if ($local) {
                $local->update([
                    'name' => trim($cargo->nombre),
                    'is_active' => (bool) $cargo->activo,
                ]);
                $updated++;
            } else {
                PositionType::create([
                    'name' => trim($cargo->nombre),
                    'is_active' => (bool) $cargo->activo,
                    'salomon_codigo' => $cargo->codigo,
                ]);
                $created++;
            }
        }

        $this->info("   Cargos: {$created} creados, {$updated} actualizados (total: " . count($cargos) . ')');
    }

    private function syncEmpleados(SalomonService $salomon): void
    {
        $this->info('📦 Sincronizando empleados desde Salomón...');

        $empleados = $salomon->getActiveEmployees();
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($empleados as $emp) {
            // Skip if no cedula
            if (empty($emp->cedula)) {
                $skipped++;
                continue;
            }

            // Ensure area exists
            $area = null;
            if ($emp->area_codigo) {
                $area = Area::firstOrCreate(
                    ['salomon_codigo' => $emp->area_codigo],
                    [
                        'name' => trim($emp->area_nombre ?? 'Sin área'),
                        'is_active' => true,
                    ]
                );
            }

            // Ensure cargo exists
            $cargo = null;
            if ($emp->cargo_codigo) {
                $cargo = PositionType::firstOrCreate(
                    ['salomon_codigo' => $emp->cargo_codigo],
                    [
                        'name' => trim($emp->cargo_nombre ?? 'Sin cargo'),
                        'area_id' => $area?->id,
                        'is_active' => true,
                    ]
                );
            }

            // Find or create person
            $person = Person::where('document_number', $emp->cedula)->first();

            $firstName = trim(($emp->primer_nombre ?? '') . ' ' . ($emp->segundo_nombre ?? ''));
            $lastName = trim(($emp->primer_apellido ?? '') . ' ' . ($emp->segundo_apellido ?? ''));

            if ($person) {
                $person->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
                $updated++;
            } else {
                $person = Person::create([
                    'document_number' => $emp->cedula,
                    'document_type' => 'CC',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
                $created++;
            }

            // Find or create user
            $user = User::where('person_id', $person->id)
                ->orWhere('login', $emp->cedula)
                ->orWhere('salomon_codigo', $emp->trabajador_codigo)
                ->first();

            if (!$user) {
                $user = User::create([
                    'login' => $emp->cedula,
                    'password' => Hash::make($emp->cedula),
                    'person_id' => $person->id,
                    'position_type_id' => $cargo?->id,
                    'is_active' => true,
                    'must_change_password' => true,
                    'salomon_codigo' => $emp->trabajador_codigo,
                ]);

                // Assign 'empleado' role
                $empleadoRole = \App\Models\Role::where('slug', 'empleado')->first();
                if ($empleadoRole && !$user->roles()->where('role_id', $empleadoRole->id)->exists()) {
                    $user->roles()->attach($empleadoRole->id);
                }
            } else {
                $user->update([
                    'person_id' => $person->id,
                    'position_type_id' => $cargo?->id,
                    'salomon_codigo' => $emp->trabajador_codigo,
                ]);
            }
        }

        $this->info("   Empleados: {$created} creados, {$updated} actualizados, {$skipped} omitidos (total: " . count($empleados) . ')');
    }
}
