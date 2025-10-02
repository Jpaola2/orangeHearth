<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminNewPasswordController extends Controller
{
    // GET /admin/reset-password/{token}
    public function create(string $token)
    {
        return view('admin.auth.reset-password', ['token' => $token]);
    }

    // POST /admin/reset-password
    public function store(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required','email'],
            'password' => ['required','confirmed','min:8'],
        ]);

        // Bloquear cambio si el email no es de admin
        $isAdmin = User::where('email', $request->email)->where('role', 'admin')->exists();
        if (! $isAdmin) {
            return back()->withErrors(['email' => __('Este correo no pertenece a un administrador.')]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Por si cambiaron el rol después, solo permitir si sigue siendo admin
                if ($user->role !== 'admin') {
                    abort(403, 'No autorizado para este flujo.');
                }
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status)) // o route('admin.login') si tienes login propio
            : back()->withErrors(['email' => [__($status)]]);
    }
}
