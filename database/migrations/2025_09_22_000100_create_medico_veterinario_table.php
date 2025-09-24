<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('medico_veterinario')) {
            Schema::create('medico_veterinario', function (Blueprint $table) {
                $table->increments('id_mv');
                $table->string('nombre_mv', 100);
                $table->string('apell_mv', 100);
                $table->string('cedu_mv', 20)->unique();
                $table->string('tarjeta_profesional_mv', 30)->unique();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_veterinario');
    }
};

