@extends('layouts.auth')

@section('content')
  <a href="{{ url('/') }}" class="back-link">
    <i class="fas fa-arrow-left"></i>
    Volver al Inicio
  </a>

  <div class="login-container">
    <div class="logo-section">
      <h1>
        <i class="fas fa-user-md"></i>
        OrangeHearth
      </h1>
      <p>Panel Veterinario</p>
      <div class="veterinary-badge">
        <i class="fas fa-stethoscope"></i>
        Acceso Profesional
      </div>
    </div>

    <div class="professional-info">
      <h4><i class="fas fa-info-circle"></i> Información Importante</h4>
      <p>Para acceder al sistema, necesitas tu correo registrado, contraseña y <strong>tarjeta profesional válida</strong>. Si no tienes acceso, contacta al administrador del sistema.</p>
    </div>

    <form id="loginForm" method="POST" action="{{ route('login.perform') }}">
      @csrf
      <input type="hidden" name="role" value="vet">

      <div class="form-group">
        <label for="email">
          <i class="fas fa-envelope"></i>
          Correo Electrónico
        </label>
        <input type="email" 
               id="email" 
               name="email"
               placeholder="veterinario@orangehearth.com"
               value="{{ old('email') }}"
               required>
        <div class="error-message @if($errors->has('email') || session('error')) visible @endif" id="emailError">
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
          <input type="password" 
                 id="password" 
                 name="password"
                 placeholder="Tu contraseña"
                 required>
          <i class="fas fa-eye" id="togglePassword"></i>
        </div>
        <div class="error-message" id="passwordError"></div>
      </div>

      <div class="form-group">
        <label for="tarjeta_profesional">
          <i class="fas fa-id-card"></i>
          Tarjeta Profesional *
        </label>
        <input type="text" 
               id="tarjeta_profesional" 
               name="tarjeta_profesional"
               placeholder="ej: TP123456"
               style="text-transform: uppercase;">
        <div class="error-message @error('tarjeta_profesional') visible @enderror" id="tarjetaError">@error('tarjeta_profesional'){{ $message }}@enderror</div>
      </div>

      <button type="submit" class="login-btn" id="loginButton">
        <i class="fas fa-sign-in-alt"></i>
        <span class="loading fas fa-spinner"></span>
        Iniciar Sesión
      </button>
    </form>

    <div class="additional-options">
      <p>¿Problemas para acceder? <a href="#" onclick="mostrarAyuda()">Contactar Soporte</a></p>
      <p><a href="{{ url('/') }}">← Regresar a la página principal</a></p>
    </div>
  </div>
@endsection
