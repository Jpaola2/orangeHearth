// Fallback global handler: works even if setupSidebar did not run yet
window.adminShowSection = window.adminShowSection || function(id, ev){
  try { if (ev) ev.preventDefault(); } catch (e) {}
  var sections = document.querySelectorAll('.dashboard-section');
  if (!sections || !sections.length) return false;
  sections.forEach(function(s){ s.classList.toggle('active', s.id === id); });
  var links = document.querySelectorAll('.sidebar a');
  links.forEach(function(a){ a.classList && a.classList.toggle('active', a.dataset && a.dataset.section === id); });
  return false;
} 
const readyCallbacks = [];

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => readyCallbacks.forEach(cb => cb()));
} else {
  setTimeout(() => readyCallbacks.forEach(cb => cb()), 0);
}

function onReady(callback) {
  readyCallbacks.push(callback);
}

onReady(() => {
  const root = document.getElementById('admin-dashboard');
  if (!root) {
    console.warn('Admin dashboard root element not found.');
    return;
  }

  const rawConfig = root.dataset.config || root.getAttribute('data-config') || window.dashboardConfig;
  const rawPreload = root.dataset.preload || root.getAttribute('data-preload') || window.dashboardPreload;

  const config = parseConfig(rawConfig);
  const preload = parseConfig(rawPreload);

  if (!config) {
    console.warn('Admin dashboard configuration missing or invalid.');
    return;
  }

  const state = {
    charts: {},
    filters: { estado: '', veterinario: '', fecha: '' },
    veterinarios: Array.isArray(config.veterinarios) ? [...config.veterinarios] : [],
    appointments: [],
  };

  const elements = collectElements();
  setupSidebar(elements);
  setupQuickActions(elements, state, config);
  setupFilterListeners(elements, state, config);
  setupUsersActions(elements, config);
  setupAppointmentsActions(elements, state, config);
  setupVetForm(elements, state, config);
  populateVeterinarios(elements.filtroVeterinario, state.veterinarios);

  // Inicializar filtros desde los inputs
  state.filters = {
    estado: elements.filtroEstado ? elements.filtroEstado.value : '',
    veterinario: elements.filtroVeterinario ? elements.filtroVeterinario.value : '',
    fecha: elements.filtroFecha ? elements.filtroFecha.value : '',
  };

  // Precarga inmediata para que los contadores muestren datos al instante
  seedFromPreload(preload, elements);
  refreshAll(state, config, elements);
});

function parseConfig(raw) {
  if (!raw) {
    return null;
  }
  if (typeof raw === 'object') {
    return raw;
  }
  // Si viene como HTML-escapado desde Blade
  const normalized = decodeHtmlEntities(String(raw));
  try {
    return JSON.parse(normalized);
  } catch (error) {
    console.error('Cannot parse dashboard config', error, normalized);
    return null;
  }
}

function decodeHtmlEntities(value) {
  const textarea = document.createElement('textarea');
  textarea.innerHTML = value;
  return textarea.value;
}

function collectElements() {
  return {
    sections: document.querySelectorAll('.dashboard-section'),
    sidebarLinks: document.querySelectorAll('.sidebar a'),
    quickActions: document.querySelectorAll('.quick-action'),
    alert: document.getElementById('dashboardAlert'),
    summary: {
      usuarios: document.getElementById('totalUsuarios'),
      mascotas: document.getElementById('totalMascotas'),
      citas: document.getElementById('totalCitas'),
      veterinarios: document.getElementById('totalVeterinarios'),
    },
    userTypeChart: document.getElementById('userTypeChart'),
    monthlyChart: document.getElementById('monthlyChart'),
    speciesChart: document.getElementById('speciesChart'),
    activityChart: document.getElementById('activityChart'),
    activitiesList: document.getElementById('activitiesList'),
    usersTableBody: document.getElementById('usersTableBody'),
    citasTableBody: document.getElementById('citasTableBody'),
    filtroEstado: document.getElementById('filtroEstado'),
    filtroVeterinario: document.getElementById('filtroVeterinario'),
    filtroFecha: document.getElementById('filtroFecha'),
    citasSummary: {
      hoy: document.getElementById('citasHoy'),
      semana: document.getElementById('citasSemana'),
      confirmadas: document.getElementById('citasConfirmadas'),
      pendientes: document.getElementById('citasPendientes'),
    },
    vetForm: document.getElementById('vetForm'),
  };
}

