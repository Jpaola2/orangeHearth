@extends('layouts.auth')

@section('title', 'Recuperar contraseña — Tutor')

@section('content')
  <a href="{{ route('login.tutor') }}" class="back-link">
    <i class="fas fa-arrow-left"></i>
    Volver al inicio de sesión
  </a>

  <div class="login-container">
    <div class="logo-section">
      <h1>
        <i class="fas fa-unlock"></i>
        OrangeHearth — Tutor
      </h1>
      <p>Recuperar contraseña</p>
      <div class="veterinary-badge">
        <i class="fas fa-paw"></i>
        Soporte de Acceso
      </div>
    </div>

    @if (session('status'))
      <div class="professional-info" role="status">{{ session('status') }}</div>
    @endif

    <div class="professional-info">
      <h4><i class="fas fa-info-circle"></i> ¿Cómo funciona?</h4>
      <p>Ingresa tu correo y una nueva contraseña. Se actualizará de inmediato y volverás a iniciar sesión.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="form-group">
        <label for="email"><i class="fas fa-envelope"></i> Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        @error('email') <div class="error-message visible">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Nueva contraseña</label>
        <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
        @error('password') <div class="error-message visible">{{ $message }}</div> @enderror
      </div>

      <div class="form-group">
        <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6" autocomplete="new-password">
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-paper-plane"></i>
        Guardar nueva contraseña
      </button>
    </form>
  </div>
@endsection

