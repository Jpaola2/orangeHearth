@extends('layouts.admin')

@php
use Illuminate\Support\Facades\Route;

// Endpoints obligatorios (existen sí o sí)
$endpoints = [
  'summary'            => route('admin.summary'),
  'users'              => route('admin.users'),
  'appointments'       => route('admin.appointments'),
  'updateAppointment'  => route('admin.appointments.update-status', ['cita' => '__ID__']),
  'exportAppointments' => route('admin.appointments.export'),
  'exportUsers'        => route('admin.users.export'),
  'storeVet'           => route('admin.veterinarios.store'),
  'storeAdmin'         => route('admin.admins.store'),
];

// Endpoints opcionales (solo si la ruta existe)
if (Route::has('admin.statistics'))   { $endpoints['statistics']   = route('admin.statistics'); }
if (Route::has('admin.activities'))   { $endpoints['activities']   = route('admin.activities'); }
if (Route::has('admin.data.export'))  { $endpoints['exportData']   = route('admin.data.export'); }
if (Route::has('admin.report.generate')) { $endpoints['report']   = route('admin.report.generate'); }

$dashboardConfig = [
  'csrfToken'   => csrf_token(),
  'endpoints'   => $endpoints, // <- ya sin 'rescheduleAppointment'
  'redirects'   => ['dashboard' => route('admin.dashboard')],
  'veterinarios'=> $veterinarios,
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
      if (target) target.style.display = 'block';
    };

    // Al cargar la página, mostrar solo la primera (ej: "resumen")
    document.addEventListener('DOMContentLoaded', () => {
      window.adminShowSection('resumen');
    });
  </script>
@endsection
