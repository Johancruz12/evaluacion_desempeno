<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['name', 'description', 'is_active', 'salomon_codigo'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function positionTypes(): HasMany
    {
        return $this->hasMany(PositionType::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function evaluationTemplates(): BelongsToMany
    {
        return $this->belongsToMany(EvaluationTemplate::class, 'evaluation_template_area', 'area_id', 'template_id');
    }
}
