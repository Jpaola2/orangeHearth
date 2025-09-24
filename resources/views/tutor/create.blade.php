@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
  <h1 class="text-xl font-bold mb-4">Añadir Mascota</h1>
  <form method="POST" action="{{ route('tutor.mascotas.store') }}">
    @include('tutor.mascotas._form')
  </form>
</div>
@endsection
