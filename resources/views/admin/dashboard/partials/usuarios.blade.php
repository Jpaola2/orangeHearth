<section id="usuarios" class="dashboard-section">
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
        </tr>
      </thead>
      <tbody id="usersTableBody">
        <tr>
          <td colspan="7" class="empty-cell">Sin informacion disponible</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>