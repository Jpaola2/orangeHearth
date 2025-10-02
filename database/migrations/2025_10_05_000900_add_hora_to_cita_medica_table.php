<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cita_medica', function (Blueprint $table) {
            if (!Schema::hasColumn('cita_medica', 'hora_cons')) {
                $table->time('hora_cons')->nullable()->after('fech_cons');
            }
        });

        // Normalizar valores nulos a las 00:00:00 para evitar problemas con Carbon
        DB::table('cita_medica')
            ->whereNull('hora_cons')
            ->update(['hora_cons' => '00:00:00']);
    }

    public function down(): void
    {
        Schema::table('cita_medica', function (Blueprint $table) {
            if (Schema::hasColumn('cita_medica', 'hora_cons')) {
                $table->dropColumn('hora_cons');
            }
        });
    }
};
