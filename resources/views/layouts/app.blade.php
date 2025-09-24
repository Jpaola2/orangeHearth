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

  <div class="chart-container chart-container--donut card">
    <div class="chart-title">
      <h4>Distribución de usuarios por tipo</h4>
    </div>
    <div class="chart-wrapper">
      <canvas id="userTypeChart"></canvas>
    </div>
  </div>
</section>

@push('styles')
<style>
.chart-container.chart-container--donut {
  margin-top: 18px;
}

.chart-wrapper {
  width: 100%;
  height: 420px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  box-sizing: border-box;
}

canvas#userTypeChart {
  width: 100% !important;
  height: 100% !important;
  max-width: 700px;
}
</style>
@endpush

@push('scripts')
<!-- Importa Chart.js antes del script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById('userTypeChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // Datos desde $preload (asegúrate de que existan en el controlador)
    const admins = Number({{ data_get($preload, 'charts.usuarios.admins', 0) }});
    const vets   = Number({{ data_get($preload, 'charts.usuarios.veterinarios', 0) }});
    const tutors = Number({{ data_get($preload, 'charts.usuarios.tutores', 0) }});
    const data = [admins, vets, tutors];

    // Si todos los valores son 0, no intenta renderizar (evita errores)
    if (data.reduce((a, b) => a + b, 0) === 0) {
        console.warn("No hay datos para mostrar en la gráfica.");
        return;
    }

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Administradores', 'Veterinarios', 'Tutores'],
            datasets: [{
                data: data,
                backgroundColor: ['#ff6384', '#36a2eb', '#ffce56'],
                borderColor: '#ffffff',
                borderWidth: 4,
                hoverOffset: 18
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        color: '#333',
                        font: { size: 13 }
                    }
                },
                tooltip: {
                    padding: 8
                }
            }
        }
    });
});
</script>
@endpush
