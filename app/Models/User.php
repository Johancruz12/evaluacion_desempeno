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
        'must_change_password', 'salomon_codigo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
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
     * roles y permisos del sistema. Se define por cédula en config/auth.php
     * (o por variable de entorno SUPER_ADMIN_CEDULA).
     */
    public function isSuperAdmin(): bool
    {
        $cedula = config('auth.super_admin_cedula', env('SUPER_ADMIN_CEDULA', '1070588425'));
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
