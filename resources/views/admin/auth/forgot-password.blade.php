@extends('layouts.auth')

@section('title', 'Recuperar contraseña — Admin')

@section('content')
  <a href="{{ route('login.admin') }}" class="back-link">
    <i class="fas fa-arrow-left"></i>
    Volver al inicio de sesión
  </a>

  <div class="login-container">
    <div class="logo-section">
      <h1>
        <i class="fas fa-unlock-alt"></i>
        OrangeHearth — Admin
      </h1>
      <p>Recuperar contraseña</p>
      <div class="veterinary-badge">
        <i class="fas fa-life-ring"></i>
        Soporte de Acceso
      </div>
    </div>

    @if (session('status'))
      <div class="professional-info" role="status">{{ session('status') }}</div>
    @endif

    <div class="professional-info">
      <h4><i class="fas fa-info-circle"></i> ¿Cómo funciona?</h4>
      <p>Actualiza tu contraseña ingresando tu correo, NIT y la nueva contraseña. Validaremos que seas administrador.</p>
    </div>

    <form id="forgotForm" method="POST" action="{{ route('admin.password.email') }}" novalidate>
      @csrf

      <div class="form-group">
        <label for="email"><i class="fas fa-envelope"></i> Correo electrónico</label>
        <input type="email" id="email" name="email" inputmode="email" autocomplete="email" placeholder="admin@ejemplo.com" value="{{ old('email') }}" required>
        <div class="error-message @error('email') visible @enderror">@error('email'){{ $message }}@enderror</div>
      </div>

      <div class="form-group">
        <label for="nit"><i class="fas fa-id-card"></i> NIT</label>
        <input type="text" id="nit" name="nit" placeholder="900123456" value="{{ old('nit') }}" required>
        <div class="error-message @error('nit') visible @enderror">@error('nit'){{ $message }}@enderror</div>
      </div>

      <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Nueva contraseña</label>
        <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
        <div class="error-message @error('password') visible @enderror">@error('password'){{ $message }}@enderror</div>
      </div>

      <div class="form-group">
        <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6" autocomplete="new-password">
      </div>

      <button type="submit" class="login-btn" id="sendLinkBtn" aria-live="polite">
        <i class="fas fa-paper-plane"></i>
        Guardar nueva contraseña
      </button>
    </form>

    <div class="additional-options">
      <p><a href="{{ url('/') }}">↩ Regresar a la página principal</a></p>
    </div>
  </div>
@endsection
