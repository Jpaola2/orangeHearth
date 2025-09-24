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
            return back()->withErrors(['email' => 'Credenciales invÃ¡lidas'])->onlyInput('email');
        }

        // 2) Regenerar sesiÃ³n
        $request->session()->regenerate();

        // 3) Validaciones adicionales por rol
        if ($data['role'] === 'vet') {
            $request->validate([
                'tarjeta_profesional' => ['required','regex:/^[A-Z]{2}[0-9]{4,8}$/'],
            ], [
                'tarjeta_profesional.required' => 'La tarjeta profesional es obligatoria',
                'tarjeta_profesional.regex' => 'Formato de tarjeta no vÃ¡lido (ej: TP123456)',
            ]);

            $tp = strtoupper((string) $request->input('tarjeta_profesional'));
            $existe = Medico::where('tarjeta_profesional_mv', $tp)->exists();
            if (!$existe) {
                Auth::logout();
                return back()->withErrors(['tarjeta_profesional' => 'Tarjeta profesional no vÃ¡lida'])->onlyInput('email');
            }
        }

        // 4) Redirigir segÃºn el rol
        return match ($data['role']) {
            'admin' => to_route('admin.dashboard'),
            'vet'   => to_route('vet.dashboard'),
            'tutor' => to_route('tutor.dashboard'),
        };
    }

    public function logout(Request $request)
    {
        $role = optional($request->user())->role;
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return match ($role) {
            'admin' => to_route('login.admin'),
            'vet'   => to_route('login.veterinario'),
            default => to_route('login.tutor'),
        };
    }
}