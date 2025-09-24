@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
  <h1 class="text-xl font-bold mb-4">Editar Mascota</h1>
  <form method="POST" action="{{ route('tutor.mascotas.update',$mascota->id_masc) }}">
    @method('PUT')
    @include('tutor.mascotas._form', ['mascota'=>$mascota])
  </form>
</div>
@endsection
