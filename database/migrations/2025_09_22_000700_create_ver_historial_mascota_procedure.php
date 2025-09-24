<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear procedimiento almacenado
        DB::unprepared('DROP PROCEDURE IF EXISTS ver_historial_mascota');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE ver_historial_mascota(IN p_id_masc INT)
BEGIN
    SELECT h.id_hc, h.fech_crea_hc, h.obse_gen_masc_hc, h.antec_masc_hc, m.nom_masc
    FROM historia_clinica h
    JOIN mascota m ON h.id_masc = m.id_masc
    WHERE m.id_masc = p_id_masc;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS ver_historial_mascota');
    }
};

