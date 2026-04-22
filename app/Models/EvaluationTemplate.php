<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationTemplate extends Model
{
    protected $fillable = ['name', 'description', 'position_type_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function positionType(): BelongsTo
    {
        return $this->belongsTo(PositionType::class);
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'evaluation_template_area', 'template_id', 'area_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(EvaluationSection::class, 'template_id')->orderBy('order');
    }

    public function scoringRanges(): HasMany
    {
        return $this->hasMany(ScoringRange::class, 'template_id')->orderBy('order');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'template_id');
    }
}
