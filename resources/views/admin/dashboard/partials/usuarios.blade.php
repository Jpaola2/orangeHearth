<section id="usuarios" class="dashboard-section" style="display:none;">
  <h3><i class="fas fa-users"></i> Administrar usuarios</h3>
  <p>Visualiza y gestiona todos los usuarios registrados en el sistema.</p>

  <div class="actions-row">
    <button class="btn" type="button" data-action="refresh-users"><i class="fas fa-sync"></i> Actualizar lista</button>
    <button class="btn btn-secondary" type="button" data-action="export-users"><i class="fas fa-file-excel"></i> Exportar Excel</button>
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
          <th>Estado</th>
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
  /* mismo alto/ancho y espaciado agradable */
  .actions-cell { display:flex; gap:8px; align-items:center; justify-content:flex-start; margin-top: 22px; }
  .btn-icon {
    display:inline-flex; align-items:center; justify-content:center;
    padding:.35rem .6rem; font-size:.85rem; line-height:1; border-radius:8px;
    border:1px solid #e5e7eb; background:#fff; cursor:pointer;
  }
  .btn-icon span.emoji { margin-right:.35rem; display:inline-block; }
  .btn-icon.btn-danger { background:#fff5f5; border-color:#fecaca; }
  .btn-icon:hover { filter:brightness(0.98); }
</style>

<!-- Modal Editar -->
<div id="editUserModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); align-items:center; justify-content:center; z-index:9999;">
  <div style="background:#fff; padding:16px; width:420px; max-width:92vw; border-radius:12px;">
    <h4 style="margin:0 0 8px;"><i class="fas fa-user-edit"></i> Editar usuario</h4>
    <form id="editUserForm">
      @csrf
      @method('PATCH')
      <input type="hidden" name="id" id="editUserId">
      <label class="form-group" style="display:block; margin:8px 0;">
        <span>Nombre</span>
        <input type="text" name="name" id="editUserName" required>
      </label>
      <label class="form-group" style="display:block; margin:8px 0;">
        <span>Email</span>
        <input type="email" name="email" id="editUserEmail" required>
      </label>
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
  const USERS_URL   = "{{ route('admin.users') }}";              // GET -> { users: [...] }
  const EXPORT_URL  = "{{ route('admin.users.export') }}";       // GET -> CSV
  const UPDATE_URL  = id => "{{ url('/admin/users') }}/" + id;   // PATCH
  const DELETE_URL  = id => "{{ url('/admin/users') }}/" + id;   // DELETE
  const CSRF_TOKEN  = "{{ csrf_token() }}";

  // UI
  const SECTION_ID  = 'usuarios';
  const $tbody      = document.getElementById('usersTableBody');
  const $btnRefresh = document.querySelector('[data-action="refresh-users"]');
  const $btnExport  = document.querySelector('[data-action="export-users"]');

  // Modal
  const $modal      = document.getElementById('editUserModal');
  const $form       = document.getElementById('editUserForm');
  const $btnCancel  = document.getElementById('btnEditCancel');
  const $fieldId    = document.getElementById('editUserId');
  const $fieldName  = document.getElementById('editUserName');
  const $fieldEmail = document.getElementById('editUserEmail');

  // Helpers
  function setEmptyRow(text) {
    $tbody.innerHTML = `<tr><td colspan="8" class="empty-cell">${text}</td></tr>`;
  }
  function badge(text, tone) {
    const bg = tone === 'success' ? '#e6f7ef' : '#e8f1ff';
    const fg = tone === 'success' ? '#1ea672' : '#2a6df4';
    return `<span style="display:inline-block;padding:.15rem .5rem;border-radius:999px;background:${bg};color:${fg};font-size:.75rem;font-weight:600">${text}</span>`;
  }

  function renderRows(rows) {
    if (!rows || !rows.length) return setEmptyRow('Sin información disponible');

    $tbody.innerHTML = rows.map(u => {
      // backend envía: id, nombre, email, tipo, detalle, especialidad, fecha_registro, estado
      const isVet = (u.tipo || '').toLowerCase().includes('veterinario');
      const nombre = u.nombre ?? '';   // 👈 usamos 'nombre' (no 'name')
      const email  = u.email  ?? '';

      return `
        <tr data-user-id="${u.id ?? ''}" data-user-name="${nombre}" data-user-email="${email}">
          <td>${nombre || '-'}</td>
          <td>${email  || '-'}</td>
          <td>${badge(u.tipo || 'Tutor', isVet ? 'success' : 'info')}</td>
          <td>${u.detalle ?? '-'}</td>
          <td>${u.especialidad ?? '-'}</td>
          <td>${u.fecha_registro ?? '-'}</td>
          <td>${badge(u.estado ?? 'Activo','success')}</td>
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

  // Carga (sin caché del navegador)
  async function loadUsers() {
    setEmptyRow('Cargando…');
    try {
      const res = await fetch(USERS_URL, {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store' // 👈 evita mostrar datos viejos
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
    window.location.href = EXPORT_URL;
  }

  // Delegación de eventos (editar/eliminar)
  $tbody.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('button[data-action]');
    if (!btn) return;

    const tr   = btn.closest('tr');
    const id   = tr?.dataset.userId;
    const name = tr?.dataset.userName || '';
    const mail = tr?.dataset.userEmail || '';

    if (!id) return;

    if (btn.dataset.action === 'edit') {
      openEditModal({ id, name, email: mail });
    }

    if (btn.dataset.action === 'delete') {
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
        await loadUsers(); // 👈 recarga inmediata
      } catch (e) {
        console.error(e);
        alert('Ocurrió un error al eliminar.');
      }
    }
  });

  // Modal
  function openEditModal(user) {
    $fieldId.value    = user.id;
    $fieldName.value  = user.name;   // 👈 aquí sí usamos 'name' porque el formulario PATCH espera 'name'
    $fieldEmail.value = user.email;
    $modal.style.display = 'flex';
  }
  function closeEditModal() { $modal.style.display = 'none'; }
  $btnCancel.addEventListener('click', closeEditModal);
  $modal.addEventListener('click', (e) => { if (e.target === $modal) closeEditModal(); });

  // Guardar cambios (PATCH)
  $form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = $fieldId.value;
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
      await loadUsers(); // 👈 recarga inmediata para ver cambios
    } catch (e) {
      console.error(e);
      alert('No se pudo guardar los cambios.');
    }
  });

  // Botones
  if ($btnRefresh) $btnRefresh.addEventListener('click', loadUsers);
  if ($btnExport)  $btnExport.addEventListener('click', exportUsers);

  // Integración con tu adminShowSection
  const _oldAdminShowSection = window.adminShowSection;
  window.adminShowSection = function(id) {
    if (typeof _oldAdminShowSection === 'function') _oldAdminShowSection(id);
    if (id === SECTION_ID) loadUsers(); // 👈 carga al entrar
  };

  // 👇 Carga inicial (aunque la sección esté oculta) para que al abrir ya esté lista
  loadUsers();
})();
</script>
