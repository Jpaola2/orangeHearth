<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Muestra el mockup / formulario de "¿Olvidaste tu contraseña?"
     */
    public function showForgotForm()
    {
        // Vista de recuperación para Tutores
        return view('tutor.auth.forgot-password');
    }

    /**
     * Cambia la contraseña directamente (flujo simplificado) y redirige
     * a la pantalla de login adecuada según el rol del usuario.
     */
    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email','exists:users,email'],
            'password' => ['required','string','min:6','confirmed'],
        ], [
            'email.exists' => 'No encontramos un usuario con ese correo.',
        ]);

        // Buscamos el usuario y actualizamos su contraseña
        $user = User::where('email', $data['email'])->firstOrFail();

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        // Decide a dónde redirigir según el rol del usuario
        $role = $user->role ?? 'tutor';

        // --- Opción A: redirigir a la PANTALLA DE LOGIN por rol ---
        // (recomendado con este flujo, para que el usuario ingrese de nuevo)
        if ($role === 'admin') {
            return to_route('login.admin')
                ->with('status', 'Contraseña actualizada. Ingresa de nuevo como administrador.');
        }

        if ($role === 'vet') {
            return to_route('login.veterinario')
                ->with('status', 'Contraseña actualizada. Ingresa de nuevo como veterinario.');
        }

        // Por defecto: tutor
        return to_route('login.tutor')
            ->with('status', 'Contraseña actualizada. Ingresa de nuevo.');
        

        // --- Si prefieres ir DIRECTO AL DASHBOARD por rol (sin pasar por login),
        //     descomenta este bloque y comenta el bloque de arriba. Asegúrate de
        //     autenticar al usuario aquí si lo necesitas. ---
        //
        // if ($role === 'admin') {
        //     return to_route('admin.profile.edit')->with('status', 'Contraseña actualizada.');
        // }
        // if ($role === 'vet') {
        //     return to_route('vet.dashboard')->with('status', 'Contraseña actualizada.');
        // }
        // return to_route('tutor.dashboard')->with('status', 'Contraseña actualizada.');
    }
}
