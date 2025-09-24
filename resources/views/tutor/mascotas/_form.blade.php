@csrf
<div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
  <label>Nombre
    <input name="nom_masc" value="{{ old('nom_masc',$mascota->nom_masc ?? '') }}" class="w-full border rounded p-2" required>
    @error('nom_masc')<div style="color:#b91c1c">{{ $message }}</div>@enderror
  </label>

  <label>Especie
    <select name="espe_masc" class="w-full border rounded p-2" required>
      @foreach(['canino','felino','otro'] as $opt)
        <option value="{{ $opt }}" @selected(old('espe_masc',$mascota->espe_masc ?? '')===$opt)>{{ ucfirst($opt) }}</option>
      @endforeach
    </select>
    @error('espe_masc')<div style="color:#b91c1c">{{ $message }}</div>@enderror
  </label>

  <label>Género
    <select name="gene_masc" class="w-full border rounded p-2" required>
      @foreach(['macho','hembra'] as $opt)
        <option value="{{ $opt }}" @selected(old('gene_masc',$mascota->gene_masc ?? '')===$opt)>{{ ucfirst($opt) }}</option>
      @endforeach
    </select>
    @error('gene_masc')<div style="color:#b91c1c">{{ $message }}</div>@enderror
  </label>
</div>

<div style="margin-top:12px;display:flex;gap:8px">
  <button class="btn">Guardar</button>
  <a class="btn btn-outline" href="{{ route('tutor.mascotas.index') }}">Cancelar</a>
</div>
