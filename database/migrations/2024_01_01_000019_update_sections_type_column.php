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
        // Laravel's enum() on PostgreSQL uses a CHECK constraint, not a native enum type.
        DB::statement('ALTER TABLE evaluation_sections DROP CONSTRAINT IF EXISTS evaluation_sections_type_check');
        DB::statement('ALTER TABLE evaluation_sections ALTER COLUMN type TYPE VARCHAR(255)');
        DB::statement("ALTER TABLE evaluation_sections ADD CONSTRAINT evaluation_sections_type_check CHECK (type IN ('competencias', 'responsabilidades', 'rango'))");
    }
};
