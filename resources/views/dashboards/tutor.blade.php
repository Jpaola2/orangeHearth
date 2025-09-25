<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Tutor - OrangeHearth</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * { box-sizing: border-box; }
    body { margin:0; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#fff8f0; color:#333; }
    .navbar { display:flex; align-items:center; justify-content:space-between; background:linear-gradient(135deg,#f28c28,#d86f00); padding:1rem 2rem; color:#fff; box-shadow:0 2px 10px rgba(0,0,0,.1); }
    .navbar .brand { display:flex; align-items:center; font-size:1.5rem; font-weight:bold; gap:.5rem; }
    .navbar button { background:#fff; color:#f28c28; border:none; padding:.8rem 1.2rem; font-weight:700; border-radius:10px; cursor:pointer; }
    .navbar button:hover { background:#ffe0c0; transform:translateY(-2px); }
    .main { padding:2rem; max-width:1200px; margin:0 auto; }
    .welcome-section { background:#fff; padding:2rem; border-radius:15px; margin-bottom:2rem; box-shadow:0 5px 20px rgba(0,0,0,.08); text-align:center; }
    .welcome-section h1 { color:#d86f00; margin:0 0 1rem 0; font-size:2.2rem; display:flex; align-items:center; justify-content:center; gap:.5rem; }
    .quick-actions { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.2rem; margin-bottom:2rem; }
    .action-card { background:#fff; padding:1.5rem; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,.08); text-align:center; border:2px solid transparent; cursor:pointer; transition:all .3s; }
    .action-card:hover { border-color:#f28c28; transform:translateY(-5px); }
    .action-card i { font-size:2.2rem; color:#f28c28; margin-bottom:.6rem; }
    .pets-section { background:#fff; padding:2rem; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,.08); }
    .pets-section h2 { color:#d86f00; margin:0 0 1.2rem 0; font-size:1.6rem; display:flex; align-items:center; gap:.5rem; }
    .pets-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1.2rem; }
    .pet-card { border:2px solid #f28c28; border-radius:15px; padding:1.2rem; background:linear-gradient(135deg,#fff,#fff8f0); cursor:pointer; transition:all .2s; }
    .pet-card:hover { transform:translateY(-4px); box-shadow:0 8px 20px rgba(242,140,40,.18); }
    .pet-header { display:flex; align-items:center; gap:1rem; margin-bottom:1rem; }
    .pet-avatar { width:56px; height:56px; background:linear-gradient(135deg,#f28c28,#d86f00); border-radius:50%; display:grid; place-items:center; color:#fff; font-size:1.4rem; font-weight:700; }
    .pet-info h3 { margin:0; font-size:1.15rem; }
    .pet-details { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; margin-bottom:1rem; color:#666; font-size:.9rem; }
    .detail-item i { color:#f28c28; width:16px; }
    .pet-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
    .btn-small { padding:.5rem 1rem; border:none; border-radius:8px; font-size:.8rem; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:.3rem; }
    .btn-primary { background:linear-gradient(135deg,#f28c28,#d86f00); color:#fff; }
    .btn-secondary { background:#fff; color:#f28c28; border:2px solid #f28c28; }
    .no-pets { text-align:center; padding:3rem; color:#666; }
    .no-pets i { font-size:3rem; color:#f28c28; margin-bottom:.6rem; }
    @media (max-width:768px){ .pets-grid{grid-template-columns:1fr;} .quick-actions{grid-template-columns:1fr;} .pet-details{grid-template-columns:1fr;} }
  </style>
</head>
<body>
  <div class="navbar">
    <div class="brand"><i class="fas fa-paw"></i> OrangeHearth</div>
    <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button></form>
  </div>

  <div class="main">
    <div class="welcome-section">
      <h1><i class="fas fa-heart"></i> Hola, {{ auth()->user()->name ?? 'Tutor' }}</h1>
      <p>Gestiona el cuidado de tus mascotas desde un solo lugar</p>
    </div>

    <div class="quick-actions">
      <button type="button" class="action-card" id="btnAddPet">
        <i class="fas fa-plus-circle"></i>
        <h3>Añadir Mascota</h3>
        <p>Registra una nueva mascota en tu perfil</p>
      </button>
      <a class="action-card" href="{{ route('tutor.citas.create') }}">
        <i class="fas fa-calendar-plus"></i>
        <h3>Agendar Cita</h3>
        <p>Programa una consulta veterinaria</p>
      </a>
      <a class="action-card" href="{{ route('tutor.citas.index') }}">
        <i class="fas fa-history"></i>
        <h3>Historial</h3>
        <p>Consulta el historial médico completo</p>
      </a>
      <a class="action-card" href="#" onclick="alert('Emergencias: contacta a tu clínica 24h'); return false;">
        <i class="fas fa-ambulance"></i>
        <h3>Emergencias</h3>
        <p>Contacto directo para urgencias</p>
      </a>
    </div>

    <div class="pets-section">
      <h2><i class="fas fa-paw"></i> Mis Mascotas</h2>
      <div class="pets-grid">
        @forelse(($mascotas ?? []) as $mascota)
          <div class="pet-card" onclick="window.location='{{ route('tutor.mascotas.show', ['mascota' => $mascota->id_masc]) }}'">
            <div class="pet-header">
              <div class="pet-avatar">{{ strtoupper(substr($mascota->nom_masc,0,1)) }}</div>
              <div class="pet-info">
                <h3>{{ $mascota->nom_masc }}</h3>
                <p>{{ $mascota->espe_masc }} • {{ $mascota->gene_masc }}</p>
              </div>
            </div>
            <div class="pet-details">
              <div class="detail-item"><i class="fas fa-tag"></i> <span>{{ $mascota->espe_masc }}</span></div>
              <div class="detail-item"><i class="fas fa-heart"></i> <span>{{ $mascota->gene_masc }}</span></div>
            </div>
            <div class="pet-actions">
              <a class="btn-small btn-primary" href="{{ route('tutor.citas.create', ['mascota' => $mascota->id_masc]) }}"><i class="fas fa-calendar-plus"></i> Agendar Cita</a>
            </div>
          </div>
        @empty
          <div class="no-pets">
            <i class="fas fa-paw"></i>
            <h3>No tienes mascotas registradas</h3>
            <p>Agrega tu primera mascota para comenzar a gestionar su cuidado</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Modal: Nueva mascota -->
  <div id="petModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div style="background:#fff; width:720px; max-width:95vw; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
      <div style="padding:14px 16px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
        <strong>Nueva mascota</strong>
        <button id="btnClosePet" class="btn-small btn-secondary" style="border:1px solid #eee;">Cerrar</button>
      </div>
      <div style="padding:16px;">
        <form id="petForm">
          @csrf
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <label>Nombre
              <input name="nom_masc" required style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px;" />
            </label>
            <label>Especie
              <select name="espe_masc" required style="width:100%; padding:10px 12px; height:42px; border:1px solid #ccc; border-radius:8px;">
                <option value="">Seleccione</option>
                <option value="canino">Canino</option>
                <option value="felino">Felino</option>
                <option value="otro">Otro</option>
              </select>
            </label>
            <label>Género
              <select name="gene_masc" required style="width:100%; padding:10px 12px; height:42px; border:1px solid #ccc; border-radius:8px;">
                <option value="">Seleccione</option>
                <option value="macho">Macho</option>
                <option value="hembra">Hembra</option>
              </select>
            </label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
              <label>Edad (número)
                <input type="number" min="0" name="edad_masc" style="width:100%; padding:10px 12px; height:42px; border:1px solid #ccc; border-radius:8px;" />
              </label>
              <label>Unidad
                <select name="unidad_edad" style="width:100%; padding:10px 12px; height:42px; border:1px solid #ccc; border-radius:8px;">
                  <option value="">—</option>
                  <option value="dias">Días</option>
                  <option value="meses">Meses</option>
                  <option value="años">Años</option>
                </select>
              </label>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:14px;">
            <button type="button" id="btnCancelPet" class="btn-small btn-secondary">Cancelar</button>
            <button type="submit" class="btn-small btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const modal = document.getElementById('petModal');
      const btnOpen = document.getElementById('btnAddPet');
      const btnClose = document.getElementById('btnClosePet');
      const btnCancel = document.getElementById('btnCancelPet');
      const form = document.getElementById('petForm');

      function open(){ modal.style.display='flex'; }
      function close(){ modal.style.display='none'; try{ form.reset(); }catch(e){} }
      btnOpen?.addEventListener('click', open);
      btnClose?.addEventListener('click', close);
      btnCancel?.addEventListener('click', close);
      modal?.addEventListener('click', (e)=>{ if(e.target===modal) close(); });

      form?.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const data = new FormData(form);
        try{
          const res = await fetch(`{{ route('tutor.mascotas.quick') }}`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:data, credentials:'same-origin' });
          if(res.status===422){ const j = await res.json(); alert(Object.values(j.errors||{}).flat().join('\n')||'Validación fallida'); return; }
          if(!res.ok){ const t = await res.text(); throw new Error('Error '+res.status+' '+t); }
          // recargar para ver la nueva mascota en la grilla
          window.location.reload();
        }catch(err){ console.error(err); alert('No se pudo crear la mascota.'); }
      });
    })();
  </script>
</body>
</html>
