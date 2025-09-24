@csrf
<div class="grid md:grid-cols-3 gap-4">
  <label class="block">
    <span class="text-sm">Nombre</span>
    <input name="nom_masc" value="{{ old('nom_masc', $mascota->nom_masc ?? '') }}" class="w-full border rounded p-2" required>
    @error('nom_masc')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </label>
  <label class="block">
    <span class="text-sm">Especie</span>
    <select name="espe_masc" class="w-full border rounded p-2" required>
      @foreach(['canino','felino','otro'] as $opt)
        <option value="{{ $opt }}" @selected(old('espe_masc', $mascota->espe_masc ?? '')===$opt)>{{ ucfirst($opt) }}</option>
      @endforeach
    </select>
    @error('espe_masc')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </label>
  <label class="block">
    <span class="text-sm">Género</span>
    <select name="gene_masc" class="w-full border rounded p-2" required>
      @foreach(['macho','hembra'] as $opt)
        <option value="{{ $opt }}" @selected(old('gene_masc', $mascota->gene_masc ?? '')===$opt)>{{ ucfirst($opt) }}</option>
      @endforeach
    </select>
    @error('gene_masc')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </label>
</div>
<div class="mt-4 flex gap-2">
  <button class="px-4 py-2 rounded bg-orange-500 text-white">Guardar</button>
  <a href="{{ route('tutor.mascotas.index') }}" class="px-4 py-2 rounded border">Cancelar</a>
</div>
