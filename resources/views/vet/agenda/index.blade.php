<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agenda - Veterinario</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body{font-family:"Segoe UI",sans-serif;margin:0;background:#f7f7f7;color:#333}
    .wrap{max-width:1200px;margin:24px auto;padding:0 16px}
    h1{font-size:22px;margin:0 0 10px;color:#d86f00}
    .filters{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:8px;background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:12px}
    select,input{padding:10px 12px;border:1px solid #ddd;border-radius:10px;height:42px}
    .btn{padding:10px 14px;border-radius:10px;border:1px solid #f28c28;background:#fff;color:#d86f00;cursor:pointer;height:42px;display:inline-flex;align-items:center;gap:8px;text-decoration:none}
    .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:12px 0}
    .kpi{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:18px;text-align:center}
    .kpi .num{font-size:26px;font-weight:800}
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid #eee;text-align:left}
    th{background:#fff8f0;color:#d86f00}
    .empty{padding:16px;text-align:center;color:#666}
  </style>
</head>
<body>
  <div class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <h1 style="margin:0;"><i class="fas fa-calendar-alt"></i> Mi agenda</h1>
      <a href="{{ route('vet.dashboard') }}" class="btn" title="Volver al panel">← Volver</a>
    </div>

    <div class="filters">
      <select id="fEstado">
        <option value="">Todos los estados</option>
        <option value="pendiente">Pendiente</option>
        <option value="confirmada">Confirmada</option>
        <option value="completada">Completada</option>
        <option value="cancelada">Cancelada</option>
      </select>
      <input type="date" id="fFecha" />
      <div></div>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button class="btn" id="btnNueva"><i class="fas fa-plus"></i> Nueva cita</button>
        <a class="btn" href="{{ route('vet.agenda.report') }}" target="_blank" title="Generar reporte PDF"><i class="fas fa-file-pdf"></i> Reporte PDF</a>
        <button class="btn" id="btnRefrescar"><i class="fas fa-rotate"></i> Actualizar</button>
      </div>
    </div>

    <div class="grid">
      <div class="kpi"><div class="num" id="kHoy">0</div><div>Citas hoy</div></div>
      <div class="kpi"><div class="num" id="kSemana">0</div><div>Esta semana</div></div>
      <div class="kpi"><div class="num" id="kConf">0</div><div>Confirmadas</div></div>
      <div class="kpi"><div class="num" id="kPend">0</div><div>Pendientes</div></div>
    </div>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Mascota</th>
            <th>Tutor</th>
            <th>Especialidad</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="6" class="empty">Cargando…</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal nueva cita -->
  <div id="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); align-items:center; justify-content:center;">
    <div style="background:#fff; width:680px; max-width:95vw; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
      <div style="padding:14px 16px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
        <strong>Nueva cita</strong>
        <button id="btnClose" class="btn">Cerrar</button>
      </div>
      <div style="padding:14px 16px;">
        <form id="formNueva">
          @csrf
          <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px;">
            <label>Fecha
            <input type="date" name="fecha" id="fechaNueva" required
              style="width:100%; padding:10px 12px; height:42px; font-size:14px;
                    border:1px solid #ccc; border-radius:8px; box-sizing:border-box; background:#fff;">
          </label>

          <label>Hora
            <input type="time" name="hora" id="horaNueva" step="900" required
              style="width:100%; padding:10px 12px; height:42px; font-size:14px;
                    border:1px solid #ccc; border-radius:8px; box-sizing:border-box; background:#fff;">
          </label>

          <label>Motivo
            <input type="text" name="motivo" required placeholder="Control, Vacunación…"
              style="width:100%; padding:10px 12px; height:42px; font-size:14px;
                    border:1px solid #ccc; border-radius:8px; box-sizing:border-box;">
          </label>
          </div>
          <hr style="margin:16px 0; border:none; border-top:1px solid #eee;"/>
          <h4 style="margin:0 0 8px;">Tutor y Mascota</h4>
          <div style="display:grid; grid-template-columns:2fr auto; gap:8px; align-items:center;">
            <input id="emailTutor" type="email" placeholder="correo@ejemplo.com" style="padding:10px 12px; height:42px; border:1px solid #ddd; border-radius:10px;">
            <button type="button" id="btnBuscar" class="btn">Buscar</button>
          </div>
          <input type="hidden" name="tutor_id" id="tutorId">
          <div id="tutorInfo" style="display:none; margin-top:8px; padding:8px; background:#f7f7f7; border-radius:8px;"></div>
          <div style="margin-top:10px;">
            <label>Mascota
              <select id="mascotaSel" name="mascota_id" style="width:100%; padding:10px 12px; height:42px; border:1px solid #ddd; border-radius:10px;">
                <option value="">Seleccione una mascota…</option>
              </select>
            </label>
          </div>
          <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:14px;">
            <button type="button" id="btnCancel" class="btn">Cancelar</button>
            <button type="submit" class="btn" style="background:#f28c28;color:#fff;border-color:#f28c28;">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const URL_LIST = "{{ route('vet.appointments') }}";
    const URL_UPDATE = (id) => "{{ url('/vet/appointments') }}/"+id+"/estado";

    const fEstado = document.getElementById('fEstado');
    const fFecha  = document.getElementById('fFecha');
    const tbody   = document.getElementById('tbody');
    const kHoy = document.getElementById('kHoy');
    const kSemana = document.getElementById('kSemana');
    const kConf = document.getElementById('kConf');
    const kPend = document.getElementById('kPend');

    function setEmpty(text){ tbody.innerHTML = `<tr><td class="empty" colspan="6">${text}</td></tr>`; }

    function buildQuery(){
      const p = new URLSearchParams();
      if (fEstado.value) p.set('estado', fEstado.value);
      if (fFecha.value)  p.set('fecha',  fFecha.value);
      return p.toString() ? ('?'+p.toString()) : '';
    }

    async function load(){
      setEmpty('Cargando…');
      try{
        const res = await fetch(URL_LIST + buildQuery(), { headers:{'Accept':'application/json'}, credentials:'same-origin' });
        const j = await res.json();
        render(j.appointments||[]);
        const s = j.summary||{}; kHoy.textContent=s.citas_hoy||0; kSemana.textContent=s.citas_semana||0; kConf.textContent=s.confirmadas||0; kPend.textContent=s.pendientes||0;
      }catch(e){ setEmpty('No se pudo cargar'); }
    }

    function render(rows){
      if(!rows.length){ setEmpty('Sin citas'); return; }
      tbody.innerHTML = rows.map(c=>{
        return `<tr data-id="${c.id}">
          <td>${c.fecha_hora || `${c.fecha} ${c.hora || ''}`}</td>
          <td>${(c.mascota||'')}</td>
          <td>${(c.tutor||'')}<br><small>${c.tutor_email||''}</small></td>
          <td>${c.especialidad||''}</td>
          <td>${c.estado||'pendiente'}</td>
          <td>
            <select data-act="estado">
              ${['pendiente','confirmada','completada','cancelada'].map(v=>`<option value="${v}" ${v===c.estado?'selected':''}>${v}</option>`).join('')}
            </select>
            <button data-act="aplicar">Aplicar</button>
          </td>
        </tr>`;
      }).join('');
    }

    tbody.addEventListener('click', async (e)=>{
      const btn = e.target.closest('button[data-act="aplicar"]');
      if(!btn) return;
      const tr = btn.closest('tr'); const id = tr?.dataset.id; const sel = tr?.querySelector('select[data-act="estado"]');
      if(!id||!sel) return;
      try{
        const res = await fetch(URL_UPDATE(id), { method:'PATCH', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({estado: sel.value}), credentials:'same-origin' });
        if(!res.ok){ throw new Error('Error '+res.status); }
        await load();
      }catch(err){ alert('No se pudo actualizar'); }
    });

    // Modal handlers
    const modal = document.getElementById('modal');
    const btnNueva = document.getElementById('btnNueva');
    const btnClose = document.getElementById('btnClose');
    const btnCancel = document.getElementById('btnCancel');
    const formNueva = document.getElementById('formNueva');
    const emailTutor = document.getElementById('emailTutor');
    const btnBuscar = document.getElementById('btnBuscar');
    const tutorId = document.getElementById('tutorId');
    const tutorInfo = document.getElementById('tutorInfo');
    const mascotaSel = document.getElementById('mascotaSel');
    const fechaNueva = document.getElementById('fechaNueva');
    const horaNueva = document.getElementById('horaNueva');
    const medicoSel = document.getElementById('medico');

    async function cargarVeterinariosDisponibles() {

      if (!medicoSel) return;

      const f = fechaNueva ? fechaNueva.value : '';

      const h = horaNueva ? horaNueva.value : '';

      if (!f) {

        medicoSel.innerHTML = '<option value="">Seleccione una fecha...</option>';

        return;

      }

      medicoSel.innerHTML = '<option value="">Cargando...</option>';

      try {

        let url = `{{ route('tutor.citas.available-vets') }}?fecha=${encodeURIComponent(f)}`;

        if (h) {

          url += `&hora=${encodeURIComponent(h)}`;

        }

        const res = await fetch(url, { headers:{'Accept':'application/json'}, credentials:'same-origin' });

        const j = await res.json();

        const vets = j.veterinarios || [];

        medicoSel.innerHTML = vets.length

          ? vets.map(v => `<option value="${v.id}">${v.nombre}${v.especialidad ? ' - ' + v.especialidad : ''}</option>`).join('')

          : '<option value="">Sin disponibilidad</option>';

      } catch (error) {

        console.error(error);

        medicoSel.innerHTML = '<option value="">Error cargando medicos</option>';

      }

    }



    fechaNueva?.addEventListener('change', cargarVeterinariosDisponibles);
    horaNueva?.addEventListener('change', () => { if (fechaNueva?.value) cargarVeterinariosDisponibles(); });

    function openModal(){ modal.style.display='flex'; tutorInfo.style.display='none'; tutorInfo.innerHTML=''; tutorId.value=''; mascotaSel.innerHTML='<option value="">Seleccione una mascota…</option>'; }
    function closeModal(){ modal.style.display='none'; formNueva.reset(); tutorId.value=''; tutorInfo.innerHTML=''; tutorInfo.style.display='none'; mascotaSel.innerHTML='<option value="">Seleccione una mascota…</option>'; }
    btnNueva?.addEventListener('click', openModal);
    btnClose?.addEventListener('click', closeModal);
    btnCancel?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });

    btnBuscar?.addEventListener('click', async ()=>{
      const q = (emailTutor.value||'').trim(); if(!q){ alert('Ingresa un correo de tutor'); return; }
      try{
        const url = `{{ route('vet.tutores.search') }}?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, { headers:{'Accept':'application/json'}, credentials:'same-origin' });
        const json = await res.json();
        const item = (json.results||[]).find(r => (r.email||'').toLowerCase()===q.toLowerCase()) || (json.results||[])[0];
        if(item){
          tutorId.value = item.id;
          tutorInfo.innerHTML = `<strong>Tutor:</strong> ${item.nombre} <small>&lt;${item.email||''}&gt;</small>`;
          tutorInfo.style.display='block';
          // cargar mascotas
          const petsRes = await fetch(`{{ url('/vet/tutores') }}/${item.id}/mascotas`, { headers:{'Accept':'application/json'}, credentials:'same-origin' });
          const pj = await petsRes.json();
          const pets = pj.pets||[];
          mascotaSel.innerHTML = '<option value="">Seleccione una mascota…</option>' + pets.map(p=>`<option value="${p.id_masc}">${p.nom_masc}</option>`).join('');
        } else {
          if(confirm('No encontramos ese tutor. ¿Deseas crearlo ahora?')){
            const url2 = `{{ route('vet.tutores.index') }}?create=1&q=${encodeURIComponent(q)}`; window.location.href = url2;
          }
        }
      }catch(e){ console.error(e); alert('No se pudo buscar el tutor'); }
    });

    formNueva?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      if(!tutorId.value){ alert('Selecciona un tutor.'); return; }
      if(!mascotaSel.value){ alert('Selecciona una mascota.'); return; }
      const data = new FormData(formNueva);
      try{
        const res = await fetch(`{{ route('vet.appointments.store') }}`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:data, credentials:'same-origin' });
        if(res.status===422){ const j= await res.json(); alert(Object.values(j.errors||{}).flat().join('\n')||'Validación fallida'); return; }
        if(!res.ok){ const t = await res.text(); throw new Error('Error '+res.status+' '+t); }
        closeModal();
        load();
      }catch(err){ console.error(err); alert('No se pudo crear la cita'); }
    });

    document.getElementById('btnRefrescar').addEventListener('click', load);
    fEstado.addEventListener('change', load);
    fFecha.addEventListener('change', load);
    load();
  </script>
</body>
</html>
