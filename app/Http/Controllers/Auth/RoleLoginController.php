<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Medico;

class RoleLoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
            'role'     => ['required','in:admin,vet,tutor'],
        ]);

        // 1) Autenticar por email + password
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenciales inválidas'])->onlyInput('email');
        }

        // 2) Regenerar sesión
        $request->session()->regenerate();

        // 3) Validaciones adicionales por rol (veterinario)
        if ($data['role'] === 'vet') {
            // La cuenta debe ser de veterinario
            if (auth()->user()->role !== 'vet') {
                Auth::logout();
                return back()->withErrors(['email' => 'Esta cuenta no corresponde a un veterinario.'])->onlyInput('email');
            }

            // Validar tarjeta sin exigir un formato específico
            $request->validate([
                'tarjeta_profesional' => ['required','string','max:50'],
            ], [
                'tarjeta_profesional.required' => 'La tarjeta profesional es obligatoria',
            ]);

            $input = strtoupper(trim((string) $request->input('tarjeta_profesional')));

            // Debe existir una ficha Medico asociada a este usuario con esa tarjeta
            $match = Medico::where('user_id', auth()->id())
                ->whereRaw('UPPER(TRIM(tarjeta_profesional_mv)) = ?', [$input])
                ->exists();

            if (!$match) {
                Auth::logout();
                return back()->withErrors([
                    'tarjeta_profesional' => 'La tarjeta profesional no coincide con su cuenta.',
                ])->onlyInput('email');
            }
        }

        // 4) Redirigir según el rol
        return match ($data['role']) {
            'admin' => to_route('admin.dashboard'),
            'vet'   => to_route('vet.dashboard'),
            'tutor' => to_route('tutor.dashboard'),
        };
    }

    public function logout(Request $request)
    {
        $role = optional($request->user())->role;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return match ($role) {
            'admin' => to_route('login.admin'),
            'vet'   => to_route('login.veterinario'),
            default => to_route('login.tutor'),
        };
    }
}

