<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationSection extends Model
{
    protected $fillable = ['template_id', 'name', 'description', 'type', 'order', 'weight', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'weight' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'template_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationCriteria::class, 'section_id')->orderBy('order');
    }
}
