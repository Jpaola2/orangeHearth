{{-- resources/views/admin/dashboard/partials/agenda.blade.php --}}
<section id="agenda" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-calendar-alt"></i> Gestión de citas del sistema</h3>
  <p>Visualiza y gestiona todas las citas agendadas en el sistema.</p>

  <div class="actions-row">
    <button class="btn" type="button" data-action="refresh-appointments">
      <i class="fas fa-sync"></i> Actualizar citas
    </button>
    <button class="btn btn-secondary" type="button" data-action="export-appointments">
      <i class="fas fa-file-excel"></i> Exportar citas
    </button>
  </div>

  <div class="filters-panel">
    <h4>Filtros</h4>
    <div class="filters-grid">
      <label>
        <span>Estado</span>
        <select id="filtroEstado">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendiente</option>
          <option value="confirmada">Confirmada</option>
          <option value="completada">Completada</option>
          <option value="cancelada">Cancelada</option>
        </select>
      </label>

      <label>
        <span>Veterinario</span>
        <select id="filtroVeterinario">
          <option value="">Todos los veterinarios</option>
          @foreach(($veterinarios ?? []) as $vet)
            <option value="{{ $vet['id'] }}">{{ $vet['nombre'] }} ({{ $vet['estado'] }})</option>
          @endforeach
        </select>
      </label>

      <label>
        <span>Fecha</span>
        <input type="date" id="filtroFecha">
      </label>
    </div>
  </div>

  <div class="stats-grid" id="citasResumen">
    <div class="stat-card">
      <div class="stat-number highlight-orange" id="citasHoy">0</div>
      <div class="stat-label">Citas hoy</div>
    </div>
    <div class="stat-card">
      <div class="stat-number highlight-blue" id="citasSemana">0</div>
      <div class="stat-label">Esta semana</div>
    </div>
    <div class="stat-card">
      <div class="stat-number highlight-green" id="citasConfirmadas">0</div>
      <div class="stat-label">Confirmadas</div>
    </div>
    <div class="stat-card">
      <div class="stat-number highlight-red" id="citasPendientes">0</div>
      <div class="stat-label">Pendientes</div>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="data-table" id="citasTable">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Mascota</th>
          <th>Tutor</th>
          <th>Veterinario</th>
          <th>Especialidad</th>
          <th>Estado</th>
          <th style="width:180px;">Acciones</th>
        </tr>
      </thead>
      <tbody id="citasTableBody">
        <tr>
          <td colspan="7" class="empty-cell">Sin citas registradas</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<script>