function setupSidebar(elements) {
  elements.sidebarLinks.forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();
      const sectionId = link.dataset.section;
      if (!sectionId) return;
      elements.sidebarLinks.forEach(item => item.classList.remove('active'));
      link.classList.add('active');
      showSection(sectionId, elements.sections);
    });
  });
  // Exponer para llamadas inline si fuese necesario
  window.adminShowSection = (id, ev) => {
    if (ev) ev.preventDefault();
    elements.sidebarLinks.forEach(item => item.classList.toggle('active', item.dataset.section === id));
    showSection(id, elements.sections);
  };
}

function setupQuickActions(elements, state, config) {
  elements.quickActions.forEach(action => {
    action.addEventListener('click', event => {
      event.preventDefault();
      const section = action.dataset.section;
      const actionKey = action.dataset.action;
      if (section) {
        window.adminShowSection(section, event);
        return;
      }
      if (actionKey) {
        handleQuickAction(actionKey, state, config, elements);
      }
    });
  });
}

function setupFilterListeners(elements, state, config) {
  if (elements.filtroEstado) {
    elements.filtroEstado.addEventListener('change', () => {
      state.filters.estado = elements.filtroEstado.value;
      loadAppointments(state, config, elements);
    });
  }
  if (elements.filtroVeterinario) {
    elements.filtroVeterinario.addEventListener('change', () => {
      state.filters.veterinario = elements.filtroVeterinario.value;
      loadAppointments(state, config, elements);
    });
  }
  if (elements.filtroFecha) {
    elements.filtroFecha.addEventListener('change', () => {
      state.filters.fecha = elements.filtroFecha.value;
      loadAppointments(state, config, elements);
    });
  }
}

function setupUsersActions(elements, config) {
  const refreshBtn = document.querySelector('[data-action="refresh-users"]');
  const exportBtn = document.querySelector('[data-action="export-users"]');
  if (refreshBtn) refreshBtn.addEventListener('click', () => loadUsers(config, elements));
  if (exportBtn) exportBtn.addEventListener('click', () => { window.location.href = config.endpoints.exportUsers; });
}

function setupAppointmentsActions(elements, state, config) {
  const refreshBtn = document.querySelector('[data-action="refresh-appointments"]');
  const exportBtn = document.querySelector('[data-action="export-appointments"]');
  const statsBtn = document.querySelector('[data-action="show-appointments-report"]');
  if (refreshBtn) refreshBtn.addEventListener('click', () => loadAppointments(state, config, elements));
  if (exportBtn) exportBtn.addEventListener('click', () => { const url = buildUrl(config.endpoints.exportAppointments, state.filters); window.location.href = url; });
  if (statsBtn) statsBtn.addEventListener('click', () => generateAppointmentsReport(state, config, elements));

  if (elements.citasTableBody) {
    elements.citasTableBody.addEventListener('click', event => {
      const button = event.target.closest('button[data-action]');
      if (!button) return;
      const action = button.dataset.action;
      const id = parseInt(button.dataset.id, 10);
      if (!id) return;
      const mapping = { confirm: 'confirmada', cancel: 'cancelada', complete: 'completada' };
      const newStatus = mapping[action];
      if (!newStatus) return;
      updateAppointmentStatus(id, newStatus, state, config, elements);
    });
  }
}

function setupVetForm(elements, state, config) {
  if (!elements.vetForm) return;

  elements.vetForm.addEventListener('submit', async event => {
    event.preventDefault();

    const formData = new FormData(elements.vetForm);
    const payload = Object.fromEntries(formData.entries());

    try {
      const response = await fetch(config.endpoints.storeVet, { method: 'POST', headers: buildHeaders(config.csrfToken, true), body: JSON.stringify(payload), credentials: 'same-origin' });
      if (!response.ok) {
        const message = await extractError(response);
        const error = new Error(message || 'No fue posible registrar el veterinario.');
        error.status = response.status;
        throw error;
      }

      const data = await response.json();
      elements.vetForm.reset();
      state.veterinarios.push(data.veterinario);
      populateVeterinarios(elements.filtroVeterinario, state.veterinarios);
      flashMessage(elements.alert, 'success', data.message || 'Registro exitoso.');

      const redirectUrl = (config.redirects && config.redirects.dashboard) || '/admin';
      setTimeout(() => { window.location.href = redirectUrl; }, 600);
    } catch (error) {
      console.error(error);
      const message = error && error.status === 422 ? 'Datos invalidos. Revisa la informacion e intentalo nuevamente.' : (error && error.message ? error.message : 'No fue posible registrar el veterinario.');
      flashMessage(elements.alert, 'error', message);
    }
  });
}

