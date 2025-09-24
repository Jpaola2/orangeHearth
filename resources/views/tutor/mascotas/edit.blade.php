@extends('layouts.app')
@section('content')
<div class="card">
  <h2 class="text-xl" style="margin-bottom:10px">Editar Mascota</h2>
  <form method="POST" action="{{ route('tutor.mascotas.update',$mascota->id_masc) }}">
    @method('PUT')
    @include('tutor.mascotas._form', ['mascota'=>$mascota])
  </form>
</div>
@endsection
