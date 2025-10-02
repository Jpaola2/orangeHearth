{{-- resources/views/admin/dashboard/partials/agenda.blade.php --}}
<section id="agenda" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-calendar-alt"></i> Gesti&oacute;n de citas del sistema</h3>
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
            <option value="{{ $vet['id'] }}">{{ $vet['nombre'] }}</option>
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
          <th style="width:160px;">Acciones</th>
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
document.addEventListener("DOMContentLoaded", function () {
  // Endpoints (sin reprogramación)
  const LIST_URL = @json(route('admin.appointments'));
  const EXPORT_URL = @json(route('admin.appointments.export'));
  const BASE_APPOINTMENT_URL = @json(url('/admin/appointments'));
  const UPDATE_URL = (id) => `${BASE_APPOINTMENT_URL}/${id}/estado`;
  const ASSIGN_VET_URL = (id) => `${BASE_APPOINTMENT_URL}/${id}/assign-vet`;

  const VETS = @json($veterinarios ?? []);
  const CSRF_TOKEN = @json(csrf_token());

  const SECTION_ID = "agenda";
  const tbody = document.getElementById("citasTableBody");
  const btnRefresh = document.querySelector('[data-action="refresh-appointments"]');
  const btnExport = document.querySelector('[data-action="export-appointments"]');
  const filtroEstado = document.getElementById("filtroEstado");
  const filtroVeterinario = document.getElementById("filtroVeterinario");
  const filtroFecha = document.getElementById("filtroFecha");
  const kpiHoy = document.getElementById("citasHoy");
  const kpiSemana = document.getElementById("citasSemana");
  const kpiConf = document.getElementById("citasConfirmadas");
  const kpiPend = document.getElementById("citasPendientes");

  function setEmptyRow(message) {
    tbody.innerHTML = `<tr><td colspan="7" class="empty-cell">${message}</td></tr>`;
  }

  function renderBadge(estado) {
    const styles = {
      pendiente:   { bg: "#fff0f0", fg: "#cc2e2e" },
      confirmada:  { bg: "#e8f9ef", fg: "#1ea672" },
      completada:  { bg: "#e8f1ff", fg: "#2a6df4" },
      cancelada:   { bg: "#f6f6f6", fg: "#666" },
    };
    const key = (estado || "pendiente").toLowerCase();
    const palette = styles[key] || styles.pendiente;
    const label = key.charAt(0).toUpperCase() + key.slice(1);
    return `<span style="display:inline-block;padding:.2rem .55rem;border-radius:999px;background:${palette.bg};color:${palette.fg};font-size:.75rem;font-weight:600">${label}</span>`;
  }

  function estadoSelect(value) {
    const estados = ["pendiente", "confirmada", "completada", "cancelada"];
    return `<select data-action="change-estado" class="mini-select">
      ${estados.map((estado) => `<option value="${estado}" ${estado === value ? "selected" : ""}>
        ${estado.charAt(0).toUpperCase() + estado.slice(1)}
      </option>`).join("")}
    </select>`;
  }

  function vetSelect(selectedId) {
    const options = [{ id: "", nombre: "Sin asignar" }].concat(VETS);
    return `<select data-action="change-vet" class="mini-select">
      ${options.map((vet) => `<option value="${vet.id}" ${String(vet.id) === String(selectedId || "") ? "selected" : ""}>
        ${vet.nombre}
      </option>`).join("")}
    </select>`;
  }

  function renderStats(summary = {}) {
    kpiHoy.textContent = summary.citas_hoy ?? 0;
    kpiSemana.textContent = summary.citas_semana ?? 0;
    kpiConf.textContent = summary.confirmadas ?? 0;
    kpiPend.textContent = summary.pendientes ?? 0;
  }

  function renderRows(rows = []) {
    if (!rows.length) {
      setEmptyRow("Sin citas registradas");
      return;
    }

    tbody.innerHTML = rows.map((row) => `
      <tr data-id="${row.id}">
        <td>${row.fecha}</td>
        <td>${row.mascota}</td>
        <td>${row.tutor}</td>
        <td>${vetSelect(row.veterinario_id)}</td>
        <td>${row.especialidad}</td>
        <td>${renderBadge(row.estado)}</td>
        <td style="display:flex;gap:8px;align-items:center;white-space:nowrap;">
          ${estadoSelect((row.estado || "pendiente").toLowerCase())}
          <button type="button" class="btn btn-sm" data-action="aplicar-estado" title="Aplicar">Aplicar</button>
        </td>
      </tr>
    `).join("");
  }

  function buildQueryString() {
    const params = new URLSearchParams();
    if (filtroEstado.value) params.set("estado", filtroEstado.value);
    if (filtroVeterinario.value) params.set("veterinario", filtroVeterinario.value);
    if (filtroFecha.value) params.set("fecha", filtroFecha.value);
    const qs = params.toString();
    return qs ? `?${qs}` : "";
  }

  async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
      credentials: "same-origin",
      headers: { Accept: "application/json", ...(options.headers || {}) },
      ...options,
    });
    if (!response.ok) {
      const text = await response.text();
      throw new Error(`HTTP ${response.status} ${text}`);
    }
    return response.json();
  }

  async function patchJson(url, payload = {}) {
    return fetchJson(url, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": CSRF_TOKEN,
      },
      body: JSON.stringify(payload),
    });
  }

  async function loadAppointments() {
    setEmptyRow("Cargando...");
    try {
      const data = await fetchJson(LIST_URL + buildQueryString());
      renderRows(data.appointments || []);
      renderStats(data.summary || {});
    } catch (error) {
      console.error(error);
      setEmptyRow("No se pudo cargar la lista. Intenta nuevamente.");
      renderStats({ citas_hoy: 0, citas_semana: 0, confirmadas: 0, pendientes: 0 });
    }
  }

  async function cambiarEstado(row) {
    const id = row.dataset.id;
    const select = row.querySelector('select[data-action="change-estado"]');
    if (!id || !select) return;
    try {
      await patchJson(UPDATE_URL(id), { estado: select.value });
      await loadAppointments();
    } catch (error) {
      console.error(error);
      alert("No se pudo actualizar el estado.");
    }
  }

  async function asignarVeterinario(row, select) {
    const id = row?.dataset?.id;
    if (!id || !select) return;
    try {
      await patchJson(ASSIGN_VET_URL(id), { id_mv: select.value || null });
      await loadAppointments();
    } catch (error) {
      console.error(error);
      alert("No se pudo asignar el m\u00E9dico.");
    }
  }

  // Listeners
  tbody.addEventListener("change", async function (event) {
    const select = event.target.closest('select[data-action="change-vet"]');
    if (!select) return;
    const row = select.closest("tr[data-id]");
    if (!row) return;
    await asignarVeterinario(row, select);
  });

  tbody.addEventListener("click", function (event) {
    const row = event.target.closest("tr[data-id]");
    if (!row) return;
    if (event.target.closest('button[data-action="aplicar-estado"]')) {
      cambiarEstado(row);
      return;
    }
  });

  function exportAppointments() {
    window.location.href = EXPORT_URL + buildQueryString();
  }

  // Filtros con debounce
  let debounceId;
  function triggerReload() {
    clearTimeout(debounceId);
    debounceId = setTimeout(loadAppointments, 250);
  }

  filtroEstado.addEventListener("change", triggerReload);
  filtroVeterinario.addEventListener("change", triggerReload);
  filtroFecha.addEventListener("change", triggerReload);
  if (btnRefresh) btnRefresh.addEventListener("click", loadAppointments);
  if (btnExport) btnExport.addEventListener("click", exportAppointments);

  // Integración con el sidebar
  const previousShowSection = window.adminShowSection;
  window.adminShowSection = function (id, event) {
    let result = false;
    if (typeof previousShowSection === "function") {
      result = previousShowSection(id, event);
    }
    if (id === SECTION_ID) {
      loadAppointments();
    }
    return result;
  };

  // Si ya está visible, cargar de una
  const section = document.getElementById(SECTION_ID);
  if (section && section.style.display !== "none") {
    loadAppointments();
  }
});
</script>
