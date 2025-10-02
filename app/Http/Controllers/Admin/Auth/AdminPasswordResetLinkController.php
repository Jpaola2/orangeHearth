<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPasswordResetLinkController extends Controller
{
    // GET /admin/forgot-password
    public function create()
    {
        return view('admin.auth.forgot-password');
    }

    // POST /admin/forgot-password (flujo simplificado como Tutor)
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'nit'      => ['required','string','max:50'],
            'password' => ['required','string','min:6','confirmed'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'nit.required'   => 'El NIT es obligatorio.',
        ]);

        // Verificar admin por email + NIT
        $admin = Admin::where('email', $data['email'])->where('nit', $data['nit'])->first();
        if (! $admin) {
            return back()->withErrors(['email' => 'No encontramos un administrador con ese correo y NIT.'])->withInput();
        }

        // Actualizar contraseña del usuario vinculado (o por email/rol)
        $user = $admin->user ?: User::where('email', $data['email'])->where('role', 'admin')->first();
        if (! $user) {
            return back()->withErrors(['email' => 'No se pudo localizar la cuenta de usuario asociada.'])->withInput();
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return to_route('login.admin')->with('status', 'Contraseña actualizada. Ingresa nuevamente.');
    }
}

