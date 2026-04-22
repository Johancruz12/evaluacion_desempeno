<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->string('period_type', 20)->nullable()->after('period'); // trimestral, semestral, anual
            $table->date('admission_date')->nullable()->after('period_type');
            $table->text('obs_organizacional')->nullable()->after('observations');
            $table->text('obs_cargo')->nullable()->after('obs_organizacional');
            $table->text('obs_responsabilidades')->nullable()->after('obs_cargo');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn(['period_type', 'admission_date', 'obs_organizacional', 'obs_cargo', 'obs_responsabilidades']);
        });
    }
};
