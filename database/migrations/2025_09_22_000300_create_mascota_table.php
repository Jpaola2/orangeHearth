<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mascota')) {
            Schema::create('mascota', function (Blueprint $table) {
                $table->increments('id_masc');
                $table->string('nom_masc', 100);
                $table->string('espe_masc', 50);
                $table->string('gene_masc', 10);
                $table->unsignedInteger('id_tutor');

                $table->foreign('id_tutor')
                    ->references('id_tutor')->on('tutor')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->index('id_tutor', 'idx_mascota_idtutor');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mascota');
    }
};

