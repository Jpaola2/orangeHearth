<section id="usuarios" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-users"></i> Administrar usuarios</h3>
  <p>Visualiza y gestiona todos los usuarios registrados en el sistema.</p>

  <div class="actions-row">
    <button class="btn" type="button" data-action="refresh-users"><i class="fas fa-sync"></i> Actualizar lista</button>
    <button class="btn btn-secondary" type="button" data-action="export-users"><i class="fas fa-file-excel"></i> Exportar Excel</button>
    <button class="btn btn-secondary" type="button" data-action="export-pdf" data-role=""><i class="fas fa-file-pdf"></i> Exportar Todos (PDF)</button>
    <button class="btn btn-secondary" type="button" data-action="export-pdf" data-role="tutor"><i class="fas fa-file-pdf"></i> Exportar Tutores (PDF)</button>
    <button class="btn btn-secondary" type="button" data-action="export-pdf" data-role="vet"><i class="fas fa-file-pdf"></i> Exportar Veterinarios (PDF)</button>
  </div>

  <div class="filters-panel">
    <h4>Filtros</h4>
    <div class="filters-grid">
      <label>
        <span>Tipo de usuario</span>
        <select id="filtroTipoUsuario">
          <option value="">Todos</option>
          <option value="tutor">Tutores</option>
          <option value="vet">Veterinarios</option>
        </select>
      </label>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="data-table" id="usersTable">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Tipo</th>
          <th>Tarjeta/Mascotas</th>
          <th>Especialidad</th>
          <th>Fecha registro</th>
          <th style="width:156px;">Acciones</th>
        </tr>
      </thead>
      <tbody id="usersTableBody">
        <tr>
          <td colspan="8" class="empty-cell">Sin información disponible</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<!-- Estilos mínimos para acciones -->
