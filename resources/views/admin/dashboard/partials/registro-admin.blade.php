@if (session('success_admin'))
  <div class="alert success">{{ session('success_admin') }}</div>
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

<section id="registro-admin" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-user-shield"></i> Registrar nuevo administrador</h3>

  <form id="adminForm" method="POST" action="{{ route('admin.admins.store') }}" class="form-grid" novalidate>
    @csrf

    <label class="form-group">
      <span><i class="fas fa-user"></i> Nombre completo</span>
      <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ej: Admin General">
      @error('name')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-id-card"></i> Cédula</span>
      <input type="text" name="cedula" value="{{ old('cedula') }}" required placeholder="Ej: 1234567890">
      @error('cedula')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-envelope"></i> Correo electrónico</span>
      <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@orangehearth.com" autocomplete="email">
      @error('email')<small class="error">{{ $message }}</small>@enderror
    </label>

    <label class="form-group">
      <span><i class="fas fa-lock"></i> Contraseña</span>
      <input type="password" name="password" required placeholder="Contraseña segura" minlength="8" autocomplete="new-password">
      @error('password')<small class="error">{{ $message }}</small>@enderror
    </label>

    <div class="form-actions">
      <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Registrar administrador</button>
    </div>
  </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('adminForm');
  if(!form) return;
  const fields = [
    {name:'name', req:true},
    {name:'cedula', req:true, test:v=>/^\d{6,20}$/.test(v)},
    {name:'email', req:true, test:v=>/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)},
    {name:'password', req:true, test:v=>v.length>=8},
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
  form.querySelectorAll('input').forEach(i=>{
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
#registro-admin .form-group input.error { border:2px solid #dc3545 !important; background:#fdf2f2; }
#registro-admin small.error { color:#dc3545; display:none; }
</style>
