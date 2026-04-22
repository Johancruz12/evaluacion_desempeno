<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentPlan extends Model
{
    protected $fillable = [
        'evaluation_id', 'competencia', 'actividad',
        'responsable', 'fecha_seguimiento', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_seguimiento' => 'date',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }
}
