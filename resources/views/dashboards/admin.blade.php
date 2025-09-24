@extends('layouts.app')
@section('content')
<div class="card">
  <h1>Panel Administrador</h1>
  <p>Hola, {{ auth()->user()->name ?? 'Admin' }}</p>
  <form method="POST" action="{{ route('logout') }}">@csrf <button class="btn-outline">Cerrar sesión</button></form>
</div>
@endsection
