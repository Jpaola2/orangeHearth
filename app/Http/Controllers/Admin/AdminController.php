<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // Mostrar formulario de registro (invitado)
    public function create()
    {
        return view('admin.register');
    }

    // Guardar nuevo admin
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email', 'unique:admins,email'],
            'telefono'        => ['nullable', 'string', 'max:50'],
            'cedula'          => ['required', 'string', 'max:30', 'unique:admins,cedula'],
            'empresa_nombre'  => ['required', 'string', 'max:255'],
            'nit'             => ['required', 'string', 'max:30'],
            'password'        => ['required', 'string', 'min:8'],
        ]);

        // Usuario de acceso
        $user = User::create([
            'name'     => $data['nombre_completo'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'admin',
        ]);

        // Ficha administrativa
        $admin = Admin::create([
            'user_id'        => $user->id,
            'nombre_completo'=> $data['nombre_completo'],
            'email'          => $data['email'],
            'telefono'       => $data['telefono'] ?? null,
            'cedula'         => $data['cedula'],
            'empresa_nombre' => $data['empresa_nombre'],
            'nit'            => strtoupper($data['nit']),
            // (Evita guardar password en tabla admins; se guarda en users)
        ]);

        auth()->login($user);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Registro exitoso. Bienvenido al panel administrativo.');
    }

    // Editar perfil (solo el propio)
    public function edit(Request $request)
    {
        /** @var User $auth */
        $auth = $request->user();

        // Buscar la ficha Admin por user_id preferiblemente
        $admin = Admin::where('user_id', $auth->id)->first()
              ?? Admin::where('email', $auth->email)->firstOrFail();

        return view('admin.profile.edit', compact('admin'));
    }

    // Actualizar perfil (solo campos permitidos y solo el propio)
    public function update(Request $request)
    {
        /** @var User $auth */
        $auth = $request->user();

        // Ficha admin asociada
        $admin = Admin::where('user_id', $auth->id)->first()
              ?? Admin::where('email', $auth->email)->firstOrFail();

        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'email'           => [
                'required', 'email:rfc,dns', 'max:255',
                Rule::unique('users', 'email')->ignore($auth->id),
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
            'telefono'        => ['nullable', 'string', 'max:50'],
            'cedula'          => [ 'required', 'string', 'max:30', Rule::unique('admins', 'cedula')->ignore($admin->id) ],
            'empresa_nombre'  => ['required', 'string', 'max:255'],
            'nit'             => ['required', 'string', 'max:30'],
            'password'        => ['nullable', 'string', 'min:8'],
        ]);

        // Actualizar USER
        $auth->name  = $data['nombre_completo'];
        $auth->email = $data['email'];
        if (!empty($data['password'])) {
            $auth->password = Hash::make($data['password']);
        }
        $auth->save();

        // Actualizar ADMIN
        $admin->update([
            'nombre_completo' => $data['nombre_completo'],
            'email'           => $data['email'],
            'telefono'        => $data['telefono'] ?? null,
            'cedula'          => $data['cedula'],
            'empresa_nombre'  => $data['empresa_nombre'],
            'nit'             => strtoupper($data['nit']),
        ]);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }
}
