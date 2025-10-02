@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
<div class="container" style="max-width: 720px;">
  <h2 class="mb-3"><i class="fas fa-id-card"></i> Mi perfil</h2>

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

  <form method="POST" action="{{ route('admin.profile.update') }}" novalidate>
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Nombre completo</label>
      <input type="text" name="nombre_completo" class="form-control" value="{{ old('nombre_completo', $admin->nombre_completo) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Correo electrónico</label>
      <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Teléfono (opcional)</label>
      <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $admin->telefono) }}" placeholder="+573001112233">
    </div>

    <div class="mb-3">
      <label class="form-label">Cédula</label>
      <input type="text" name="cedula" class="form-control" value="{{ old('cedula', $admin->cedula) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Empresa (Razón social)</label>
      <input type="text" name="empresa_nombre" class="form-control" value="{{ old('empresa_nombre', $admin->empresa_nombre) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">NIT (5 dígitos)</label>
      <input type="text" name="nit" class="form-control" value="{{ old('nit', $admin->nit) }}" required pattern="\d{5}" placeholder="22296">
      <div class="form-text">Debe coincidir con el NIT autorizado.</div>
    </div>

    <hr class="my-4">

    <div class="mb-3">
      <label class="form-label">Nueva contraseña (opcional)</label>
      <input type="password" name="password" class="form-control" minlength="8" placeholder="Dejar en blanco para no cambiar">
    </div>
    <div class="mb-3">
      <label class="form-label">Confirmar nueva contraseña</label>
      <input type="password" name="password_confirmation" class="form-control" minlength="8">
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Guardar cambios
      </button>
      <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Volver al dashboard</a>
    </div>
  </form>
</div>
@endsection

