<section id="actividades" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-history"></i> Ultimas actividades del sistema</h3>
  <p>Registro en tiempo real de las acciones mas recientes.</p>
  <div class="activities-list" id="activitiesList"></div>
</section>

<script>
(function () {
  // ===== Endpoints =====
  const LIST_URL   = "{{ route('admin.activities') }}"; // GET -> { activities: [...] }
  const SECTION_ID = "actividades";

  // ===== Elements =====
  const $list = document.getElementById("activitiesList");

  // ===== Helpers UI =====
  function skeleton(n = 5) {
    const rows = Array.from({ length: n }).map(() => `
      <div class="activity-item skeleton">
        <div class="activity-dot"></div>
        <div class="activity-body">
          <div class="line w-70"></div>
          <div class="line w-35"></div>
        </div>
      </div>
    `).join("");
    $list.innerHTML = rows;
  }

  function emptyState() {
    $list.innerHTML = `
      <div class="activity-empty">
        <i class="fas fa-inbox"></i>
        <p>No hay actividades recientes.</p>
      </div>
    `;
  }

  function itemHTML(a) {
    // a.icon viene del backend (ej: 'fas fa-user-plus', 'fas fa-calendar-plus')
    // a.message: texto principal, a.time: ej. "hace 15 minutos"
    return `
      <div class="activity-item">
        <div class="activity-dot">
          <i class="${a.icon || 'fas fa-circle'}"></i>
        </div>
        <div class="activity-body">
          <div class="activity-title">${a.message || '-'}</div>
          <div class="activity-time">${a.time || ''}</div>
        </div>
      </div>
    `;
  }

  function render(list) {
    if (!list || !list.length) return emptyState();
    $list.innerHTML = list.map(itemHTML).join("");
  }

  // ===== Data =====
  async function loadActivities() {
    skeleton(6);
    try {
      const res = await fetch(LIST_URL, { headers: { Accept: "application/json" } });
      if (!res.ok) throw new Error("HTTP " + res.status);
      const json = await res.json();
      render(json.activities || []);
    } catch (e) {
      console.error(e);
      emptyState();
    }
  }

  // ===== Auto-refresh (solo cuando la sección está visible) =====
  let pollId = null;

  function startPolling() {
    if (pollId) return;
    pollId = setInterval(loadActivities, 5000); // cada 5s
  }
  function stopPolling() {
    if (pollId) {
      clearInterval(pollId);
      pollId = null;
    }
  }

  // Integra con tu función global adminShowSection
  const _oldShow = window.adminShowSection;
  window.adminShowSection = function (id) {
    if (typeof _oldShow === "function") _oldShow(id);
    if (id === SECTION_ID) {
      loadActivities();
      startPolling();
    } else {
      stopPolling();
    }
  };

  // Carga inicial si viene visible
  const $section = document.getElementById(SECTION_ID);
  if ($section && $section.style.display !== "none") {
    loadActivities();
    startPolling();
  }

  // ===== Estilos mínimos para que se vea como el mockup =====
  const style = document.createElement("style");
  style.textContent = `
    #activitiesList { 
      max-height: 520px; 
      overflow: auto; 
      padding: 6px; 
    }
    .activity-item {
      display: flex; 
      gap: 12px; 
      align-items: center; 
      background: #f7f9fc; 
      border-radius: 12px; 
      padding: 14px; 
      margin: 10px 0; 
    }
    .activity-item .activity-dot {
      width: 44px; 
      height: 44px; 
      min-width: 44px;
      border-radius: 50%; 
      background: #f58a1f22; 
      display: grid; 
      place-items: center; 
      color: #f58a1f;
      font-size: 18px;
    }
    .activity-item .activity-body { flex: 1; }
    .activity-title { font-weight: 700; color: #1b2430; }
    .activity-time { color: #6b7280; font-size: .9rem; margin-top: 2px; }

    /* Skeleton loading */
    .activity-item.skeleton { animation: pulse 1.2s infinite ease-in-out; }
    .activity-item.skeleton .activity-dot { background: #e9edf3; color: transparent; }
    .activity-item.skeleton .line { height: 12px; background: #e9edf3; border-radius: 6px; margin: 6px 0; }
    .activity-item.skeleton .w-70 { width: 70%; }
    .activity-item.skeleton .w-35 { width: 35%; }
    @keyframes pulse { 0%{opacity:.6} 50%{opacity:1} 100%{opacity:.6} }

    /* Empty state */
    .activity-empty { 
      text-align:center; 
      color:#6b7280; 
      padding: 28px 0; 
    }
    .activity-empty i { font-size: 28px; margin-bottom: 6px; display:block; opacity:.7; }
  `;
  document.head.appendChild(style);
})();
</script>
