<?php

namespace App\Mail;

use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AutoEvaluationCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Evaluation $evaluation,
        public User $jefe
    ) {}

    public function build(): static
    {
        $employeeName  = $this->evaluation->employee?->name ?? 'El empleado';
        $templateName  = $this->evaluation->template?->name ?? 'la evaluación';
        $evaluationUrl = route('evaluations.show', $this->evaluation);

        return $this
            ->subject("✅ {$employeeName} completó su autoevaluación — Acción requerida")
            ->view('emails.auto-evaluation-completed', [
                'jefeName'     => $this->jefe->name,
                'employeeName' => $employeeName,
                'templateName' => $templateName,
                'period'       => $this->evaluation->period,
                'evaluationUrl'=> $evaluationUrl,
            ]);
    }
}
