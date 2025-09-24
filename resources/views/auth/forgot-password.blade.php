@extends('layouts.auth')

@section('content')
  <a href="{{ url()->previous() }}" class="back-link">
    <i class="fas fa-arrow-left"></i>
    Volver
  </a>

  <div class="login-container">
    <div class="logo-section">
      <h1>
        <i class="fas fa-unlock-alt"></i>
        OrangeHearth
      </h1>
      <p>Recuperar contraseña</p>
      <div class="veterinary-badge">
        <i class="fas fa-life-ring"></i>
        Soporte de Acceso
      </div>
    </div>

    @if(session('status'))
      <div class="professional-info" role="status">{{ session('status') }}</div>
    @endif

    <div class="professional-info">
      <h4><i class="fas fa-info-circle"></i> Instrucciones</h4>
      <p>Ingresa tu correo electrónico y define tu nueva contraseña.</p>
    </div>

    <form id="forgotForm" method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="form-group">
        <label for="email"><i class="fas fa-envelope"></i> Correo Electrónico</label>
        <input type="email" id="email" name="email" placeholder="tu@email.com" value="{{ old('email') }}" required>
        <div class="error-message @error('email') visible @enderror" id="emailError">@error('email'){{ $message }}@enderror</div>
      </div>

      <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Nueva contraseña</label>
        <div class="password-toggle">
          <input type="password" id="password" name="password" required>
          <i class="fas fa-eye" id="togglePassword"></i>
        </div>
        <div class="error-message @error('password') visible @enderror" id="passwordError">@error('password'){{ $message }}@enderror</div>
      </div>

      <div class="form-group">
        <label for="password_confirmation"><i class="fas fa-lock"></i> Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
      </div>

      <button type="submit" class="login-btn" id="loginButton">
        <i class="fas fa-paper-plane"></i>
        <span class="loading fas fa-spinner"></span>
        Actualizar contraseña
      </button>
    </form>

    <div class="additional-options">
      <p><a href="{{ url('/') }}">← Regresar a la página principal</a></p>
    </div>
  </div>
@endsection
