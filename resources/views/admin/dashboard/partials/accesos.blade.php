<section id="accesos" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-bolt"></i> Accesos directos</h3>
  <p>Enlaces rapidos a las funciones mas importantes del sistema.</p>
  <div class="quick-actions">
    <a href="#" class="quick-action" onclick="adminShowSection('registro-vet')">
      <i class="fas fa-user-md"></i>
      <span>Registrar Veterinario</span>
    </a>
    <a href="#" class="quick-action" onclick="adminShowSection('usuarios')">
      <i class="fas fa-users"></i>
      <span>Ver Usuarios</span>
    </a>
    <a href="#" class="quick-action" onclick="adminShowSection('agenda')">
      <i class="fas fa-calendar-alt"></i>
      <span>Gestion de Citas</span>
    </a>
    <a href="{{ route('admin.appointments.report') }}" class="quick-action" target="_blank">
      <i class="fas fa-file-pdf"></i>
      <span>Generar reporte</span>
    </a>

    <!-- <a href="#" class="quick-action" onclick="adminShowSection('clean-data')">
      <i class="fas fa-trash-alt"></i>
      <span>Limpiar Datos</span>
    </a> -->
    <!-- <a href="#" class="quick-action" onclick="adminShowSection('export-data')">
      <i class="fas fa-download"></i>
      <span>Exportar Datos</span>
    </a> -->
   <a href="javascript:void(0)" 
      class="quick-action" 
      onclick="alert('⚙️ Esta funcionalidad está en desarrollo, vuelve pronto!');">
      <i class="fas fa-cog"></i>
      <span>Configuración</span>
    </a>

  </div>
</section>

<script>
(function () {
  const LIST_URL   = "{{ route('admin.activities') }}"; // GET -> { activities: [...] }
  const SECTION_ID = "actividades";
  const $section   = document.getElementById(SECTION_ID);
  const $list      = document.getElementById("activitiesList");

  if (!$section || !$list) return;

  // ---- UI helpers
  function skeleton(n = 5) {
    $list.innerHTML = Array.from({ length:n }).map(() => `
      <div class="activity-item skeleton">
        <div class="activity-dot"></div>
        <div class="activity-body">
          <div class="line w-70"></div>
          <div class="line w-35"></div>
        </div>
      </div>
    `).join("");
  }
  function emptyState() {
    $list.innerHTML = `
      <div class="activity-empty">
        <i class="fas fa-inbox"></i>
        <p>No hay actividades recientes.</p>
      </div>`;
  }
  function itemHTML(a) {
    return `
      <div class="activity-item">
        <div class="activity-dot"><i class="${a.icon || 'fas fa-circle'}"></i></div>
        <div class="activity-body">
          <div class="activity-title">${a.message || '-'}</div>
          <div class="activity-time">${a.time || ''}</div>
        </div>
      </div>`;
  }
  function render(list) {
    if (!list || !list.length) return emptyState();
    $list.innerHTML = list.map(itemHTML).join("");
  }

  // ---- Data
  async function loadActivities() {
    skeleton(6);
    try {
      const url = LIST_URL + (LIST_URL.includes('?') ? '&' : '?') + '_t=' + Date.now(); // cache-bust
      const res = await fetch(url, { headers: { 'Accept':'application/json' }, cache: 'no-store' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();
      render(json.activities || []);
    } catch (e) {
      console.error('[activities] fetch error:', e);
      emptyState();
    }
  }

  // ---- Autorefresco solo visible
  let pollId = null;
  function startPolling() { if (!pollId) pollId = setInterval(loadActivities, 5000); }
  function stopPolling()  { if (pollId) { clearInterval(pollId); pollId = null; } }

  // 1) Si tienes adminShowSection, nos colgamos
  const _oldShow = window.adminShowSection;
  window.adminShowSection = function (id) {
    if (typeof _oldShow === "function") _oldShow(id);
    if (id === SECTION_ID) { loadActivities(); startPolling(); }
    else { stopPolling(); }
  };

  // 2) Fallback: observar cambios de display en la sección (por si NO usas adminShowSection)
  const mo = new MutationObserver(() => {
    const visible = $section.style.display !== 'none';
    if (visible) { loadActivities(); startPolling(); }
    else { stopPolling(); }
  });
  mo.observe($section, { attributes: true, attributeFilter: ['style'] });

  // 3) Si ya estuviera visible al cargar
  if ($section.style.display !== 'none') { loadActivities(); startPolling(); }

  // ---- estilos
  const style = document.createElement("style");
  style.textContent = `
    #activitiesList { max-height:520px; overflow:auto; padding:6px; }
    .activity-item { display:flex; gap:12px; align-items:center; background:#f7f9fc; border-radius:12px; padding:14px; margin:10px 0; }
    .activity-dot { width:44px; height:44px; min-width:44px; border-radius:50%; background:#f58a1f22; display:grid; place-items:center; color:#f58a1f; font-size:18px; }
    .activity-body { flex:1; }
    .activity-title { font-weight:700; color:#1b2430; }
    .activity-time { color:#6b7280; font-size:.9rem; margin-top:2px; }
    .activity-item.skeleton { animation:pulse 1.2s infinite ease-in-out; }
    .activity-item.skeleton .activity-dot { background:#e9edf3; color:transparent; }
    .activity-item.skeleton .line { height:12px; background:#e9edf3; border-radius:6px; margin:6px 0; }
    .activity-item.skeleton .w-70 { width:70%; }
    .activity-item.skeleton .w-35 { width:35%; }
    @keyframes pulse { 0%{opacity:.6} 50%{opacity:1} 100%{opacity:.6} }
    .activity-empty { text-align:center; color:#6b7280; padding:28px 0; }
    .activity-empty i { font-size:28px; margin-bottom:6px; display:block; opacity:.7; }
  `;
  document.head.appendChild(style);
})();
</script>
