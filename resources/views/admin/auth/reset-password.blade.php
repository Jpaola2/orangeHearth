@extends('layouts.auth')

@section('content')
<div class="auth-card">
  <h1>Definir nueva contraseña (Admin)</h1>

  <form method="POST" action="{{ route('admin.password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <label class="form-group">
      <span>Correo electrónico</span>
      <input type="email" name="email" value="{{ old('email') }}" required autofocus>
      @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </label>

    <label class="form-group">
      <span>Nueva contraseña</span>
      <input type="password" name="password" required minlength="8" autocomplete="new-password">
      @error('password') <small class="text-danger">{{ $message }}</small> @enderror
    </label>

    <label class="form-group">
      <span>Confirmar contraseña</span>
      <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
    </label>

    <button class="btn" type="submit">Restablecer</button>
  </form>
</div>
@endsection

