<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel Veterinario - OrangeHearth</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root { --naranja:#f28c28; --naranja-oscuro:#d86f00; --gris-suave:#f9f9f9; --blanco:#ffffff; --gris-texto:#444; }
    body { margin:0; font-family:'Segoe UI',sans-serif; display:flex; background:var(--gris-suave); }
    .sidebar { width:240px; background:var(--naranja); color:#fff; padding:2rem 1rem; min-height:100vh; }
    .sidebar h2 { text-align:center; margin-bottom:2rem; font-size:1.6rem; }
    .sidebar ul { list-style:none; padding:0; }
    .sidebar li { margin:1rem 0; padding:.6rem 1rem; border-radius:8px; cursor:pointer; transition:background .3s ease; }
    .sidebar li:hover { background-color:var(--naranja-oscuro); }
    .sidebar a { color:#fff; text-decoration:none; display:flex; align-items:center; gap:.6rem; }
    .sidebar a i { width:18px; opacity:.95; }
    .main { flex-grow:1; padding:2rem; }
    .header { display:flex; justify-content:space-between; align-items:center; background:var(--blanco); padding:1rem 2rem; box-shadow:0 2px 8px rgba(0,0,0,.1); margin-bottom:2rem; border-radius:10px; }
    .header h1 { color:var(--naranja-oscuro); font-size:1.8rem; margin:0; }
    .header button { background:var(--naranja); border:none; color:#fff; padding:.6rem 1.2rem; font-size:1rem; border-radius:8px; cursor:pointer; }
    .acciones { margin:2rem auto; max-width:700px; display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
    .acciones button { flex:1 1 150px; background:var(--blanco); border:2px solid var(--naranja); padding:1rem; border-radius:12px; color:var(--naranja-oscuro); font-weight:700; transition:all .3s ease; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,.05); }
    .acciones button:hover { background-color:var(--naranja); color:#fff; }
    .grid-iconos { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:1.2rem; margin:2rem 0; }
    .icono { background:var(--blanco); padding:1.2rem; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,.05); text-align:center; cursor:pointer; transition:background .3s; }
    .icono:hover { background-color:#fff3e6; }
    .resumen { background:var(--blanco); padding:2rem; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.06); text-align:center; color:var(--gris-texto); }
    .btn-volver { margin-top:2rem; background:#fff; color:var(--naranja); border:2px solid var(--naranja); padding:.6rem 1.2rem; border-radius:8px; font-weight:700; cursor:pointer; }
    .btn-volver:hover { background-color:var(--naranja); color:#fff; }
  </style>
</head>
<body>
  <aside class="sidebar">
    <h2>OrangeHearth</h2>
    <ul>
      <li><a href="{{ route('vet.dashboard') }}"><i class="fas fa-home"></i> Inicio</a></li>
      <li><a href="{{ route('vet.pacientes.index') }}"><i class="fas fa-paw"></i> Paciente</a></li>
      <li><a href="{{ route('vet.tutores.index') }}"><i class="fas fa-user"></i> Propietario</a></li>
      <li><a href="{{ route('vet.agenda.report') }}" target="_blank"><i class="fas fa-file-alt"></i> Reportes</a></li>
      <li><a href="{{ route('vet.agenda') }}"><i class="fas fa-calendar-alt"></i> Agenda</a></li>
      <li><a href="#" onclick="alert('Configuración: en desarrollo');return false;"><i class="fas fa-cog"></i> Configuración</a></li>
    </ul>
  </aside>

  <main class="main">
    <div class="header">
      <h1 id="bienvenida">Bienvenido, {{ auth()->user()->name ?? "Doctor" }}!</h1>
      <form id="logoutForm" method="POST" action="{{ route('logout') }}">@csrf <button type="submit">Cerrar sesión</button></form>
    </div>

    <!-- <div class="acciones">
      <button onclick="alert('Búsqueda de paciente: en desarrollo');">Buscar Paciente</button>
      <button onclick="alert('Crear Paciente: en desarrollo');">Crear Paciente</button>
      <button onclick="alert('Crear Propietario: en desarrollo');">Crear Propietario</button>
    </div> -->

    <h2 style="text-align:center;">¿Qué deseas hacer hoy?</h2>
    <div class="grid-iconos">
      <div class="icono" onclick="alert('Consulta: en desarrollo');"><div style="width:50px;height:50px;background:var(--naranja);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;color:#fff;font-weight:bold;">🩺</div><div>Consulta</div></div>
      <div class="icono" onclick="alert('Cirugía: en desarrollo');"><div style="width:50px;height:50px;background:var(--naranja);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;color:#fff;font-weight:bold;">✂️</div><div>Cirugía</div></div>
      <div class="icono" onclick="alert('Vacunación: en desarrollo');"><div style="width:50px;height:50px;background:var(--naranja);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;color:#fff;font-weight:bold;">💉</div><div>Vacunación</div></div>
      <div class="icono" onclick="alert('Eutanasia: en desarrollo');"><div style="width:50px;height:50px;background:var(--naranja);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;color:#fff;font-weight:bold;">😔</div><div>Eutanasia</div></div>
      <div class="icono" onclick="alert('Exámenes: en desarrollo');"><div style="width:50px;height:50px;background:var(--naranja);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;color:#fff;font-weight:bold;">🔬</div><div>Exámenes</div></div>
      <div class="icono" onclick="alert('Fórmula: en desarrollo');"><div style="width:50px;height:50px;background:var(--naranja);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;color:#fff;font-weight:bold;">📋</div><div>Fórmula</div></div>
      <div class="icono" onclick="alert('Desparasitación: en desarrollo');"><div style="width:50px;height:50px;background:var(--naranja);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .5rem;color:#fff;font-weight:bold;">🪱</div><div>Desparasitación</div></div>
    </div>

    <div class="resumen">
      <h3>Resumen de Eventos</h3>
      <p>Total citas hoy: <strong>-</strong></p>
      <p>Periodo: {{ now()->toDateString() }}</p>
      <!-- <button class="btn-volver" onclick="window.location='{{ url('/') }}'">Volver al inicio</button> -->
    </div>
  </main>
</body>
</html>

<script>
  (function(){
    // Inyectar campana de notificaciones al lado del boton de logout
    const header = document.querySelector('.header');
    if(header){
      const form = header.querySelector('form[action*="logout"]');
      const wrap = document.createElement('div');
      wrap.style.display = 'flex';
      wrap.style.alignItems = 'center';
      wrap.style.gap = '10px';

      const bellWrap = document.createElement('div');
      bellWrap.style.position = 'relative';
      bellWrap.innerHTML = '<button id="btnBell" style="background:#fff;border:1px solid #eee;color:#d86f00;padding:.6rem;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;"><i class="fas fa-bell"></i></button><span id="bellCount" style="position:absolute;top:-6px;right:-6px;background:#dc3545;color:#fff;border-radius:999px;font-size:11px;padding:2px 6px;display:none;">0</span><div id="notifPanel" style="display:none; position:absolute; right:0; top:48px; width:360px; max-height:380px; overflow:auto; background:#fff; border:1px solid #eee; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,.1); z-index:1000;"><div style="padding:8px 12px; border-bottom:1px solid #eee; font-weight:600; color:#d86f00;">Notificaciones</div><div id="notifList" style="padding:8px 12px; font-size:14px;">Sin novedades</div></div>';

      if(form && form.parentNode){
        wrap.appendChild(bellWrap);
        form.parentNode.insertBefore(wrap, form);
        wrap.appendChild(form);
      }
    }

    async function loadNotifications(){
      try{
        const res = await fetch("{{ route('vet.notifications') }}", { headers:{'Accept':'application/json'}, credentials:'same-origin' });
        const j = await res.json();
        const items = j.items||[];
        const count = items.length;
        const countEl = document.getElementById('bellCount');
        const listEl = document.getElementById('notifList');
        if(!countEl || !listEl) return;
        countEl.style.display = count ? 'inline-block' : 'none';
        countEl.textContent = count;
        listEl.innerHTML = items.length ? items.map(i=>`<div style='padding:6px 0;border-bottom:1px solid #f3f3f3;'>${i.message}<br><small style='color:#666'>${i.time}</small></div>`).join('') : 'Sin novedades';
      }catch(e){ /* ignore */ }
    }
    document.addEventListener('click', (e)=>{
      const btn = document.getElementById('btnBell');
      const panel = document.getElementById('notifPanel');
      if(!btn || !panel) return;
      if(e.target===btn || (btn.contains(e.target))){ panel.style.display = panel.style.display==='block'?'none':'block'; return; }
      if(!panel.contains(e.target)){ panel.style.display='none'; }
    });
    setInterval(loadNotifications, 20000);
    loadNotifications();
  })();
</script>
