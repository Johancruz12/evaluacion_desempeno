<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->integer('salomon_codigo')->nullable()->unique()->after('employee_code');
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->integer('salomon_codigo')->nullable()->unique()->after('is_active');
        });

        Schema::table('position_types', function (Blueprint $table) {
            $table->integer('salomon_codigo')->nullable()->unique()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'salomon_codigo']);
        });
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('salomon_codigo');
        });
        Schema::table('position_types', function (Blueprint $table) {
            $table->dropColumn('salomon_codigo');
        });
    }
};
