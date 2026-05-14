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

    /**
     * Calcula los totales de la evaluación en escala 0-100.
     *
     * Cada criterio se califica de 1 a 5. El puntaje total es el promedio
     * de los puntajes por criterio convertido a porcentaje:
     *   total = (sum(scores) / (count * 5)) * 100
     *
     * - `total_auto_score`: porcentaje de autoevaluación (empleado).
     * - `total_evaluator_score`: porcentaje calificado por el jefe.
     * - `final_score`: promedio ponderado entre auto y evaluador
     *    (50/50 si ambos existen, o el que esté disponible).
     */
    public function calculateScores(): void
    {
        $responses = $this->responses()->get();

        $autoSum = 0; $autoCount = 0;
        $evalSum = 0; $evalCount = 0;

        foreach ($responses as $r) {
            if ($r->auto_score !== null) {
                $autoSum += (float) $r->auto_score;
                $autoCount++;
            }
            if ($r->evaluator_score !== null) {
                $evalSum += (float) $r->evaluator_score;
                $evalCount++;
            }
        }

        // Convertir a porcentaje 0-100 (score / max=5 * 100)
        $autoPct = $autoCount > 0 ? ($autoSum / ($autoCount * 5)) * 100 : null;
        $evalPct = $evalCount > 0 ? ($evalSum / ($evalCount * 5)) * 100 : null;

        // Final = promedio entre los dos cuando ambos existen
        if ($autoPct !== null && $evalPct !== null) {
            $finalPct = ($autoPct + $evalPct) / 2;
        } else {
            $finalPct = $autoPct ?? $evalPct;
        }

        $this->total_auto_score      = $autoPct !== null ? round($autoPct, 2) : null;
        $this->total_evaluator_score = $evalPct !== null ? round($evalPct, 2) : null;
        $this->final_score           = $finalPct !== null ? round($finalPct, 2) : null;
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

    /**
     * Garantiza que existan filas en `evaluation_responses` para cada criterio
     * activo del template. Necesario porque la migración tiene
     * `cascade on delete` desde criterios; si se edita la plantilla, las
     * respuestas previas se pierden y la evaluación queda sin filas.
     * Idempotente.
     */
    public function ensureResponses(): int
    {
        $this->loadMissing('template.sections.criteria');
        if (!$this->template) {
            return 0;
        }

        $created = 0;
        foreach ($this->template->sections as $section) {
            if (!$section->is_active) {
                continue;
            }
            foreach ($section->criteria as $criteria) {
                if (!$criteria->is_active) {
                    continue;
                }
                $resp = EvaluationResponse::firstOrCreate([
                    'evaluation_id' => $this->id,
                    'criteria_id'   => $criteria->id,
                ]);
                if ($resp->wasRecentlyCreated) {
                    $created++;
                }
            }
        }
        return $created;
    }
}
