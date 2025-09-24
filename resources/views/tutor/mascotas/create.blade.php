@extends('layouts.app')
@section('content')
<div class="card">
  <h2 class="text-xl" style="margin-bottom:10px">Añadir Mascota</h2>
  <form method="POST" action="{{ route('tutor.mascotas.store') }}">
    @include('tutor.mascotas._form')
  </form>
</div>
@endsection
