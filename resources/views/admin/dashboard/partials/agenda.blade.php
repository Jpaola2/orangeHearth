<section id="agenda" class="dashboard-section">
  <h3><i class="fas fa-calendar-alt"></i> Gestion de citas del sistema</h3>
  <p>Visualiza y gestiona todas las citas agendadas en el sistema.</p>
  <div class="actions-row">
    <button class="btn" type="button" data-action="refresh-appointments"><i class="fas fa-sync"></i> Actualizar citas</button>
    <button class="btn btn-secondary" type="button" data-action="export-appointments"><i class="fas fa-file-excel"></i> Exportar citas</button>
    <button class="btn btn-secondary" type="button" data-action="show-appointments-report"><i class="fas fa-chart-bar"></i> Estadisticas</button>
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
          <th>Acciones</th>
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