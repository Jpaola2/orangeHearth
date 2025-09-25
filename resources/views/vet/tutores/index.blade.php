<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Propietarios (Tutores)</title>
  <style>
    body{font-family:"Segoe UI",sans-serif;margin:0;background:#f7f7f7;color:#333}
    .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
    h1{font-size:22px;margin:0;color:#d86f00}
    .back,button{padding:10px 14px;border-radius:10px;border:1px solid #f28c28;background:#fff;color:#d86f00;cursor:pointer;height:42px}
    .search{display:flex;gap:8px;margin-bottom:12px}
    .search input{padding:10px 12px;border:1px solid #ddd;border-radius:10px;min-width:320px;height:42px;font-size:14px}
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid #eee;text-align:left}
    th{background:#fff8f0;color:#d86f00}
    .count{display:inline-block;background:#eef6ff;color:#1e6bb8;border-radius:999px;padding:2px 8px;font-size:12px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h1>Propietarios (Tutores)</h1>
      <div style="display:flex; gap:8px; align-items:center;">
        <button id="btnNuevo">+ Nuevo tutor</button>
        <a class="back" href="{{ route('vet.dashboard') }}">← Panel</a>
      </div>
    </div>

    <form class="search" method="get" action="{{ route('vet.tutores.index') }}">
      <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre, cédula o correo..." />
      <button type="submit">Buscar</button>
    </form>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Cédula</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Mascotas</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tutores as $t)
            <tr>
              <td><strong>{{ trim(($t->nomb_tutor ?? '').' '.($t->apell_tutor ?? '')) }}</strong></td>
              <td>{{ $t->ced_tutor ?? '—' }}</td>
              <td>{{ $t->correo_tutor ?? '—' }}</td>
              <td>{{ $t->tel_tutor ?? '—' }}</td>
              <td>{{ $t->direc_tutor ?? '—' }}</td>
              <td><span class="count">{{ $t->mascotas_count }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6">No hay registros</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal nuevo tutor -->
  <div id="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div style="background:#fff; width:680px; max-width:95vw; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
      <div style="padding:14px 16px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
        <strong>Nuevo tutor</strong>
        <button id="btnCerrar" class="back" type="button">Cerrar</button>
      </div>
      <div style="padding:14px 16px;">
        <form id="formNuevo">
          @csrf
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <label>Nombre 
              <input name="nomb_tutor" required 
                style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>

            <label>Apellido 
              <input name="apell_tutor" 
                style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>

            <label>Cédula 
              <input name="ced_tutor" 
                style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>

            <label>Teléfono 
              <input name="tel_tutor" 
                style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>

            <label>Correo 
              <input type="email" name="correo_tutor" required 
                style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>

            <label>Dirección 
              <input name="direc_tutor" 
                style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>

          </div>
          <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:14px;">
            <button type="button" id="btnCancelar" class="back">Cancelar</button>
            <button type="submit" class="back" style="background:#f28c28; color:#fff; border-color:#f28c28;">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const modal = document.getElementById('modal');
    const btnNuevo = document.getElementById('btnNuevo');
    const btnCerrar = document.getElementById('btnCerrar');
    const btnCancelar = document.getElementById('btnCancelar');
    const form = document.getElementById('formNuevo');

    function openModal(){ modal.style.display='flex'; }
    function closeModal(){ modal.style.display='none'; form.reset(); }
    btnNuevo?.addEventListener('click', openModal);
    btnCerrar?.addEventListener('click', closeModal);
    btnCancelar?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });

    form?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const data = new FormData(form);
      try{
        const res = await fetch(`{{ route('vet.tutores.store') }}`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:data, credentials:'same-origin' });
        if(res.status===422){ const j= await res.json(); alert(Object.values(j.errors||{}).flat().join('\n')||'Validación fallida'); return; }
        if(!res.ok){ const t= await res.text(); throw new Error('Error '+res.status+' '+t); }
        location.reload();
      }catch(err){ console.error(err); alert('No se pudo crear el tutor'); }
    });

    // Autoabrir modal para crear con correo prellenado
    (function(){
      const params = new URLSearchParams(window.location.search);
      if (params.get('create') === '1') {
        const q = params.get('q') || '';
        openModal();
        try {
          const emailField = document.querySelector('input[name="correo_tutor"]');
          if (emailField && q) emailField.value = q;
        } catch(e) {}
      }
    })();
  </script>
</body>
</html>
