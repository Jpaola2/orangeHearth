<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('medico_veterinario')) {
            return;
        }

        $hasUserId = Schema::hasColumn('medico_veterinario', 'user_id');

        Schema::table('medico_veterinario', function (Blueprint $table) use ($hasUserId) {
            if (!Schema::hasColumn('medico_veterinario', 'especialidad')) {
                $column = $table->string('especialidad', 120)->default('Medicina General');
                if ($hasUserId) {
                    $column->after('user_id');
                }
            }

            if (!Schema::hasColumn('medico_veterinario', 'telefono')) {
                $column = $table->string('telefono', 30)->nullable();
                if (Schema::hasColumn('medico_veterinario', 'especialidad')) {
                    $column->after('especialidad');
                }
            }

            if (!Schema::hasColumn('medico_veterinario', 'estado')) {
                $column = $table->enum('estado', ['activo', 'inactivo'])->default('activo');
                if (Schema::hasColumn('medico_veterinario', 'telefono')) {
                    $column->after('telefono');
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('medico_veterinario')) {
            return;
        }

        Schema::table('medico_veterinario', function (Blueprint $table) {
            foreach (['estado', 'telefono', 'especialidad'] as $column) {
                if (Schema::hasColumn('medico_veterinario', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};