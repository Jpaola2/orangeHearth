<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ficha de {{ $mascota->nom_masc }}</title>
  <style>
    body{font-family:"Segoe UI",sans-serif;margin:0;background:#f7f7f7;color:#333}
    .wrap{max-width:900px;margin:24px auto;padding:0 16px}
    h1{font-size:22px;margin:0 0 10px;color:#d86f00}
    .back{display:inline-block;margin:10px 0 16px;padding:8px 12px;border-radius:8px;border:1px solid #f28c28;background:#fff;color:#d86f00;text-decoration:none}
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:16px;margin-bottom:16px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .label{color:#666;font-size:12px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    th{background:#fff8f0;color:#d86f00}
  </style>
</head>
<body>
  <div class="wrap">
    <a class="back" href="{{ route('vet.pacientes.index') }}">← Volver</a>
    <h1>Ficha de {{ $mascota->nom_masc }}</h1>

    <div class="card">
      <div class="row">
        <div>
          <div class="label">Especie</div>
          <div><strong>{{ $mascota->espe_masc ?? '—' }}</strong></div>
        </div>
        <div>
          <div class="label">Género</div>
          <div><strong>{{ $mascota->gene_masc ?? '—' }}</strong></div>
        </div>
        <div>
          <div class="label">Tutor</div>
          <div><strong>{{ trim(($mascota->tutor->nomb_tutor ?? '').' '.($mascota->tutor->apell_tutor ?? '')) ?: 'Sin tutor' }}</strong></div>
        </div>
        <div>
          <div class="label">Correo tutor</div>
          <div><strong>{{ $mascota->tutor->correo_tutor ?? '—' }}</strong></div>
        </div>
      </div>
    </div>

    <div class="card">
      <h3 style="margin-top:0">Últimas citas</h3>
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Motivo</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          @forelse($citas as $c)
            <tr>
              <td>{{ \Carbon\Carbon::parse($c->fech_cons)->format('d/m/Y') }}</td>
              <td>{{ $c->motiv_cons }}</td>
              <td>{{ ucfirst($c->estado ?? 'pendiente') }}</td>
            </tr>
          @empty
            <tr><td colspan="3">Sin citas registradas</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

