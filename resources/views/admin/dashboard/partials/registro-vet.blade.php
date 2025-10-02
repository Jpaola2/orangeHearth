@if (session('success'))
  <div class="alert success">{{ session('success') }}</div>
@endif

@if ($errors->any())
  <div class="alert error">
    <ul style="margin:0;padding-left:1rem;">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<section id="registro-vet" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-user-md"></i> Registrar nuevo médico veterinario</h3>

  <form id="vetForm" method="POST" action="{{ route('admin.veterinarios.store') }}" class="form-grid" novalidate>
    @csrf

    <label class="form-group">
      <span><i class="fas fa-user"></i> Nombre completo</span>
      <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Juan Camilo Pérez">
      @error('nombre')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-id-card"></i> Cédula *</span>
      <input type="text" name="cedula" value="{{ old('cedula') }}" required placeholder="Ej: 1234567890">
      @error('cedula')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-envelope"></i> Correo electrónico</span>
      <input type="email" name="correo" value="{{ old('correo') }}" required placeholder="veterinario@orangehearth.com" autocomplete="email">
      @error('correo')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-lock"></i> Contraseña</span>
      <input type="password" name="clave" required placeholder="Contraseña segura" minlength="8" autocomplete="new-password">
      @error('clave')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-stethoscope"></i> Especialidad</span>
      <select name="especialidad" required>
        <option value="">Seleccione especialidad...</option>
        <option value="Medicina General" {{ old('especialidad')==='Medicina General'?'selected':'' }}>Medicina General</option>
        <option value="Cirugía" {{ old('especialidad')==='Cirugía'?'selected':'' }}>Cirugía</option>
        <option value="Dermatología" {{ old('especialidad')==='Dermatología'?'selected':'' }}>Dermatología</option>
        <option value="Cardiología" {{ old('especialidad')==='Cardiología'?'selected':'' }}>Cardiología</option>
        <option value="Neurología" {{ old('especialidad')==='Neurología'?'selected':'' }}>Neurología</option>
        <option value="Oncología" {{ old('especialidad')==='Oncología'?'selected':'' }}>Oncología</option>
      </select>
      @error('especialidad')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-id-badge"></i> Tarjeta profesional *</span>
      <input type="text" name="tarjeta_profesional" value="{{ old('tarjeta_profesional') }}" required placeholder="Ej: TP123456">
      @error('tarjeta_profesional')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-phone"></i> Teléfono</span>
      <input type="tel" name="telefono" value="{{ old('telefono') }}" required placeholder="+57 300 123 4567" autocomplete="tel">
      @error('telefono')<small class="error">{{ $message }}</small>@enderror
    </label>

    <div class="form-actions">
      <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Registrar veterinario</button>
    </div>
  </form>
</section>


<script>
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('vetForm');
  if(!form) return;
  const fields = [
    {name:'nombre', req:true},
    {name:'cedula', req:true, test:v=>/^\d{6,20}$/.test(v)},
    {name:'correo', req:true, test:v=>/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)},
    {name:'clave', req:true, test:v=>v.length>=8},
    {name:'especialidad', req:true},
    {name:'tarjeta_profesional', req:true},
    {name:'telefono', req:true}
  ];
  function setErr(input, ok, msg){
    input.classList.toggle('error', !ok);
    const err = input.parentElement.querySelector('small.error');
    if(err){ err.textContent = ok? '' : (msg||'Campo inválido'); err.style.display = ok? 'none':'block'; }
  }
  function validateInput(input){
    const def = fields.find(f=>f.name===input.name); if(!def) return true;
    const v = (input.value||'').trim();
    let ok = true; let msg='';
    if(def.req && !v){ ok=false; msg='Este campo es obligatorio'; }
    if(ok && def.test){ ok = !!def.test(v); if(!ok) msg='Formato inválido'; }
    setErr(input, ok, msg); return ok;
  }
  form.querySelectorAll('input,select').forEach(i=>{
    i.addEventListener('blur', ()=>validateInput(i));
    i.addEventListener('input', ()=>validateInput(i));
  });
  form.addEventListener('submit', e=>{
    let ok = true; fields.forEach(f=>{ const i=form.querySelector(`[name="${f.name}"]`); if(i && !validateInput(i)) ok=false; });
    if(!ok){ e.preventDefault(); alert('Corrige los campos en rojo antes de continuar.'); }
  });
});
</script>

<style>
#registro-vet .form-group input.error, #registro-vet .form-group select.error {
  border:2px solid #dc3545 !important; background:#fdf2f2;
}
#registro-vet small.error { color:#dc3545; display:none; }
</style>
