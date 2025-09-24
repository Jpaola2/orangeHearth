<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('administrador')) {
            Schema::create('administrador', function (Blueprint $table) {
                $table->increments('cod_adm');
                $table->string('nom_adm', 100);
                $table->string('apell_adm', 100);
                $table->string('email', 150)->unique();
                $table->string('password', 255);
                $table->unsignedInteger('id_clinica');

                $table->foreign('id_clinica')
                    ->references('id_clinica')->on('clinica_veterinaria')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();

                $table->index('id_clinica', 'idx_admin_idclinica');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('administrador');
    }
};
