@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Mis Mascotas</h1>
    <a href="{{ route('tutor.mascotas.create') }}" class="px-4 py-2 rounded bg-orange-500 text-white">Nueva</a>
  </div>

  @if(session('ok'))
    <div class="mb-3 p-3 bg-green-100 rounded">{{ session('ok') }}</div>
  @endif

  <div class="bg-white rounded shadow divide-y">
    @forelse($mascotas as $m)
      <div class="p-4 flex items-center justify-between">
        <div>
          <div class="font-semibold">{{ $m->nom_masc }}</div>
          <div class="text-sm text-gray-600">{{ ucfirst($m->espe_masc) }} · {{ ucfirst($m->gene_masc) }}</div>
        </div>
        <div class="flex gap-2">
          <a class="px-3 py-1 rounded bg-blue-500 text-white" href="{{ route('tutor.mascotas.edit',$m->id_masc) }}">Editar</a>
          <form method="POST" action="{{ route('tutor.mascotas.destroy',$m->id_masc) }}" onsubmit="return confirm('¿Eliminar?')">
            @csrf @method('DELETE')
            <button class="px-3 py-1 rounded bg-red-500 text-white">Borrar</button>
          </form>
        </div>
      </div>
    @empty
      <div class="p-6 text-center text-gray-500">Aún no tienes mascotas.</div>
    @endforelse
  </div>
</div>
@endsection
