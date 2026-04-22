<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('person_id')->nullable()->after('id')->constrained('persons')->onDelete('set null');
            $table->foreignId('area_id')->nullable()->after('person_id')->constrained('areas')->onDelete('set null');
            $table->foreignId('position_type_id')->nullable()->after('area_id')->constrained('position_types')->onDelete('set null');
            $table->string('employee_code')->nullable()->after('login');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropForeign(['area_id']);
            $table->dropForeign(['position_type_id']);
            $table->dropColumn(['person_id', 'area_id', 'position_type_id', 'employee_code']);
        });
    }
};
