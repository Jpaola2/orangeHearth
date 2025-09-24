<section id="resumen" class="dashboard-section active">
  <h3><i class="fas fa-chart-pie"></i> Resumen general del sistema</h3>
  <div class="stats-grid">
    <div class="stat-card usuarios">
      <div class="stat-number" id="totalUsuarios">{{ data_get($preload, 'totals.usuarios', 0) }}</div>
      <div class="stat-label">Usuarios registrados</div>
    </div>
    <div class="stat-card mascotas">
      <div class="stat-number" id="totalMascotas">{{ data_get($preload, 'totals.mascotas', 0) }}</div>
      <div class="stat-label">Mascotas registradas</div>
    </div>
    <div class="stat-card citas">
      <div class="stat-number" id="totalCitas">{{ data_get($preload, 'totals.citas', 0) }}</div>
      <div class="stat-label">Citas agendadas</div>
    </div>
    <div class="stat-card veterinarios">
      <div class="stat-number" id="totalVeterinarios">{{ data_get($preload, 'totals.veterinarios', 0) }}</div>
      <div class="stat-label">Veterinarios activos</div>
    </div>
  </div>

  <!-- Panel de bienvenida decorativo -->
  <div class="welcome-panel">
    <div class="welcome-content">
      <div class="welcome-header">
        <i class="fas fa-heart"></i>
        <h4 id="dynamicWelcome">Bienvenido al Panel de Administración de OrangeHearth</h4>
      </div>
      <div class="welcome-body">
        <p>Gestiona de manera eficiente todos los aspectos de tu clínica veterinaria desde esta plataforma integral.</p>
        <div class="welcome-features">
          <div class="feature-item">
            <i class="fas fa-users"></i>
            <span>Administrar usuarios y perfiles</span>
          </div>
          <div class="feature-item">
            <i class="fas fa-calendar-check"></i>
            <span>Control total de citas y agenda</span>
          </div>
          <!-- <div class="feature-item">
            <i class="fas fa-chart-line"></i>
            <span>Estadísticas y reportes detallados</span>
          </div> -->
          <div class="feature-item">
            <i class="fas fa-paw"></i>
            <span>Seguimiento de mascotas y tratamientos</span>
          </div>
        </div>
      </div>
    </div>
    <div class="welcome-decoration">
      <div class="decoration-circle circle-1"></div>
      <div class="decoration-circle circle-2"></div>
      <div class="decoration-circle circle-3"></div>
    </div>
  </div>
</section>

<style>
/* Estilos para el panel de bienvenida */
.welcome-panel {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 1px solid #dee2e6;
  border-radius: 15px;
  padding: 2rem;
  margin-top: 2rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.welcome-content {
  position: relative;
  z-index: 2;
}

.welcome-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.welcome-header i {
  font-size: 1.5rem;
  color: #f28c28;
}

.welcome-header h4 {
  color: #495057;
  margin: 0;
  font-size: 1.4rem;
  font-weight: 600;
}

.welcome-body p {
  color: #6c757d;
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.welcome-features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.7);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.feature-item:hover {
  background: rgba(242, 140, 40, 0.1);
  transform: translateY(-2px);
}

.feature-item i {
  font-size: 1.2rem;
  color: #f28c28;
  width: 20px;
  text-align: center;
}

.feature-item span {
  color: #495057;
  font-weight: 500;
}

/* Decoración de fondo */
.welcome-decoration {
  position: absolute;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.decoration-circle {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(45deg, rgba(242, 140, 40, 0.1), rgba(216, 111, 0, 0.05));
}

.circle-1 {
  width: 120px;
  height: 120px;
  top: -30px;
  right: -30px;
}

.circle-2 {
  width: 80px;
  height: 80px;
  top: 60px;
  right: 120px;
}

.circle-3 {
  width: 60px;
  height: 60px;
  bottom: -20px;
  right: 80px;
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-features {
    grid-template-columns: 1fr;
  }
  
  .welcome-panel {
    padding: 1.5rem;
  }
  
  .welcome-header h4 {
    font-size: 1.2rem;
  }
}
</style>

<script>
// Función para generar saludo dinámico según la hora
function updateWelcomeMessage() {
  const now = new Date();
  const hour = now.getHours();
  let greeting;
  let icon;

  if (hour >= 5 && hour < 12) {
    greeting = "Buenos días";
    icon = "fas fa-sun";
  } else if (hour >= 12 && hour < 18) {
    greeting = "Buenas tardes";
    icon = "fas fa-cloud-sun";
  } else {
    greeting = "Buenas noches";
    icon = "fas fa-moon";
  }

  // Actualizar el contenido del header
  const welcomeHeader = document.querySelector('.welcome-header');
  if (welcomeHeader) {
    welcomeHeader.innerHTML = `
      <i class="${icon}"></i>
      <h4>${greeting}, bienvenido a la gestión del administrador</h4>
    `;
  }
}

// Ejecutar cuando cargue la página
document.addEventListener('DOMContentLoaded', updateWelcomeMessage);

// Actualizar cada minuto por si cambia la hora
setInterval(updateWelcomeMessage, 60000);
</script>