function seedFromPreload(preload, elements) {
  if (!preload || typeof preload !== 'object') return;
  if (preload.totals) {
    updateText(elements.summary.usuarios, preload.totals.usuarios ?? 0);
    updateText(elements.summary.mascotas, preload.totals.mascotas ?? 0);
    updateText(elements.summary.citas, preload.totals.citas ?? 0);
    updateText(elements.summary.veterinarios, preload.totals.veterinarios ?? 0);
  }
  if (preload.distribution) {
    renderUserTypeChart(elements.userTypeChart, preload.distribution);
  }
}

async function refreshSummary(config, elements) {
  try {
    const data = await fetchJson(config.endpoints.summary);
    if (data?.totals) {
      updateText(elements.summary.usuarios, data.totals.usuarios);
      updateText(elements.summary.mascotas, data.totals.mascotas);
      updateText(elements.summary.citas, data.totals.citas);
      updateText(elements.summary.veterinarios, data.totals.veterinarios);
    }
    renderUserTypeChart(elements.userTypeChart, data?.distribution);
  } catch (error) {
    console.error('Error loading summary', error);
    flashMessage(elements.alert, 'error', 'No fue posible cargar el resumen.');
  }
}

async function loadStatistics(config, state, elements) {
  try {
    const data = await fetchJson(config.endpoints.statistics);
    renderMonthlyChart(elements.monthlyChart, state.charts, data?.monthly);
    renderSpeciesChart(elements.speciesChart, state.charts, data?.species);
    renderActivityChart(elements.activityChart, state.charts, data?.activity);
  } catch (error) { console.error('Error loading statistics', error); }
}

async function loadActivities(config, elements) {
  try { const data = await fetchJson(config.endpoints.activities); renderActivities(elements.activitiesList, data?.activities || []); } catch (error) { console.error('Error loading activities', error); }
}

async function loadUsers(config, elements) {
  try { const data = await fetchJson(config.endpoints.users); renderUsers(elements.usersTableBody, data?.users || []); } catch (error) { console.error('Error loading users', error); flashMessage(elements.alert, 'error', 'No fue posible cargar los usuarios.'); }
}

async function loadAppointments(state, config, elements) {
  try {
    const url = buildUrl(config.endpoints.appointments, state.filters);
    const data = await fetchJson(url);
    state.appointments = data?.appointments || [];
    renderAppointments(elements.citasTableBody, state.appointments);
    updateAppointmentSummary(elements.citasSummary, data?.summary || {});
  } catch (error) {
    console.error('Error loading appointments', error);
    flashMessage(elements.alert, 'error', 'No fue posible cargar las citas.');
  }
}

async function updateAppointmentStatus(id, status, state, config, elements) {
  const url = config.endpoints.updateAppointment.replace('__ID__', id);
  try {
    const response = await fetch(url, { method: 'PATCH', headers: buildHeaders(config.csrfToken, true), body: JSON.stringify({ estado: status }), credentials: 'same-origin' });
    if (!response.ok) throw new Error(await extractError(response));
    await response.json();
    flashMessage(elements.alert, 'success', 'Estado actualizado.');
    loadAppointments(state, config, elements);
    refreshSummary(config, elements);
  } catch (error) {
    console.error('Error updating appointment status', error);
    flashMessage(elements.alert, 'error', error.message || 'No se pudo actualizar la cita.');
  }
}

async function generateAppointmentsReport(state, config, elements) {
  const url = buildUrl(config.endpoints.report, state.filters);
  try {
    const response = await fetch(url, { method: 'GET', credentials: 'same-origin' });
    if (!response.ok) throw new Error(await extractError(response));
    const blob = await response.blob();
    const filename = getFilenameFromHeaders(response.headers) || 'reporte_citas.txt';
    downloadBlob(blob, filename);
  } catch (error) {
    console.error('Error generating report', error);
    flashMessage(elements.alert, 'error', 'No fue posible generar el reporte.');
  }
}

