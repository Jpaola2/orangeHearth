@extends('layouts.app')

@section('title', 'Registrar administrador')

@section('content')
<div class="container" style="max-width: 720px;">
  <h2 class="mb-3"><i class="fas fa-user-shield"></i> Registrar administrador</h2>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.admins.store') }}" novalidate>
    @csrf

    <div class="mb-3">
      <label class="form-label">Nombre completo</label>
      <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Correo electrÃ³nico</label>
      <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">TelÃ©fono (opcional)</label>
      <input type="text" name="telefono" value="{{ old('telefono') }}" class="form-control" placeholder="+573001112233">
    </div>

    <div class="mb-3">
      <label class="form-label">CÃ©dula</label>
      <input type="text" name="cedula" value="{{ old('cedula') }}" class="form-control" required placeholder="Solo dÃ­gitos (6â€“10)">
    </div>

    <div class="mb-3">
      <label class="form-label">Empresa (RazÃ³n social)</label>
      <input type="text" name="empresa_nombre" value="{{ old('empresa_nombre') }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">NIT (5 dígitos)</label>
      <input type="password" name="nit" value="{{ old('nit') }}" class="form-control" required minlength="5" maxlength="5" pattern="\d{5}" inputmode="numeric" autocomplete="off" placeholder="NIT autorizado">
      <div class="form-text">Debe coincidir con el NIT autorizado en el sistema.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">ContraseÃ±a</label>
      <input type="password" name="password" class="form-control" required minlength="8">
    </div>

    <div class="mb-3">
      <label class="form-label">Confirmar contraseÃ±a</label>
      <input type="password" name="password_confirmation" class="form-control" required minlength="8">
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-check-circle"></i> Crear cuenta
      </button>
      <a href="{{ route('login') }}" class="btn btn-outline-secondary">Volver al inicio de sesiÃ³n</a>
    </div>
  </form>
</div>
@endsection