<style>
  .actions-cell { display:flex; gap:8px; align-items:center; justify-content:flex-start; margin-top: 22px; }
  .btn-icon {
    display:inline-flex; align-items:center; justify-content:center;
    padding:.35rem .6rem; font-size:.85rem; line-height:1; border-radius:8px;
    border:1px solid #e5e7eb; background:#fff; cursor:pointer;
  }
  .btn-icon span.emoji { margin-right:.35rem; display:inline-block; }
  .btn-icon.btn-danger { background:#fff5f5; border-color:#fecaca; }
  .btn-icon:hover { filter:brightness(0.98); }
  .form-group { display:block; margin:8px 0; }
  .form-group span { display:block; margin-bottom:4px; font-size: .85rem; color: #333; }
  .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; }
  .small-muted { display:block; margin-top:4px; font-size:.8rem; color:#666; }
  .card { border:1px solid #eee; border-radius:10px; padding:12px; }
</style>

<!-- Modal Editar -->
<div id="editUserModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); align-items:center; justify-content:center; z-index:9999;">
  <div style="background:#fff; padding:16px; width:420px; max-width:92vw; border-radius:12px;">
    <h4 style="margin:0 0 8px;"><i class="fas fa-user-edit"></i> Editar usuario</h4>
    <form id="editUserForm">
      @csrf
      @method('PATCH')
      <input type="hidden" name="id" id="editUserId">
      <input type="hidden" name="__role" id="editUserRole"> <!-- role para lógica del front -->

      <div id="edit-form-fields"></div>

      <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:12px;">
        <button type="button" class="btn btn-secondary" id="btnEditCancel">Cancelar</button>
        <button type="submit" class="btn">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  // Backend
  const USERS_URL       = "{{ route('admin.users') }}";
  const DETAILS_URL     = id => `{{ url('/admin/users') }}/${id}/details`;
  const EXPORT_URL      = "{{ route('admin.users.export') }}";
  const PDF_EXPORT_URL  = "{{ route('admin.users.report.pdf') }}";
  const UPDATE_URL      = id => `{{ url('/admin/users') }}/${id}`;
  const DELETE_URL      = id => `{{ url('/admin/users') }}/${id}`;
  const CSRF_TOKEN      = "{{ csrf_token() }}";

  // UI
  const SECTION_ID    = 'usuarios';
  const $tbody        = document.getElementById('usersTableBody');
  const $btnRefresh   = document.querySelector('[data-action="refresh-users"]');
  const $btnExport    = document.querySelector('[data-action="export-users"]');
  const $fTipoUsuario = document.getElementById('filtroTipoUsuario');

  // Modal
  const $modal      = document.getElementById('editUserModal');
  const $form       = document.getElementById('editUserForm');
  const $formFields = document.getElementById('edit-form-fields');
  const $btnCancel  = document.getElementById('btnEditCancel');
  const $fieldId    = document.getElementById('editUserId');
  const $fieldRole  = document.getElementById('editUserRole');

  // Helpers
  function setEmptyRow(text) {
    $tbody.innerHTML = `<tr><td colspan="8" class="empty-cell">${text}</td></tr>`;
  }
  function badge(text, tone) {
    const bg = tone === 'success' ? '#e6f7ef' : (tone === 'danger' ? '#fff0f0' : '#e8f1ff');
    const fg = tone === 'success' ? '#1ea672' : (tone === 'danger' ? '#cc2e2e' : '#2a6df4');
    return `<span style="display:inline-block;padding:.15rem .5rem;border-radius:999px;background:${bg};color:${fg};font-size:.75rem;font-weight:600">${text}</span>`;
  }

  function renderRows(rows) {
    if (!rows || !rows.length) return setEmptyRow('Sin información disponible');

    $tbody.innerHTML = rows.map(u => {
      const isVet = (u.tipo || '').toLowerCase().includes('veterinario');
      return `
        <tr data-user-id="${u.id ?? ''}" data-user-role="${isVet ? 'vet' : 'tutor'}">
          <td>${u.nombre || '-'}</td>
          <td>${u.email  || '-'}</td>
          <td>${badge(u.tipo || 'Tutor', isVet ? 'success' : 'info')}</td>
          <td>${u.detalle ?? '-'}</td>
          <td>${u.especialidad ?? '-'}</td>
          <td>${u.fecha_registro ?? '-'}</td>

          <td class="actions-cell">
            <button type="button" class="btn-icon" data-action="edit" title="Editar">
              <span class="emoji">📝</span> Editar
            </button>
            <button type="button" class="btn-icon btn-danger" data-action="delete" title="Eliminar">
              <span class="emoji">🗑️</span> Eliminar
            </button>
          </td>
        </tr>
      `;
    }).join('');
  }

  function getQueryString() {
    const params = new URLSearchParams();
    const role = $fTipoUsuario.value;
    if (role) params.set('role', role);
    return params.toString();
  }

  // Carga (sin caché del navegador)
  async function loadUsers() {
    setEmptyRow('Cargando…');
    const queryString = getQueryString();
    const url = USERS_URL + (queryString ? '?' + queryString : '');

    try {
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
      });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const json = await res.json();
      renderRows(json.users || []);
    } catch (e) {
      console.error(e);
      setEmptyRow('No se pudo cargar la lista. Intenta nuevamente.');
    }
  }

  // Exportar
  function exportUsers() {
    const queryString = getQueryString();
    window.location.href = EXPORT_URL + (queryString ? '?' + queryString : '');
  }
  function exportUsersPdf(role) {
    const params = new URLSearchParams();
    if (role) params.set('role', role);
    const queryString = params.toString();
    window.location.href = PDF_EXPORT_URL + (queryString ? '?' + queryString : '');
  }
  document.querySelectorAll('[data-action="export-pdf"]').forEach(btn => {
    btn.addEventListener('click', () => { exportUsersPdf(btn.dataset.role); });
  });

  // Delegación de eventos (editar/eliminar)
  $tbody.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('button[data-action]');
    if (!btn) return;

    const tr   = btn.closest('tr');
    const id   = tr?.dataset.userId;
    const role = tr?.dataset.userRole;

    if (!id) return;

    if (btn.dataset.action === 'edit') {
      openEditModal(id, role);
    }

    if (btn.dataset.action === 'delete') {
      const name = tr.cells[0].textContent;
      const ok = confirm(`¿Eliminar a "${name}"? Esta acción no se puede deshacer.`);
      if (!ok) return;

      try {
        const res = await fetch(DELETE_URL(id), {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
          cache: 'no-store'
        });
        if (res.status === 409) {
          const j = await res.json();
          alert(j.message || 'No se puede eliminar por relaciones existentes.');
          return;
        }
        if (!res.ok) throw new Error('HTTP '+res.status);
        await loadUsers();
      } catch (e) {
        console.error(e);
        alert('Ocurrió un error al eliminar.');
      }
    }
  });

  // --- Modal ---
  async function openEditModal(userId, role) {
    try {
      const res = await fetch(DETAILS_URL(userId), { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('HTTP '+res.status);
      const user = await res.json();

      $fieldId.value   = user.id;
      $fieldRole.value = role; // guardar rol para el submit

      let fieldsHtml = `
        <label class="form-group"><span>Nombre</span><input type="text" name="name" value="${user.name || ''}" required></label>
        <label class="form-group"><span>Email</span><input type="email" name="email" value="${user.email || ''}" required></label>
      `;

      if (role === 'tutor' && user.tutor) {
        fieldsHtml += `
          <label class="form-group"><span>Cédula</span><input type="text" name="ced_tutor" value="${user.tutor.ced_tutor || ''}" required></label>
          <label class="form-group"><span>Teléfono</span><input type="text" name="tel_tutor" value="${user.tutor.tel_tutor || ''}" required></label>
          <label class="form-group"><span>Dirección</span><input type="text" name="direc_tutor" value="${user.tutor.direc_tutor || ''}" required></label>
        `;
      } else if (role === 'vet' && user.vet) {
        fieldsHtml += `
          <label class="form-group"><span>Cédula</span><input type="text" name="cedu_mv" value="${user.vet.cedu_mv || ''}" required></label>
          <label class="form-group"><span>Tarjeta Profesional</span><input type="text" name="tarjeta_profesional_mv" value="${user.vet.tarjeta_profesional_mv || ''}" required></label>
          <label class="form-group"><span>Especialidad</span><input type="text" name="especialidad" value="${user.vet.especialidad || ''}" required></label>
          <label class="form-group"><span>Teléfono</span><input type="text" name="telefono" value="${user.vet.telefono || ''}" required></label>

          <!-- SOLO VETERINARIO: CAMBIO DE CONTRASEÑA -->
          <div class="card" style="margin-top:10px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:6px;">
              <input type="checkbox" id="toggleChangePassword">
              <span>Cambiar contraseña (solo veterinario)</span>
            </label>
            <div id="vetPasswordFields" style="display:none;">
              <label class="form-group">
                <span>Nueva contraseña</span>
                <input type="password" name="password" id="editPassword" minlength="8" autocomplete="new-password" disabled>
              </label>
              <label class="form-group">
                <span>Confirmar contraseña</span>
                <input type="password" name="password_confirmation" id="editPasswordConfirm" minlength="8" autocomplete="new-password" disabled>
              </label>
              <small class="small-muted">Déjalas vacías si no deseas cambiarla.</small>
            </div>
          </div>
        `;
      }

      $formFields.innerHTML = fieldsHtml;
      $modal.style.display = 'flex';

      // Wire del toggle (si existe)
      const $toggle = document.getElementById('toggleChangePassword');
      const $pwdBox = document.getElementById('vetPasswordFields');
      const $pwd    = document.getElementById('editPassword');
      const $pwd2   = document.getElementById('editPasswordConfirm');

      if ($toggle) {
        $toggle.addEventListener('change', () => {
          const on = $toggle.checked;
          if ($pwdBox) $pwdBox.style.display = on ? 'block' : 'none';
          if ($pwd)  $pwd.disabled  = !on, $pwd.value  = on ? $pwd.value : '';
          if ($pwd2) $pwd2.disabled = !on, $pwd2.value = on ? $pwd2.value : '';
        });
      }
    } catch (e) {
      console.error(e);
      alert('No se pudieron cargar los detalles del usuario.');
    }
  }

  function closeEditModal() { $modal.style.display = 'none'; }
  $btnCancel.addEventListener('click', closeEditModal);
  $modal.addEventListener('click', (e) => { if (e.target === $modal) closeEditModal(); });

  // Guardar cambios (PATCH)
  $form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id   = $fieldId.value;
    const role = $fieldRole.value;

    // Si no es vet, por si acaso elimina campos de password del form (no deben enviarse)
    if (role !== 'vet') {
      const pw  = $form.querySelector('input[name="password"]');
      const pw2 = $form.querySelector('input[name="password_confirmation"]');
      if (pw)  pw.parentElement.removeChild(pw);
      if (pw2) pw2.parentElement.removeChild(pw2);
    } else {
      // Si es vet pero el toggle está apagado, deshabilitados => no se envían en FormData
      // (ya lo manejamos con disabled en el toggle)
    }

    const payload = new FormData($form);

    try {
      const res = await fetch(UPDATE_URL(id), {
        method: 'POST', // Laravel: POST + _method=PATCH
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        body: payload,
        cache: 'no-store'
      });

      if (res.status === 422) {
        const j = await res.json();
        alert(Object.values(j.errors || {}).flat().join('\n') || 'Validación fallida.');
        return;
      }
      if (!res.ok) throw new Error('HTTP '+res.status);

      closeEditModal();
      await loadUsers();
    } catch (e) {
      console.error(e);
      alert('No se pudo guardar los cambios.');
    }
  });

  // Botones
  if ($btnRefresh) $btnRefresh.addEventListener('click', loadUsers);
  if ($btnExport)  $btnExport.addEventListener('click', exportUsers);
  $fTipoUsuario.addEventListener('change', loadUsers);

  // Integración con tu adminShowSection
  const _oldAdminShowSection = window.adminShowSection;
  window.adminShowSection = function(id) {
    if (typeof _oldAdminShowSection === 'function') _oldAdminShowSection(id);
    if (id === SECTION_ID) loadUsers();
  };

  loadUsers();
})();
</script>
