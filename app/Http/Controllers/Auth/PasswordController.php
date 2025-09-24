<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email','exists:users,email'],
            'password' => ['required','string','min:6','confirmed'],
        ], [
            'email.exists' => 'No encontramos un usuario con ese correo',
        ]);

        User::where('email', $data['email'])->update([
            'password' => Hash::make($data['password']),
        ]);

        return to_route('login')->with('status', 'Contraseña actualizada. Ahora puedes iniciar sesión.');
    }
}
