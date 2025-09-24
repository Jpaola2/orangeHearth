@extends('layouts.app')
@section('content')
<div class="card">
  <h1>Dashboard Tutor</h1>
  <p>Bienvenido, {{ auth()->user()->name ?? 'Tutor' }}</p>
  <a class="btn" href="{{ route('tutor.mascotas.index') }}">Mis Mascotas</a>
  <form method="POST" action="{{ route('logout') }}" style="margin-top:12px;">@csrf <button class="btn-outline">Cerrar sesión</button></form>
</div>
@endsection
