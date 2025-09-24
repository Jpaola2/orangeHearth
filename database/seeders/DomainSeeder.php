<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Clínica
        DB::table('clinica_veterinaria')->updateOrInsert(
            ['rut_vet' => 'V.1546465'],
            ['nombre' => 'Clínica Cat']
        );
        $clinicaId = (int) DB::table('clinica_veterinaria')->where('rut_vet', 'V.1546465')->value('id_clinica');

        // 2) Tutores
        $tutores = [
            ['ced' => '1233456789', 'nomb' => 'Andres',  'apell' => 'Correa',  'tel' => '333 333 333', 'correo' => 'andres@gmail.com',   'dir' => 'carrera 2 p 74', 'pwd' => 'tutor1.'],
            ['ced' => '147258369',  'nomb' => 'Fernanda','apell' => 'Zuleta',  'tel' => '444 444 444', 'correo' => 'Fenanda@gmail.com', 'dir' => 'carrera 56 a 89 autopista sur', 'pwd' => 'tutor2.'],
            ['ced' => '804693528',  'nomb' => 'Sandra',  'apell' => 'Torres',  'tel' => '555 555 555', 'correo' => 'Sandrita@gmail.com','dir' => 'Avenida 19 - calle 72', 'pwd' => 'tutor3.'],
        ];
        foreach ($tutores as $t) {
            DB::table('tutor')->updateOrInsert(
                ['ced_tutor' => $t['ced']],
                [
                    'nomb_tutor'  => $t['nomb'],
                    'apell_tutor' => $t['apell'],
                    'tel_tutor'   => $t['tel'],
                    'correo_tutor'=> $t['correo'],
                    'direc_tutor' => $t['dir'],
                    'password'    => Hash::make($t['pwd']),
                ]
            );

            // Crear/actualizar usuario de login y vincularlo
            $user = User::updateOrCreate(
                ['email' => $t['correo']],
                [
                    'name' => $t['nomb'].' '.$t['apell'],
                    'password' => Hash::make($t['pwd']),
                    'role' => 'tutor',
                ]
            );

            DB::table('tutor')->where('ced_tutor', $t['ced'])->update(['user_id' => $user->id]);
        }

        // 3) Médicos
        $medicos = [
            ['cedu' => '1233456789', 'nombre' => 'Paola', 'apell' => 'Bríñez', 'tp' => 'TP58669'],
            ['cedu' => '147852369',  'nombre' => 'Laura', 'apell' => 'Cardozo','tp' => 'TP45632'],
        ];
        foreach ($medicos as $m) {
            DB::table('medico_veterinario')->updateOrInsert(
                ['cedu_mv' => $m['cedu']],
                [
                    'nombre_mv' => $m['nombre'],
                    'apell_mv'  => $m['apell'],
                    'tarjeta_profesional_mv' => $m['tp'],
                ]
            );
        }

        // Mapas por clave única para IDs
        $tutorIdByCed = fn(string $ced) => (int) DB::table('tutor')->where('ced_tutor', $ced)->value('id_tutor');
        $medicoIdByCed = fn(string $ced) => (int) DB::table('medico_veterinario')->where('cedu_mv', $ced)->value('id_mv');

        // 5) Mascotas
        $mascotas = [
            ['nom' => 'Angel',  'esp' => 'felino', 'gen' => 'macho',  'ced_tutor' => '1233456789'],
            ['nom' => 'Morita', 'esp' => 'felino', 'gen' => 'hembra', 'ced_tutor' => '147258369'],
            ['nom' => 'Emily',  'esp' => 'felino', 'gen' => 'hembra', 'ced_tutor' => '804693528'],
            ['nom' => 'Motas',  'esp' => 'canino', 'gen' => 'macho',  'ced_tutor' => '1233456789'],
        ];
        foreach ($mascotas as $m) {
            $idTutor = $tutorIdByCed($m['ced_tutor']);
            if (!$idTutor) continue;
            DB::table('mascota')->updateOrInsert(
                ['nom_masc' => $m['nom'], 'id_tutor' => $idTutor],
                ['espe_masc' => $m['esp'], 'gene_masc' => $m['gen']]
            );
        }

        $mascotaId = fn(string $nom, int $idTutor) => (int) DB::table('mascota')
            ->where(['nom_masc' => $nom, 'id_tutor' => $idTutor])
            ->value('id_masc');

        // 6) Historias clínicas
        $historias = [
            ['nom' => 'Angel',  'ced_tutor' => '1233456789', 'fech' => '2022-04-10', 'obse' => 'Decaimiento con presencia de vómitos y diarrea desde hace cuatro días', 'antec' => 'Cirugía por objeto extraño en el sistema digestivo'],
            ['nom' => 'Morita', 'ced_tutor' => '147258369',  'fech' => '2023-09-27', 'obse' => 'Convulsión', 'antec' => 'Golpes craneales'],
            ['nom' => 'Emily',  'ced_tutor' => '804693528',  'fech' => '2020-04-25', 'obse' => 'Alergia dermatológica', 'antec' => 'Ninguno'],
            ['nom' => 'Motas',  'ced_tutor' => '1233456789', 'fech' => '2024-06-04', 'obse' => 'Vómito sanguinolento', 'antec' => 'Ninguno'],
        ];
        foreach ($historias as $h) {
            $idTutor = $tutorIdByCed($h['ced_tutor']);
            $idMasc = $mascotaId($h['nom'], $idTutor);
            if (!$idMasc) continue;
            DB::table('historia_clinica')->updateOrInsert(
                ['id_masc' => $idMasc],
                ['fech_crea_hc' => $h['fech'], 'obse_gen_masc_hc' => $h['obse'], 'antec_masc_hc' => $h['antec']]
            );
        }

        // 7) Citas
        $citas = [
            ['fech' => '2025-05-14', 'motiv' => 'Cita general', 'diag' => 'Infección parasitaria', 'trata' => 'Antiparasitario', 'ced_tutor' => '1233456789', 'ced_mv' => '1233456789', 'masc' => 'Angel'],
            ['fech' => '2025-05-22', 'motiv' => 'Vacunación',    'diag' => 'Nefropatía',           'trata' => 'Hemodiálisis renal', 'ced_tutor' => '147258369',  'ced_mv' => '147852369',  'masc' => 'Morita'],
            ['fech' => '2025-05-17', 'motiv' => 'Cirugía',       'diag' => 'OVH',                  'trata' => 'Reposo y analgésicos', 'ced_tutor' => '804693528',  'ced_mv' => '1233456789', 'masc' => 'Emily'],
            ['fech' => '2025-05-05', 'motiv' => 'Cita general',  'diag' => 'Orquiectomía',         'trata' => 'Reposo y analgésicos', 'ced_tutor' => '1233456789', 'ced_mv' => '147852369', 'masc' => 'Motas'],
        ];
        foreach ($citas as $c) {
            $idTutor = $tutorIdByCed($c['ced_tutor']);
            $idMv = $medicoIdByCed($c['ced_mv']);
            $idMasc = $mascotaId($c['masc'], $idTutor);
            if (!$idTutor || !$idMv || !$idMasc) continue;
            DB::table('cita_medica')->updateOrInsert(
                ['id_tutor' => $idTutor, 'id_mv' => $idMv, 'id_masc' => $idMasc, 'fech_cons' => $c['fech']],
                ['motiv_cons' => $c['motiv'], 'diag_cons' => $c['diag'], 'trata_cons' => $c['trata']]
            );
        }
    }
}
