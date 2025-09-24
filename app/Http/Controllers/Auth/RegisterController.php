<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showTutorForm()
    {
        return view('auth.tutor-register');
    }

    public function registerTutor(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required','string','min:3','max:200'],
            'tipo_id'     => ['required','string'],
            'numero_id'   => ['required','string','max:20','unique:tutor,ced_tutor'],
            'correo'      => ['required','email','max:150','unique:users,email'],
            'password'    => ['required','string','min:8'],
            'telefono'    => ['required','string','max:20'],
            'direccion'   => ['required','string','max:255'],

            // Mascota
            'mascota'     => ['required','string','max:100'],
            'especie'     => ['required','string','max:50'],
            'edad'        => ['nullable','integer','min:1','max:365'],
            'unidad_edad' => ['nullable','string','in:dias,meses,años'],
            'raza'        => ['nullable','string','max:100'],
            'genero'      => ['required','in:macho,hembra'],
        ]);

        // Separar nombre y apellido en lo posible
        $parts = preg_split('/\s+/', trim($data['nombre']), 2);
        $nomb = $parts[0] ?? $data['nombre'];
        $apell = $parts[1] ?? '';

        $user = User::create([
            'name' => $data['nombre'],
            'email' => $data['correo'],
            'password' => Hash::make($data['password']),
            'role' => 'tutor',
        ]);

        $tutor = Tutor::create([
            'ced_tutor'    => $data['numero_id'],
            'nomb_tutor'   => $nomb,
            'apell_tutor'  => $apell,
            'tel_tutor'    => $data['telefono'],
            'correo_tutor' => $data['correo'],
            'direc_tutor'  => $data['direccion'],
            'password'     => Hash::make($data['password']),
            'user_id'      => $user->id,
        ]);

        // Normalizar especie a lo que usa el sistema
        $especie = match (mb_strtolower($data['especie'])) {
            'perro' => 'canino',
            'gato'  => 'felino',
            default => 'otro',
        };

        \App\Models\Mascota::create([
            'nom_masc'  => $data['mascota'],
            'espe_masc' => $especie,
            'gene_masc' => $data['genero'],
            'edad_masc' => $data['edad'] ?? null,
            'unidad_edad' => $data['unidad_edad'] ?? null,
            'id_tutor'  => $tutor->id_tutor,
        ]);

        return to_route('login.tutor')->with('status', 'Registro exitoso. Ahora puedes iniciar sesión.');
    }
}
