<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'login', 'password', 'person_id', 'area_id',
        'position_type_id', 'employee_code', 'is_active',
        'must_change_password', 'is_super_admin', 'salomon_codigo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_has_roles');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function positionType(): BelongsTo
    {
        return $this->belongsTo(PositionType::class);
    }

    public function evaluationsAsEmployee(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'employee_id');
    }

    public function evaluationsAsEvaluator(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function getNameAttribute(): string
    {
        return $this->person?->full_name ?? $this->login;
    }

    public function isAdmin(): bool
    {
        return $this->roles()->where('slug', 'director_rh')->exists();
    }

    /**
     * Super Administrador: único usuario autorizado a gestionar
     * roles y permisos del sistema.
     *
     * Orden de verificación (a prueba de despliegues incompletos):
     *   1) Columna persistida `users.is_super_admin` (si ya existe).
     *   2) Cédula configurada en config/auth.php (si está disponible).
     *   3) Fallback duro a la cédula 1070588425 (último recurso para
     *      ambientes con config:cache viejo o sin la columna nueva).
     */
    public function isSuperAdmin(): bool
    {
        // 1) Columna en BD. Usamos attributes para no romper si la columna
        //    aún no existe (por ejemplo, antes de correr la migración).
        if (!empty($this->attributes['is_super_admin'] ?? null)) {
            return true;
        }

        // 2) Config (puede estar cacheada vacía en producción).
        $cedula = config('auth.super_admin_cedula');

        // 3) Fallback duro (mismo valor por defecto que en config/auth.php).
        if (empty($cedula)) {
            $cedula = '1070588425';
        }

        return $this->login === $cedula;
    }

    public function isJefeArea(): bool
    {
        return $this->roles()->where('slug', 'jefe_area')->exists();
    }

    public function isEmpleado(): bool
    {
        return $this->roles()->where('slug', 'empleado')->exists();
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasPermission(string $slug): bool
    {
        return $this->roles()->whereHas('permissions', function ($q) use ($slug) {
            $q->where('slug', $slug);
        })->exists();
    }

    public function isFromStrategicArea(): bool
    {
        $areaName = Str::of((string) ($this->area?->name ?? ''))
            ->ascii()
            ->lower()
            ->value();

        return str_contains($areaName, 'recursos humanos')
            || str_contains($areaName, 'gerencia')
            || str_contains($areaName, 'presidencia');
    }

    public function canCreateEvaluations(): bool
    {
        return $this->isAdmin() || $this->isFromStrategicArea();
    }

    public function canEditEvaluationTemplates(): bool
    {
        return $this->isAdmin() || $this->isFromStrategicArea();
    }
}
