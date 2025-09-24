@extends('layouts.auth-register')

@section('content')
  <div class="registro-box">
    <h2>Registro de Tutor y Mascota</h2>
    <form id="registroForm" method="POST" action="{{ route('register.tutor.perform') }}" novalidate>
      @csrf
      <div class="section-title">Información del Tutor</div>

      <div class="form-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Juan Pérez González" required data-validation="nombre">
        <small class="error-message">@error('nombre'){{ $message }}@enderror</small>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="tipo_id">Tipo de Identificación</label>
          <select id="tipo_id" name="tipo_id" required>
            <option value="">Seleccione...</option>
            <option value="DNI" @selected(old('tipo_id')==='DNI')>DNI</option>
            <option value="NIE" @selected(old('tipo_id')==='NIE')>NIE</option>
            <option value="Pasaporte" @selected(old('tipo_id')==='Pasaporte')>Pasaporte</option>
            <option value="Cedula" @selected(old('tipo_id')==='Cedula')>Cédula</option>
          </select>
          <small class="error-message"></small>
        </div>
        <div class="form-group">
          <label for="numero_id">Número de Identificación</label>
          <input type="text" id="numero_id" name="numero_id" value="{{ old('numero_id') }}" placeholder="12345678A" required data-validation="documento">
          <small class="error-message">@error('numero_id'){{ $message }}@enderror</small>
        </div>
      </div>

      <div class="form-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" name="correo" value="{{ old('correo') }}" placeholder="correo@ejemplo.com" required data-validation="email">
        <small class="error-message">@error('correo'){{ $message }}@enderror</small>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required data-validation="password">
        <small class="error-message">@error('password'){{ $message }}@enderror</small>
        <div class="password-strength">
          <div class="requirement" data-requirement="length"><span class="icon">✓</span> 8-20 caracteres</div>
          <div class="requirement" data-requirement="uppercase"><span class="icon">✓</span> Al menos una mayúscula</div>
          <div class="requirement" data-requirement="lowercase"><span class="icon">✓</span> Al menos una minúscula</div>
          <div class="requirement" data-requirement="number"><span class="icon">✓</span> Al menos un número</div>
          <div class="requirement" data-requirement="special"><span class="icon">✓</span> Carácter especial (!@#$%^&*)</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="telefono">Teléfono</label>
          <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="+57 3001234567" required data-validation="telefono">
          <small class="error-message">@error('telefono'){{ $message }}@enderror</small>
        </div>
      </div>

      <div class="form-group">
        <label for="direccion">Dirección</label>
        <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" placeholder="Calle Ejemplo 123, Ciudad" required data-validation="direccion">
        <small class="error-message">@error('direccion'){{ $message }}@enderror</small>
      </div>

      <div class="section-title">Información de la Mascota</div>

      <div class="form-row">
        <div class="form-group">
          <label for="mascota">Nombre de la mascota</label>
          <input type="text" id="mascota" name="mascota" value="{{ old('mascota') }}" placeholder="Ej: Max, Luna, Firulais" required data-validation="mascota">
          <small class="error-message">@error('mascota'){{ $message }}@enderror</small>
        </div>
        <div class="form-group">
          <label for="especie">Especie</label>
          <select id="especie" name="especie" required>
            <option value="">Seleccione...</option>
            <option value="Perro" @selected(old('especie')==='Perro')>Perro</option>
            <option value="Gato" @selected(old('especie')==='Gato')>Gato</option>
            <option value="Conejo" @selected(old('especie')==='Conejo')>Conejo</option>
            <option value="Hamster" @selected(old('especie')==='Hamster')>Hamster</option>
            <option value="Pájaro" @selected(old('especie')==='Pájaro')>Pájaro</option>
            <option value="Otro" @selected(old('especie')==='Otro')>Otro</option>
          </select>
          <small class="error-message">@error('especie'){{ $message }}@enderror</small>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edad">Edad de la mascota</label>
          <div style="display:flex;gap:.5rem;align-items:stretch;">
            <input type="number" id="edad" name="edad" value="{{ old('edad') }}" placeholder="Ej: 2" min="1" max="50" data-validation="edad" style="flex:2;">
            <select id="unidad_edad" name="unidad_edad" style="flex:1; padding: .9rem; border:2px solid #e0e0e0; border-radius:8px; font-size:1rem; background:white;">
              <option value="">Unidad</option>
              <option value="dias" @selected(old('unidad_edad')==='dias')>Días</option>
              <option value="meses" @selected(old('unidad_edad')==='meses')>Meses</option>
              <option value="años" @selected(old('unidad_edad')==='años')>Años</option>
            </select>
          </div>
          <small class="error-message"></small>
        </div>
        <div class="form-group">
          <label for="genero">Género</label>
          <select id="genero" name="genero" required>
            <option value="">Seleccione...</option>
            <option value="macho" @selected(old('genero')==='macho')>Macho</option>
            <option value="hembra" @selected(old('genero')==='hembra')>Hembra</option>
          </select>
          <small class="error-message"></small>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="raza">Raza (opcional)</label>
          <input type="text" id="raza" name="raza" value="{{ old('raza') }}" placeholder="Ej: Labrador, Persa">
          <small class="error-message"></small>
        </div>
      </div>

      <button type="submit">Registrar Tutor y Mascota</button>
    </form>
  </div>
@endsection
