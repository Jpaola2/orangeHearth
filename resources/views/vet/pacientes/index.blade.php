<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pacientes - OrangeHearth</title>
  <style>
    body{font-family:"Segoe UI",sans-serif;margin:0;background:#f7f7f7;color:#333}
    .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
    h1{font-size:22px;margin:0;color:#d86f00}
    .search{display:flex;gap:8px}
    .search input{padding:10px 12px;border:1px solid #ddd;border-radius:10px;min-width:320px;height:42px;font-size:14px}
    .search button,.back{padding:10px 14px;border-radius:10px;border:1px solid #f28c28;background:#fff;color:#d86f00;cursor:pointer;height:42px}
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid #eee;text-align:left}
    th{background:#fff8f0;color:#d86f00}
    tr:hover td{background:#fafafa}
    .tag{display:inline-block;padding:2px 8px;border-radius:999px;background:#eef6ff;color:#1e6bb8;font-size:12px}
    .actions a{display:inline-block;padding:6px 10px;border-radius:8px;border:1px solid #ddd;text-decoration:none;color:#333}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h1>Pacientes (Mascotas)</h1>
      <div style="display:flex; gap:8px; align-items:center;">
        <button id="btnNuevo" class="back" type="button">+ Nuevo paciente</button>
        <a class="back" href="{{ route('vet.dashboard') }}">← Panel</a>
      </div>
    </div>

    <form class="search" method="get" action="{{ route('vet.pacientes.index') }}" style="margin-bottom:12px;">
      <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por mascota o tutor..." />
      <button type="submit">Buscar</button>
    </form>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Mascota</th>
            <th>Especie</th>
            <th>Género</th>
            <th>Tutor</th>
            <th>Correo tutor</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($mascotas as $m)
            <tr>
              <td><strong>{{ $m->nom_masc }}</strong></td>
              <td>{{ $m->espe_masc ?? '—' }}</td>
              <td><span class="tag">{{ $m->gene_masc ?? '—' }}</span></td>
              <td>{{ trim(($m->tutor->nomb_tutor ?? '').' '.($m->tutor->apell_tutor ?? '')) ?: 'Sin tutor' }}</td>
              <td>{{ $m->tutor->correo_tutor ?? '—' }}</td>
              <td class="actions">
                <a href="{{ route('vet.pacientes.show', $m) }}">Ver</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6">No hay registros</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal crear paciente -->
  <div id="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div style="background:#fff; width:720px; max-width:95vw; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
      <div style="padding:14px 16px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
        <strong>Nuevo paciente</strong>
        <button id="btnCerrar" class="back" type="button">Cerrar</button>
      </div>
      <div style="padding:14px 16px;">
        <form id="formNuevo">
          @csrf
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
            <label>Nombre mascota
              <input name="nom_masc" required style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>
            <label>Especie
              <input name="espe_masc" placeholder="canino/felino..." style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box;" />
            </label>
            <label>Género
              <select name="gene_masc" style="width:100%; padding:10px 12px; height:42px; font-size:14px; border:1px solid #ccc; border-radius:8px; box-sizing:border-box; background:#fff;">
                <option value="">Seleccione</option>
                <option value="macho">Macho</option>
                <option value="hembra">Hembra</option>
              </select>
            </label>
          </div>

          <hr style="margin:16px 0; border:none; border-top:1px solid #eee;"/>
          <h3 style="margin:0 0 8px;">Tutor</h3>
          <p style="margin:0 0 8px; color:#666;">Busca por correo; si no existe, serás redirigido para crearlo.</p>
          <div style="display:grid; grid-template-columns: 2fr auto; gap:8px; align-items:center;">
            <input id="tutorEmail" type="email" placeholder="correo@ejemplo.com" style="padding:12px; height:44px; border:1px solid #ddd; border-radius:10px; font-size:14px;" />
            <button id="btnBuscarTutor" type="button" class="back">Buscar</button>
          </div>
          <input type="hidden" name="tutor_id" id="tutorId" />
          <div id="foundTutor" style="display:none; margin-top:8px; padding:8px; background:#f7f7f7; border-radius:8px;"></div>

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
    const btnBuscarTutor = document.getElementById('btnBuscarTutor');
    const tutorEmail = document.getElementById('tutorEmail');
    const tutorId = document.getElementById('tutorId');
    const foundTutor = document.getElementById('foundTutor');

    function openModal(){ modal.style.display='flex'; }
    function closeModal(){ modal.style.display='none'; form.reset(); tutorId.value=''; foundTutor.style.display='none'; foundTutor.innerHTML=''; }

    btnNuevo?.addEventListener('click', openModal);
    btnCerrar?.addEventListener('click', closeModal);
    btnCancelar?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });

    btnBuscarTutor?.addEventListener('click', async ()=>{
      foundTutor.style.display='none'; tutorId.value='';
      const q = (tutorEmail.value||'').trim(); if(!q){ alert('Ingresa un correo para buscar'); return; }
      try{
        const url = `{{ route('vet.tutores.search') }}?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, { headers:{'Accept':'application/json'}, credentials:'same-origin' });
        const json = await res.json();
        const item = (json.results||[]).find(r => (r.email||'').toLowerCase()===q.toLowerCase()) || (json.results||[])[0];
        if(item){
          tutorId.value = item.id;
          foundTutor.innerHTML = `<strong>Tutor encontrado:</strong> ${item.nombre} <small>&lt;${item.email||''}&gt;</small>`;
          foundTutor.style.display='block';
        } else {
          if(confirm('No encontramos ese tutor. ¿Deseas crearlo ahora?')){
            const url = `{{ route('vet.tutores.index') }}?create=1&q=${encodeURIComponent(q)}`;
            window.location.href = url;
          }
        }
      }catch(e){ console.error(e); alert('No se pudo buscar el tutor'); }
    });

    form?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!tutorId.value){ alert('Debes seleccionar un tutor antes de guardar la mascota.'); return; }
      const data = new FormData(form);
      try{
        const res = await fetch(`{{ route('vet.pacientes.store') }}`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:data, credentials:'same-origin' });
        if(res.status===422){ const j= await res.json(); alert(Object.values(j.errors||{}).flat().join('\n')||'Validación fallida'); return; }
        if(!res.ok){ const t = await res.text(); throw new Error('Error '+res.status+' '+t); }
        location.reload();
      }catch(err){ console.error(err); alert('No se pudo crear el paciente'); }
    });
  </script>
</body>
</html>
