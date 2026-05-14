<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evaluation_sections', function (Blueprint $table) {
            // Stores an array of description paragraphs for text-only sections
            $table->json('content')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_sections', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
