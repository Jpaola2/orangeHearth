@extends('layouts.app')
@section('content')
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <h2 class="text-xl">Mis Mascotas</h2>
    <a class="btn" href="{{ route('tutor.mascotas.create') }}">Añadir Mascota</a>
  </div>

  @if(session('ok')) <div class="card" style="background:#ecfdf5;margin-bottom:12px">{{ session('ok') }}</div> @endif

  @forelse($mascotas as $m)
    <div class="card" style="margin-bottom:10px">
      <strong>{{ $m->nom_masc }}</strong>
      <div style="color:#475569">Especie: {{ ucfirst($m->espe_masc) }} · Género: {{ ucfirst($m->gene_masc) }}</div>
      <div style="margin-top:8px;display:flex;gap:8px">
        <a class="btn" href="{{ route('tutor.mascotas.edit',$m->id_masc) }}">Editar</a>
        <form method="POST" action="{{ route('tutor.mascotas.destroy',$m->id_masc) }}" onsubmit="return confirm('¿Eliminar mascota?')">
          @csrf @method('DELETE')
          <button class="btn btn-outline">Eliminar</button>
        </form>
      </div>
    </div>
  @empty
    <p>No tienes mascotas aún.</p>
  @endforelse
</div>
@endsection
