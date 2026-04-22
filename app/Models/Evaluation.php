<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    protected $fillable = [
        'template_id', 'employee_id', 'evaluator_id',
        'period', 'period_type', 'evaluation_date', 'admission_date',
        'status', 'observations',
        'obs_organizacional', 'obs_cargo', 'obs_responsabilidades',
        'reopen_reason', 'reopen_deadline', 'reopened_at', 'reopened_by',
        'total_auto_score', 'total_evaluator_score', 'final_score',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
            'admission_date' => 'date',
            'reopen_deadline' => 'date',
            'reopened_at' => 'datetime',
            'total_auto_score' => 'decimal:2',
            'total_evaluator_score' => 'decimal:2',
            'final_score' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'template_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EvaluationResponse::class);
    }

    public function developmentPlans(): HasMany
    {
        return $this->hasMany(DevelopmentPlan::class);
    }

    public function reopenedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(EvaluationNotification::class);
    }

    /** Sum per-criterion avg scores (each criterion scored 1–5). */
    public function calculateScores(): void
    {
        $responses = $this->responses()->get();

        $autoTotal = 0;
        $evalTotal = 0;
        $finalTotal = 0;
        $count = 0;

        foreach ($responses as $r) {
            $auto = $r->auto_score !== null ? (float) $r->auto_score : null;
            $eval = $r->evaluator_score !== null ? (float) $r->evaluator_score : null;

            if ($auto !== null) {
                $autoTotal += $auto;
            }
            if ($eval !== null) {
                $evalTotal += $eval;
            }

            if ($auto !== null && $eval !== null) {
                $finalTotal += ($auto + $eval) / 2;
                $count++;
            } elseif ($auto !== null) {
                $finalTotal += $auto;
                $count++;
            } elseif ($eval !== null) {
                $finalTotal += $eval;
                $count++;
            }
        }

        $this->total_auto_score  = $count > 0 ? round($autoTotal, 2)  : null;
        $this->total_evaluator_score = $count > 0 ? round($evalTotal, 2) : null;
        $this->final_score = $count > 0 ? round($finalTotal, 2) : null;
        $this->save();
    }

    /** Return interpretation label for a given total score. */
    public function getInterpretation(?float $score = null): array
    {
        $s = $score ?? (float) $this->final_score;

        if ($s >= 91) {
            return ['label' => 'Sobrepasa las expectativas', 'color' => 'green'];
        } elseif ($s >= 71) {
            return ['label' => 'Buen desempeño', 'color' => 'blue'];
        } elseif ($s >= 50) {
            return ['label' => 'Cumple las expectativas', 'color' => 'yellow'];
        }
        return ['label' => 'Requiere mejora', 'color' => 'red'];
    }

    /** Check if all criteria have an auto_score filled. */
    public function hasCompletedAutoEvaluation(): bool
    {
        return $this->responses()->whereNull('auto_score')->count() === 0
            && $this->responses()->count() > 0;
    }
}
