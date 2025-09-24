<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar clínica por defecto
        DB::table('clinica_veterinaria')->updateOrInsert(
            ['rut_vet' => 'V.1546465'],
            ['nombre' => 'Clínica Cat']
        );
        $clinicaId = (int) DB::table('clinica_veterinaria')->where('rut_vet', 'V.1546465')->value('id_clinica');

        // Crear usuario admin
        $email = 'Armando.mendoza@email.com';
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Armando Mendoza',
                'password' => Hash::make('Bettymibetty123'),
                'role' => 'admin',
            ]
        );

        // Crear registro en administrador
        DB::table('administrador')->updateOrInsert(
            ['email' => $email],
            [
                'nom_adm' => 'Armando',
                'apell_adm' => 'Mendoza',
                'password' => Hash::make('Bettymibetty123'),
                'id_clinica' => $clinicaId,
                'user_id' => $user->id,
            ]
        );
    }
}

