<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reopen fields on evaluations
        Schema::table('evaluations', function (Blueprint $table) {
            $table->text('reopen_reason')->nullable()->after('obs_responsabilidades');
            $table->date('reopen_deadline')->nullable()->after('reopen_reason');
            $table->timestamp('reopened_at')->nullable()->after('reopen_deadline');
            $table->foreignId('reopened_by')->nullable()->after('reopened_at')->constrained('users')->nullOnDelete();
        });

        // Notifications table
        Schema::create('evaluation_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluation_id')->nullable()->constrained('evaluations')->cascadeOnDelete();
            $table->string('type', 50); // evaluation_created, deadline_approaching, employee_completed, boss_review, rh_closed, evaluation_reopened
            $table->string('title');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_notifications');

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropForeign(['reopened_by']);
            $table->dropColumn(['reopen_reason', 'reopen_deadline', 'reopened_at', 'reopened_by']);
        });
    }
};
