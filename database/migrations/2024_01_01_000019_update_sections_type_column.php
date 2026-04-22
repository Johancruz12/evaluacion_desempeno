<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the auto-generated CHECK constraint created by Laravel's ->enum() for PostgreSQL
        DB::statement('ALTER TABLE evaluation_sections DROP CONSTRAINT IF EXISTS evaluation_sections_type_check');
        // Change type column from enum to varchar to support more section types
        DB::statement('ALTER TABLE evaluation_sections ALTER COLUMN type TYPE VARCHAR(50)');
    }

    public function down(): void
    {
        // Revert, only safe if no extra values exist
        DB::statement("ALTER TABLE evaluation_sections ALTER COLUMN type TYPE evaluation_sections_type_enum USING type::evaluation_sections_type_enum");
    }
};
