<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            // Evaluaciones
            ['slug' => 'evaluations.view',            'description' => 'Ver evaluaciones'],
            ['slug' => 'evaluations.create',          'description' => 'Crear evaluaciones'],
            ['slug' => 'evaluations.evaluate',        'description' => 'Calificar evaluaciones'],
            ['slug' => 'evaluations.reset',           'description' => 'Reiniciar evaluaciones'],
            ['slug' => 'evaluations.export',          'description' => 'Exportar evaluaciones a PDF'],
            ['slug' => 'evaluations.bulk_reset',      'description' => 'Reiniciar evaluaciones en lote'],

            // Plantillas
            ['slug' => 'templates.view',              'description' => 'Ver plantillas de evaluación'],
            ['slug' => 'templates.manage',            'description' => 'Crear y editar plantillas'],
            ['slug' => 'templates.delete',            'description' => 'Eliminar plantillas'],

            // Tipos de sección
            ['slug' => 'section_types.manage',        'description' => 'Gestionar tipos de sección'],

            // Áreas
            ['slug' => 'areas.view',                  'description' => 'Ver áreas'],
            ['slug' => 'areas.manage',                'description' => 'Crear y editar áreas'],

            // Cargos
            ['slug' => 'positions.view',              'description' => 'Ver cargos'],
            ['slug' => 'positions.manage',            'description' => 'Crear y editar cargos'],

            // Empleados / Usuarios
            ['slug' => 'employees.view',              'description' => 'Ver empleados'],
            ['slug' => 'employees.manage',            'description' => 'Crear y editar empleados'],
            ['slug' => 'employees.deactivate',        'description' => 'Activar / desactivar empleados'],
            ['slug' => 'employees.reset_password',    'description' => 'Resetear contraseña de empleados'],

            // Mi equipo (Jefe)
            ['slug' => 'team.view',                   'description' => 'Ver mi equipo'],

            // Reportes
            ['slug' => 'reports.view',                'description' => 'Ver reportes'],
            ['slug' => 'reports.export',              'description' => 'Exportar reportes'],

            // Configuración
            ['slug' => 'settings.view',               'description' => 'Ver configuración del sistema'],
            ['slug' => 'settings.manage',             'description' => 'Modificar configuración del sistema'],

            // Roles y permisos
            ['slug' => 'roles.view',                  'description' => 'Ver roles y permisos'],
            ['slug' => 'roles.manage',                'description' => 'Gestionar roles y permisos'],

            // Planes de desarrollo
            ['slug' => 'development_plans.view',      'description' => 'Ver planes de desarrollo'],
            ['slug' => 'development_plans.manage',    'description' => 'Gestionar planes de desarrollo'],

            // Notificaciones
            ['slug' => 'notifications.view',          'description' => 'Ver notificaciones'],
        ];

        $now = now();
        $rows = array_map(fn ($p) => $p + ['created_at' => $now, 'updated_at' => $now], $permissions);

        // Insertar solo los que no existen
        foreach ($rows as $row) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $row['slug']],
                $row
            );
        }

        // Asignar permisos por defecto a cada rol del sistema
        $rolePermissions = [
            'director_rh' => '*', // Todos
            'jefe_area' => [
                'team.view',
                'evaluations.view',
                'evaluations.evaluate',
                'evaluations.export',
                'employees.view',
                'reports.view',
                'notifications.view',
                'development_plans.view',
            ],
            'empleado' => [
                'evaluations.view',
                'evaluations.export',
                'notifications.view',
                'development_plans.view',
            ],
        ];

        $allPermissionIds = DB::table('permissions')->pluck('id', 'slug');

        foreach ($rolePermissions as $roleSlug => $permSlugs) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');
            if (! $roleId) {
                continue;
            }

            // Solo asignar si el rol no tiene ningún permiso aún (no sobrescribir asignaciones existentes)
            $hasAny = DB::table('role_has_permission')->where('role_id', $roleId)->exists();
            if ($hasAny) {
                continue;
            }

            $ids = $permSlugs === '*'
                ? $allPermissionIds->values()->all()
                : $allPermissionIds->only((array) $permSlugs)->values()->all();

            foreach ($ids as $pid) {
                DB::table('role_has_permission')->insertOrIgnore([
                    'role_id'       => $roleId,
                    'permission_id' => $pid,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op: no eliminamos catálogo de permisos al hacer rollback para evitar romper asignaciones.
    }
};