function handleQuickAction(action, state, config, elements) {
  switch (action) {
    case 'generate-report':
      generateAppointmentsReport(state, config, elements);
      break;
    case 'clean-data':
      flashMessage(elements.alert, 'error', 'Limpieza masiva deshabilitada por seguridad.');
      break;
    case 'export-data':
      window.location.href = config.endpoints.exportData;
      break;
    case 'configure':
      flashMessage(elements.alert, 'success', 'Modulo de configuracion en desarrollo.');
      break;
    default: break;
  }
}

function renderUserTypeChart(canvas, distribution) {
  if (!canvas || !window.Chart) return;
  const ctx = canvas.getContext('2d');
  const existing = canvas.__chartInstance; if (existing) existing.destroy();
  const labels = distribution?.labels || ['Tutores', 'Veterinarios'];
  const values = distribution?.data || [0, 0];
  canvas.__chartInstance = new Chart(ctx, { type: 'doughnut', data: { labels, datasets: [{ data: values, backgroundColor: ['#007bff', '#dc3545'], borderWidth: 3, borderColor: '#ffffff' }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
}

function renderMonthlyChart(canvas, charts, monthly) {
  if (!canvas || !window.Chart) return;
  const labels = (monthly || []).map(item => item.label);
  const data = (monthly || []).map(item => item.value);
  charts.monthly?.destroy?.();
  charts.monthly = new Chart(canvas.getContext('2d'), { type: 'line', data: { labels, datasets: [{ label: 'Nuevos usuarios', data, borderColor: '#f28c28', backgroundColor: 'rgba(242, 140, 40, 0.15)', tension: 0.4, fill: true }] }, options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } });
}

function renderSpeciesChart(canvas, charts, species) {
  if (!canvas || !window.Chart) return;
  const labels = (species || []).map(item => item.label);
  const data = (species || []).map(item => item.value);
  charts.species?.destroy?.();
  charts.species = new Chart(canvas.getContext('2d'), { type: 'bar', data: { labels, datasets: [{ label: 'Cantidad', data, backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545', '#6f42c1'] }] }, options: { responsive: true, scales: { y: { beginAtZero: true } } } });
}

function renderActivityChart(canvas, charts, activity) {
  if (!canvas || !window.Chart) return;
  const labels = activity?.labels || [];
  const registrations = activity?.registrations || [];
  const appointments = activity?.appointments || [];
  charts.activity?.destroy?.();
  charts.activity = new Chart(canvas.getContext('2d'), { type: 'bar', data: { labels, datasets: [{ label: 'Registros', data: registrations, backgroundColor: '#f28c28' }, { label: 'Citas', data: appointments, backgroundColor: '#007bff' }] }, options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } });
}

function renderActivities(container, activities) {
  if (!container) return;
  if (!activities.length) { container.innerHTML = '<div class="empty-cell">Sin actividades registradas</div>'; return; }
  container.innerHTML = activities.map(item => `
    <div class="activity-item">
      <div class="activity-icon"><i class="${escapeHtml(item.icon || 'fas fa-info-circle')}"></i></div>
      <div>
        <strong>${escapeHtml(item.message || 'Actividad')}</strong>
        <div class="activity-time">${escapeHtml(item.time || '')}</div>
      </div>
    </div>`).join('');
}

function renderUsers(tbody, users) {
  if (!tbody) return;
  if (!users.length) { tbody.innerHTML = '<tr><td colspan="7" class="empty-cell">Sin informacion disponible</td></tr>'; return; }
  tbody.innerHTML = users.map(user => {
    const badgeClass = user.tipo === 'Veterinario' ? 'veterinario' : 'tutor';
    return `
      <tr>
        <td>${escapeHtml(user.nombre)}</td>
        <td>${escapeHtml(user.email)}</td>
        <td><span class="badge ${badgeClass}">${escapeHtml(user.tipo)}</span></td>
        <td>${escapeHtml(user.detalle)}</td>
        <td>${escapeHtml(user.especialidad)}</td>
        <td>${escapeHtml(user.fecha_registro)}</td>
        <td><span class="badge ${badgeClass}">${escapeHtml(user.estado)}</span></td>
      </tr>`;
  }).join('');
}

function renderAppointments(tbody, appointments) {
  if (!tbody) return;
  if (!appointments.length) { tbody.innerHTML = '<tr><td colspan="7" class="empty-cell">Sin citas registradas</td></tr>'; return; }
  const todayIso = formatDateISO(new Date());
  tbody.innerHTML = appointments.map(cita => {
    const badgeClass = `estado-${cita.estado || 'pendiente'}`;
    const dateIso = cita.fecha_iso || '';
    const isToday = dateIso === todayIso;
    const isPast = dateIso && dateIso < todayIso;
    const actions = buildAppointmentActions(cita, isPast);
    return `
      <tr${isToday ? ' class="appointment-today"' : ''}>
        <td><strong>${escapeHtml(cita.fecha)}</strong></td>
        <td>${escapeHtml(cita.mascota)}</td>
        <td>${escapeHtml(cita.tutor)}<br><small>${escapeHtml(cita.tutor_email)}</small></td>
        <td>${escapeHtml(cita.veterinario || 'Sin asignar')}</td>
        <td>${escapeHtml(cita.especialidad || 'Sin especificar')}</td>
        <td><span class="badge ${badgeClass}">${formatEstado(cita.estado)}</span></td>
        <td><div class="table-actions">${actions}</div></td>
      </tr>`;
  }).join('');
}

function buildAppointmentActions(cita, isPast) {
  const id = cita.id; const estado = cita.estado || 'pendiente'; const buttons = [];
  if (estado === 'pendiente') buttons.push(`<button class="confirm" data-action="confirm" data-id="${id}">Confirmar</button>`);
  if (estado !== 'cancelada' && estado !== 'completada') buttons.push(`<button class="cancel" data-action="cancel" data-id="${id}">Cancelar</button>`);
  if (isPast && estado !== 'cancelada' && estado !== 'completada') buttons.push(`<button class="complete" data-action="complete" data-id="${id}">Completar</button>`);
  return buttons.join('');
}

function updateAppointmentSummary(summaryElements, summary) {
  updateText(summaryElements.hoy, summary.citas_hoy ?? 0);
  updateText(summaryElements.semana, summary.citas_semana ?? 0);
  updateText(summaryElements.confirmadas, summary.confirmadas ?? 0);
  updateText(summaryElements.pendientes, summary.pendientes ?? 0);
}

function formatEstado(estado) {
  const map = { pendiente: 'Pendiente', confirmada: 'Confirmada', completada: 'Completada', cancelada: 'Cancelada' };
  return map[estado] || 'Pendiente';
}

function showSection(sectionId, sections) {
  sections.forEach(section => { section.classList.toggle('active', section.id === sectionId); });
}

function downloadBlob(blob, filename) {
  const url = URL.createObjectURL(blob); const anchor = document.createElement('a'); anchor.href = url; anchor.download = filename; document.body.appendChild(anchor); anchor.click(); document.body.removeChild(anchor); URL.revokeObjectURL(url);
}

function buildUrl(base, params) {
  const url = new URL(base, window.location.origin);
  if (params) Object.entries(params).forEach(([k, v]) => { if (v) url.searchParams.set(k, v); else url.searchParams.delete(k); });
  return url.toString();
}

async function fetchJson(url, options = {}) {
  const response = await fetch(url, { headers: buildHeaders(null, false), credentials: 'same-origin', ...options });
  if (!response.ok) throw new Error(await extractError(response));
  return response.json();
}

function buildHeaders(csrfToken, withJson) {
  const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
  if (withJson) headers['Content-Type'] = 'application/json';
  if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;
  return headers;
}

async function extractError(response) {
  try {
    const data = await response.json();
    if (data?.message) {
      if (data.errors) {
        const messages = Object.values(data.errors).flat();
        if (messages.length) return messages.join(' ');
      }
      return data.message;
    }
  } catch (error) {}
  return `Error ${response.status}`;
}

function flashMessage(container, type, message) {
  if (!container) return;
  container.className = `alert-message alert-${type}`;
  container.textContent = message;
  container.style.display = 'block';
  clearTimeout(flashMessage.timeout);
  flashMessage.timeout = setTimeout(() => { container.style.display = 'none'; }, 4000);
}

function updateText(element, value) { if (element) element.textContent = Number(value || 0); }

function escapeHtml(value) {
  if (value === null || value === undefined) return '';
  return String(value).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function formatDateISO(date) { const y = date.getFullYear(); const m = String(date.getMonth()+1).padStart(2,'0'); const d = String(date.getDate()).padStart(2,'0'); return `${y}-${m}-${d}`; }

function getFilenameFromHeaders(headers) { const disp = headers.get('Content-Disposition'); if (!disp) return null; const match = disp.match(/filename="?([^";]+)"?/); return match ? match[1] : null; }