(function () {
  // ==== ENDPOINTS (tus rutas existentes) ====
  const LIST_URL    = "{{ route('admin.appointments') }}";            // GET JSON (?estado=&veterinario=&fecha=)
  const EXPORT_URL  = "{{ route('admin.appointments.export') }}";      // GET CSV (mismos filtros)
  const UPDATE_URL  = id => "{{ url('/admin/appointments') }}/" + id + "/estado"; // PATCH
  const CSRF_TOKEN  = "{{ csrf_token() }}";

  // ==== ELEMENTOS UI ====
  const SECTION_ID   = 'agenda';
  const $tbody       = document.getElementById('citasTableBody');
  const $btnRefresh  = document.querySelector('[data-action="refresh-appointments"]');
  const $btnExport   = document.querySelector('[data-action="export-appointments"]');

  // filtros
  const $fEstado     = document.getElementById('filtroEstado');
  const $fVet        = document.getElementById('filtroVeterinario');
  const $fFecha      = document.getElementById('filtroFecha');

  // KPIs
  const $kHoy        = document.getElementById('citasHoy');
  const $kSemana     = document.getElementById('citasSemana');
  const $kConf       = document.getElementById('citasConfirmadas');
  const $kPend       = document.getElementById('citasPendientes');

  // ==== HELPERS ====
  function setEmptyRow(text) {
    $tbody.innerHTML = `<tr><td colspan="7" class="empty-cell">${text}</td></tr>`;
  }
  function badgeEstado(estado) {
    const map = {
      pendiente:  {bg:'#fff0f0', fg:'#cc2e2e'},
      confirmada:{bg:'#e8f9ef', fg:'#1ea672'},
      completada:{bg:'#e8f1ff', fg:'#2a6df4'},
      cancelada: {bg:'#f6f6f6', fg:'#666'}
    };
    const s = (estado || 'pendiente').toLowerCase();
    const c = map[s] || map['pendiente'];
    return `<span style="display:inline-block;padding:.2rem .55rem;border-radius:999px;background:${c.bg};color:${c.fg};font-size:.75rem;font-weight:600">${s.charAt(0).toUpperCase()+s.slice(1)}</span>`;
  }
  function estadoSelectHTML(actual) {
    const opts = ['pendiente','confirmada','completada','cancelada']
      .map(v => `<option value="${v}" ${v===actual?'selected':''}>${v.charAt(0).toUpperCase()+v.slice(1)}</option>`).join('');
    return `<select data-action="change-estado" class="mini-select">${opts}</select>`;
  }
  function fmt(str){ return (str ?? '').toString(); }

  // ==== RENDER ====
  function renderStats(summary){
    $kHoy.textContent    = summary?.citas_hoy ?? 0;
    $kSemana.textContent = summary?.citas_semana ?? 0;
    $kConf.textContent   = summary?.confirmadas ?? 0;
    $kPend.textContent   = summary?.pendientes ?? 0;
  }

  function renderRows(rows){
    if (!rows || !rows.length) return setEmptyRow('Sin citas registradas');
    $tbody.innerHTML = rows.map(c => `
      <tr data-id="${c.id}">
        <td>${fmt(c.fecha)}</td>
        <td>${fmt(c.mascota)}</td>
        <td>${fmt(c.tutor)}</td>
        <td>${fmt(c.veterinario)}</td>
        <td>${fmt(c.especialidad)}</td>
        <td>${badgeEstado(c.estado)}</td>
        <td style="white-space:nowrap;display:flex;gap:8px;align-items:center;">
          ${estadoSelectHTML((c.estado || 'pendiente').toLowerCase())}
          <button type="button" class="btn btn-sm" data-action="aplicar-estado" title="Aplicar"><span style="font-size:13px;">✅</span></button>
        </td>
      </tr>
    `).join('');
  }

  // ==== CARGA ====
  function buildQuery(){
    const q = new URLSearchParams();
    if ($fEstado.value) q.set('estado',$fEstado.value);
    if ($fVet.value)    q.set('veterinario',$fVet.value);
    if ($fFecha.value)  q.set('fecha',$fFecha.value);
    return q.toString() ? ('?'+q.toString()) : '';
  }

  async function loadAppointments(){
    setEmptyRow('Cargando…');
    try{
      const res = await fetch(LIST_URL + buildQuery(), { headers:{'Accept':'application/json'} });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const json = await res.json();
      renderRows(json.appointments || []);
      renderStats(json.summary || {});
    }catch(e){
      console.error(e);
      setEmptyRow('No se pudo cargar la lista. Intenta nuevamente.');
      renderStats({citas_hoy:0,citas_semana:0,confirmadas:0,pendientes:0});
    }
  }

  // ==== ACCIONES ====
  // Cambiar estado (delegación en tbody)
  $tbody.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('button[data-action="aplicar-estado"]');
    if (!btn) return;

    const tr = btn.closest('tr');
    const id = tr?.dataset.id;
    const sel = tr?.querySelector('select[data-action="change-estado"]');
    if (!id || !sel) return;

    const nuevoEstado = sel.value;
    try{
      const res = await fetch(UPDATE_URL(id), {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ estado: nuevoEstado })
      });
      if (!res.ok){
        const text = await res.text();
        throw new Error('HTTP '+res.status+' '+text);
      }
      await loadAppointments(); // refrescar de inmediato
    }catch(e){
      console.error(e);
      alert('No se pudo actualizar el estado.');
    }
  });

  // Exportar (respeta filtros actuales)
  function exportAppointments(){
    window.location.href = EXPORT_URL + buildQuery();
  }

  // Filtros => recarga con debounce
  let t;
  function triggerReload(){
    clearTimeout(t);
    t = setTimeout(loadAppointments, 250);
  }
  $fEstado.addEventListener('change', triggerReload);
  $fVet   .addEventListener('change', triggerReload);
  $fFecha .addEventListener('change', triggerReload);

  // Botones
  if ($btnRefresh) $btnRefresh.addEventListener('click', loadAppointments);
  if ($btnExport)  $btnExport.addEventListener('click', exportAppointments);

  // Hook con tu función global para mostrar secciones
  const _oldShow = window.adminShowSection;
  window.adminShowSection = function(id){
    if (typeof _oldShow === 'function') _oldShow(id);
    if (id === SECTION_ID) loadAppointments(); // carga automática al abrir
  };

  // Carga inicial si ya está visible
  const $section = document.getElementById(SECTION_ID);
  if ($section && $section.style.display !== 'none') loadAppointments();

  // ==== estilos mínimos para el select chico en acciones ====
  const style = document.createElement('style');
  style.textContent = `
    .mini-select{
      font-size: .8rem;
      padding: .25rem .45rem;
      border-radius: 6px;
      border: 1px solid #ddd;
    }
    #citasTable td { vertical-align: middle; }
    .btn.btn-sm{ padding:.25rem .45rem; line-height:1; }
  `;
  document.head.appendChild(style);
})();
</script>
