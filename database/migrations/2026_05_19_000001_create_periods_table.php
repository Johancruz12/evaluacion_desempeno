<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('label', 20)->unique(); // "2026-T1", "2026-S1", "2026"
            $table->string('type', 20);            // trimestral, semestral, anual
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Auto-seed periods for the current year using the configured default type
        $type = DB::table('settings')->where('key', 'default_period_type')->value('value') ?? 'trimestral';
        $year = (int) now()->year;

        $rows = match ($type) {
            'trimestral' => [
                ['label' => "{$year}-T1", 'sort_order' => 1],
                ['label' => "{$year}-T2", 'sort_order' => 2],
                ['label' => "{$year}-T3", 'sort_order' => 3],
                ['label' => "{$year}-T4", 'sort_order' => 4],
            ],
            'semestral' => [
                ['label' => "{$year}-S1", 'sort_order' => 1],
                ['label' => "{$year}-S2", 'sort_order' => 2],
            ],
            'anual' => [
                ['label' => (string) $year, 'sort_order' => 1],
            ],
            default => [],
        };

        foreach ($rows as $row) {
            DB::table('periods')->insert([
                'label'      => $row['label'],
                'type'       => $type,
                'year'       => $year,
                'sort_order' => $row['sort_order'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
