@extends('layouts.admin')

@php
  $dashboardConfig = [
    'csrfToken' => csrf_token(),
    'endpoints' => [
      'summary' => route('admin.summary'),
      'statistics' => route('admin.statistics'),
      'activities' => route('admin.activities'),
      'users' => route('admin.users'),
      'appointments' => route('admin.appointments'),
      'updateAppointment' => route('admin.appointments.update-status', ['cita' => '__ID__']),
      'exportAppointments' => route('admin.appointments.export'),
      'exportUsers' => route('admin.users.export'),
      'exportData' => route('admin.data.export'),
      'report' => route('admin.report.generate'),
      'storeVet' => route('admin.veterinarios.store'),
    ],
    'redirects' => [
      'dashboard' => route('admin.dashboard'),
    ],
    'veterinarios' => $veterinarios,
  ];
@endphp

@section('title', 'Administrador - Orange Hearth')

@section('body')
  @include('admin.dashboard.partials.header')
  <div
    class="dashboard-container"
    id="admin-dashboard"
    data-config="@json($dashboardConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)"
    data-preload="@json($preload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)"
  >
    @include('admin.dashboard.partials.sidebar')
    <main class="main-content">
      <div class="alert-message" id="dashboardAlert"></div>
      @include('admin.dashboard.partials.resumen')
      @include('admin.dashboard.partials.estadisticas')
      @include('admin.dashboard.partials.accesos')
      @include('admin.dashboard.partials.actividades')
      @include('admin.dashboard.partials.usuarios')
      @include('admin.dashboard.partials.agenda')
      @include('admin.dashboard.partials.registro-vet')
    </main>
  </div>

  <script>
    // Hacer que sea global para que la use el sidebar
    window.adminShowSection = function (id, event) {
      if (event) event.preventDefault();

      // Ocultar todas las secciones
      document.querySelectorAll('.dashboard-section').forEach(sec => sec.style.display = 'none');

      // Mostrar la seleccionada
      const target = document.getElementById(id);
      if (target) {
        target.style.display = 'block';
      }
    };

    // Al cargar la pagina, mostrar solo la primera (ej: "resumen")
    document.addEventListener('DOMContentLoaded', () => {
      window.adminShowSection('resumen');
    });
  </script>
@endsection






