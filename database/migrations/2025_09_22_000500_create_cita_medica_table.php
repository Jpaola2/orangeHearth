<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cita_medica')) {
            Schema::create('cita_medica', function (Blueprint $table) {
                $table->increments('id_cita_medi');
                $table->date('fech_cons');
                $table->text('motiv_cons');
                $table->text('diag_cons');
                $table->text('trata_cons');

                $table->unsignedInteger('id_tutor');
                $table->unsignedInteger('id_mv');
                $table->unsignedInteger('id_masc');

                $table->foreign('id_tutor')
                    ->references('id_tutor')->on('tutor')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('id_mv')
                    ->references('id_mv')->on('medico_veterinario')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('id_masc')
                    ->references('id_masc')->on('mascota')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->index('id_tutor', 'idx_cita_idtutor');
                $table->index('id_mv', 'idx_cita_idmv');
                $table->index('id_masc', 'idx_cita_idmasc');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_medica');
    }
};

