<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationResponse extends Model
{
    protected $fillable = ['evaluation_id', 'criteria_id', 'auto_score', 'evaluator_score', 'comment'];

    protected function casts(): array
    {
        return [
            'auto_score' => 'decimal:2',
            'evaluator_score' => 'decimal:2',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class, 'criteria_id');
    }
}
