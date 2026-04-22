<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('evaluation_templates')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('period');
            $table->date('evaluation_date')->nullable();
            $table->enum('status', ['pendiente', 'en_progreso', 'completada', 'revisada'])->default('pendiente');
            $table->text('observations')->nullable();
            $table->decimal('total_auto_score', 8, 2)->nullable();
            $table->decimal('total_evaluator_score', 8, 2)->nullable();
            $table->decimal('final_score', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
