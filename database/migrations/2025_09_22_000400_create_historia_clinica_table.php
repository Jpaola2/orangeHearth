<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('historia_clinica')) {
            Schema::create('historia_clinica', function (Blueprint $table) {
                $table->increments('id_hc');
                $table->date('fech_crea_hc');
                $table->text('obse_gen_masc_hc');
                $table->text('antec_masc_hc');
                $table->unsignedInteger('id_masc');

                $table->foreign('id_masc')
                    ->references('id_masc')->on('mascota')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->index('id_masc', 'idx_hist_idmasc');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_clinica');
    }
};

