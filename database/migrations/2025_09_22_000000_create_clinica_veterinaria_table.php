<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinica_veterinaria')) {
            Schema::create('clinica_veterinaria', function (Blueprint $table) {
                $table->increments('id_clinica');
                $table->string('nombre', 100);
                $table->string('rut_vet', 20)->unique();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinica_veterinaria');
    }
};

