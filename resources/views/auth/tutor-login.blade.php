@extends('layouts.auth')

@section('content')
  <a href="{{ url('/') }}" class="back-link">
    <i class="fas fa-arrow-left"></i>
    Volver al Inicio
  </a>

  <div class="login-container">
    <div class="logo-section">
      <h1>
        <i class="fas fa-user"></i>
        OrangeHearth
      </h1>
      <p>Portal del Tutor</p>
      <div class="veterinary-badge">
        <i class="fas fa-paw"></i>
        Acceso de Tutor
      </div>
    </div>

    <div class="professional-info">
      <h4><i class="fas fa-info-circle"></i> Información</h4>
      <p>Ingresa con tu correo y contraseña registrados para gestionar tus citas y mascotas.</p>
    </div>

    <form id="loginForm" method="POST" action="{{ route('login.perform') }}">
      @csrf
      <input type="hidden" name="role" value="tutor">

      <div class="form-group">
        <label for="email">
          <i class="fas fa-envelope"></i>
          Correo Electrónico
        </label>
        <input type="email" id="email" name="email" placeholder="tutor@orangehearth.com" value="{{ old('email') }}" required>
        <div class="error-message" id="emailError">
          @error('email'){{ $message }}@enderror
          @if(session('error')){{ session('error') }}@endif
        </div>
      </div>

      <div class="form-group">
        <label for="password">
          <i class="fas fa-lock"></i>
          Contraseña
        </label>
        <div class="password-toggle">
          <input type="password" id="password" name="password" placeholder="Tu contraseña" required>
          <i class="fas fa-eye" id="togglePassword"></i>
        </div>
        <div class="error-message" id="passwordError"></div>
      </div>

      <button type="submit" class="login-btn" id="loginButton">
        <i class="fas fa-sign-in-alt"></i>
        <span class="loading fas fa-spinner"></span>
        Iniciar Sesión
      </button>
    </form>

    <div class="additional-options">
      <p><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
      <p>¿No tienes cuenta? <a href="{{ route('register.tutor') }}">Regístrate aquí</a></p>
      <p>¿Problemas para acceder? <a href="#" onclick="mostrarAyuda()">Contactar Soporte</a></p>
      <p><a href="{{ url('/') }}">← Regresar a la página principal</a></p>
    </div>
  </div>
@endsection
