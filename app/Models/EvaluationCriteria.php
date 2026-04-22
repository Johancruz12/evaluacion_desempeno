<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriteria extends Model
{
    protected $table = 'evaluation_criteria';

    protected $fillable = ['section_id', 'name', 'description', 'order', 'max_score', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_score' => 'decimal:2',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(EvaluationSection::class, 'section_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EvaluationResponse::class, 'criteria_id');
    }
}
