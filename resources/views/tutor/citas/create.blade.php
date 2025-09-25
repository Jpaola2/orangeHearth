<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agendar Cita</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body{font-family:"Segoe UI",sans-serif;margin:0;background:#f7f7f7;color:#333}
    .wrap{max-width:760px;margin:24px auto;padding:0 16px}
    h1{font-size:22px;margin:0 0 10px;color:#d86f00;display:flex;align-items:center;gap:8px}
    form{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:16px}
    label{display:block;margin:8px 0}
    input,select,button{padding:10px 12px;border:1px solid #ddd;border-radius:10px;height:42px}
    input,select{width:100%}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
    .btn{border-color:#f28c28;background:#fff;color:#d86f00;cursor:pointer}
  </style>
</head>
<body>
  <div class="wrap">
    <h1><i class="fas fa-calendar-plus"></i> Agendar Cita</h1>
    <form id="form" method="POST" action="{{ route('tutor.citas.store') }}">
      @csrf
      <div class="row">
        <label>Fecha
          <input type="date" name="fecha" id="fecha" required
            style="width:100%; padding:10px 12px; height:42px; font-size:14px;
                  border:1px solid #ccc; border-radius:8px; box-sizing:border-box; background:#fff;">
        </label>

        <!-- Motivo -->
        <label>Motivo
          <input type="text" name="motivo" placeholder="Control, Vacunación…" required
            style="width:100%; padding:10px 12px; height:42px; font-size:14px;
                  border:1px solid #ccc; border-radius:8px; box-sizing:border-box;">
        </label>
      </div>

      <div class="row">
        <label>Mascota
          <select name="mascota_id" id="mascota" required>
            <option value="">Seleccione una mascota…</option>
            @foreach($mascotas as $m)
              <option value="{{ $m->id_masc }}" @selected($prefMascota==$m->id_masc)>{{ $m->nom_masc }}</option>
            @endforeach
          </select>
        </label>
        <label>Veterinario disponible
          <select name="medico_id" id="medico" required>
            <option value="">Seleccione una fecha…</option>
          </select>
        </label>
      </div>

      <div class="actions">
        <a class="btn" href="{{ route('tutor.dashboard') }}">Cancelar</a>
        <button type="submit" class="btn">Guardar</button>
      </div>
    </form>
  </div>

  <script>
    const fecha = document.getElementById('fecha');
    const medico = document.getElementById('medico');
    fecha.addEventListener('change', async ()=>{
      medico.innerHTML = '<option value="">Cargando…</option>';
      const f = fecha.value; if(!f){ medico.innerHTML='<option value="">Seleccione una fecha…</option>'; return; }
      try{
        const url = `{{ route('tutor.citas.available-vets') }}?fecha=${encodeURIComponent(f)}`;
        const res = await fetch(url, { headers:{'Accept':'application/json'}, credentials:'same-origin' });
        const j = await res.json();
        const vets = j.veterinarios||[];
        medico.innerHTML = vets.length ? vets.map(v=>`<option value="${v.id}">${v.nombre}${v.especialidad?(' - '+v.especialidad):''}</option>`).join('') : '<option value="">Sin disponibilidad</option>';
      }catch(e){ medico.innerHTML='<option value="">Error cargando médicos</option>'; }
    });
  </script>
</body>
</html>

