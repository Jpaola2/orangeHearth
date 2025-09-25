<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Historial de Citas</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body{font-family:"Segoe UI",sans-serif;margin:0;background:#f7f7f7;color:#333}
    .wrap{max-width:1000px;margin:24px auto;padding:0 16px}
    h1{font-size:22px;margin:0 0 10px;color:#d86f00;display:flex;align-items:center;gap:8px}
    .filters{display:flex;gap:8px;align-items:center;background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:10px}
    select,button{padding:10px 12px;border-radius:10px;border:1px solid #ddd;height:42px}
    button{border-color:#f28c28;background:#fff;color:#d86f00;cursor:pointer}
    table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
    th,td{padding:12px;border-bottom:1px solid #eee;text-align:left}
    th{background:#fff8f0;color:#d86f00}
    .empty{padding:16px;text-align:center;color:#666}
  </style>
</head>
<body>
  <div class="wrap">
    <h1><i class="fas fa-history"></i> Historial de Citas</h1>

    <form class="filters" method="get" action="{{ route('tutor.citas.index') }}">
      <label>Mascota
        <select name="mascota">
          <option value="">Todas</option>
          @foreach($mascotas as $m)
            <option value="{{ $m->id_masc }}" @selected($mascotaSeleccionada==$m->id_masc)>{{ $m->nom_masc }}</option>
          @endforeach
        </select>
      </label>
      <button type="submit">Filtrar</button>
      <a href="{{ route('tutor.dashboard') }}" style="margin-left:auto" class="btn">← Volver</a>
    </form>

    <table>
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Mascota</th>
          <th>Motivo</th>
          <th>Estado</th>
          <th>Veterinario</th>
        </tr>
      </thead>
      <tbody>
        @forelse($citas as $c)
          <tr>
            <td>{{ \Carbon\Carbon::parse($c->fech_cons)->format('d/m/Y') }}</td>
            <td>{{ $c->mascota->nom_masc ?? '—' }}</td>
            <td>{{ $c->motiv_cons ?? '—' }}</td>
            <td>{{ ucfirst($c->estado ?? '—') }}</td>
            <td>{{ $c->medico ? trim(($c->medico->nombre_mv ?? '').' '.($c->medico->apell_mv ?? '')) : '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Sin citas en el historial</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>

