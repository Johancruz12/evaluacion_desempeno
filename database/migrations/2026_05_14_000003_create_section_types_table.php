<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('section_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('label');
            $table->string('icon', 8)->default('📋');         // emoji
            $table->string('gradient', 100)->default('from-blue-500 to-sky-500');
            $table->string('badge_class', 100)->default('bg-blue-100 text-blue-700');
            $table->string('border_class', 60)->default('border-blue-200');
            $table->string('bg_class', 60)->default('bg-blue-50');
            $table->string('behavior', 20)->default('calificable'); // calificable | rango | texto
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        // Seed the existing built-in types
        $now = now();
        DB::table('section_types')->insert([
            [
                'slug' => 'competencias_org', 'label' => 'Competencias Organizacionales',
                'icon' => '🧠', 'gradient' => 'from-blue-500 to-blue-600',
                'badge_class' => 'bg-blue-100 text-blue-700', 'border_class' => 'border-blue-200', 'bg_class' => 'bg-blue-50',
                'behavior' => 'calificable', 'is_system' => true, 'is_active' => true, 'order' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'competencias_cargo', 'label' => 'Competencias del Cargo',
                'icon' => '💼', 'gradient' => 'from-sky-500 to-sky-600',
                'badge_class' => 'bg-sky-100 text-sky-700', 'border_class' => 'border-sky-200', 'bg_class' => 'bg-sky-50',
                'behavior' => 'calificable', 'is_system' => true, 'is_active' => true, 'order' => 2,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'responsabilidades', 'label' => 'Responsabilidades',
                'icon' => '✅', 'gradient' => 'from-amber-500 to-amber-600',
                'badge_class' => 'bg-amber-100 text-amber-700', 'border_class' => 'border-amber-200', 'bg_class' => 'bg-amber-50',
                'behavior' => 'calificable', 'is_system' => true, 'is_active' => true, 'order' => 3,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'rango', 'label' => 'Tabla de Rangos',
                'icon' => '📊', 'gradient' => 'from-slate-500 to-slate-600',
                'badge_class' => 'bg-slate-100 text-slate-700', 'border_class' => 'border-slate-200', 'bg_class' => 'bg-slate-50',
                'behavior' => 'rango', 'is_system' => true, 'is_active' => true, 'order' => 4,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'slug' => 'texto', 'label' => 'Sección informativa (solo texto)',
                'icon' => '📝', 'gradient' => 'from-violet-500 to-purple-500',
                'badge_class' => 'bg-violet-100 text-violet-700', 'border_class' => 'border-violet-200', 'bg_class' => 'bg-violet-50',
                'behavior' => 'texto', 'is_system' => true, 'is_active' => true, 'order' => 5,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('section_types');
    }
};
