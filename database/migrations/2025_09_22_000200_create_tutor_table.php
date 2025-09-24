<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tutor')) {
            Schema::create('tutor', function (Blueprint $table) {
                $table->increments('id_tutor');
                $table->string('ced_tutor', 20)->unique();
                $table->string('nomb_tutor', 100);
                $table->string('apell_tutor', 100);
                $table->string('tel_tutor', 20);
                $table->string('correo_tutor', 100);
                $table->string('direc_tutor', 255);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor');
    }
};

