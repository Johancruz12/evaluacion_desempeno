<?php

namespace App\Console\Commands;

use App\Models\Evaluation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CloseExpiredEvaluations extends Command
{
    protected $signature   = 'evaluations:close-expired';
    protected $description = 'Cierra automáticamente las evaluaciones cuya fecha límite (due_date) ha vencido.';

    public function handle(): int
    {
        $today = Carbon::today();

        $expired = Evaluation::whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->whereNotIn('status', ['completada', 'cerrada'])
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No hay evaluaciones vencidas.');
            return self::SUCCESS;
        }

        foreach ($expired as $evaluation) {
            $evaluation->update(['status' => 'cerrada']);
        }

        $this->info("Se cerraron {$expired->count()} evaluación(es) vencida(s).");
        return self::SUCCESS;
    }
}
