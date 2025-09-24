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
    'veterinarios' => $veterinarios,
  ];
@endphp

@section('title', 'Administrador - Orange Hearth')

@section('body')
  @include('admin.dashboard.partials.header')
  <div class="dashboard-container" id="admin-dashboard" data-config="@json($dashboardConfig)">
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
@endsection