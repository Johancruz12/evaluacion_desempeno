<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PositionType extends Model
{
    protected $fillable = ['name', 'description', 'area_id', 'is_active', 'salomon_codigo'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function evaluationTemplates(): HasMany
    {
        return $this->hasMany(EvaluationTemplate::class);
    }
}
