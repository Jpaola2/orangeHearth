{{-- Vista legacy. Redirige a los formularios correctos (Tutor/Admin). --}}
@extends('layouts.auth')

@section('content')
  <div class="login-container" style="text-align:center;max-width:640px;margin:40px auto;">
    <h1 style="color:#d86f00;margin-bottom:8px;">
      <i class="fas fa-unlock-alt"></i>
      Recuperar contraseña
    </h1>
    <p style="color:#555;margin:0 0 24px;">Selecciona a qué formulario quieres ir:</p>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a class="btn" href="{{ route('password.request') }}" style="padding:10px 14px;border:1px solid #f28c28;border-radius:10px;color:#d86f00;text-decoration:none;">Tutor</a>
      <a class="btn" href="{{ route('admin.password.request') }}" style="padding:10px 14px;border:1px solid #f28c28;border-radius:10px;color:#d86f00;text-decoration:none;">Administrador</a>
    </div>

    <p style="margin-top:24px;"><a href="{{ url('/') }}">↩ Volver al inicio</a></p>
  </div>
@endsection
