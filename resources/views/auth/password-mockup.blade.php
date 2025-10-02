@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('content')
<div class="container" style="max-width: 480px; margin-top:40px;">
    <h2 class="mb-3 text-center"><i class="fas fa-lock"></i> Recuperar contraseña</h2>

    <div class="alert alert-info">
        <strong>Mockup:</strong> Esta es una pantalla de ejemplo de cómo sería el flujo de cambio de contraseña.
    </div>

    <form>
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" id="email" class="form-control" placeholder="tucorreo@ejemplo.com">
        </div>

        <div class="mb-3">
            <label for="new-password" class="form-label">Nueva contraseña</label>
            <input type="password" id="new-password" class="form-control" placeholder="••••••••">
        </div>

        <div class="mb-3">
            <label for="confirm-password" class="form-label">Confirmar contraseña</label>
            <input type="password" id="confirm-password" class="form-control" placeholder="••••••••">
        </div>

        <button type="button" class="btn btn-primary w-100">
            <i class="fas fa-sync"></i> Cambiar contraseña
        </button>
    </form>

    <div class="mt-3 text-center">
        <a href="{{ route('login.admin') }}" class="btn btn-link">← Volver al login</a>
    </div>
</div>
@endsection
