<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de agenda</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
    h1 { font-size: 18px; margin: 0 0 6px 0; color: #d86f00; }
    .meta { font-size: 12px; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
    th { background: #fff3e6; color: #a85a00; }
    .empty { padding: 16px; text-align: center; }
  </style>
  </head>
  <body>
    <h1>Agenda del médico</h1>
    <div class="meta">Doctor: <strong>{{ $doctor }}</strong> — Generado: {{ $now->format('d/m/Y H:i') }}</div>

    @if(!count($citas))
      <div class="empty">Sin citas asignadas aún.</div>
    @else
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Mascota</th>
            <th>Tutor</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          @foreach($citas as $c)
            <tr>
              <td>{{ \Carbon\Carbon::parse($c->fech_cons)->format('d/m/Y') }}</td>
              <td>{{ $c->mascota->nom_masc ?? 'Sin mascota' }}</td>
              <td>{{ trim(($c->tutor->nomb_tutor ?? '').' '.($c->tutor->apell_tutor ?? '')) ?: 'Sin tutor' }}</td>
              <td>{{ ucfirst($c->estado ?? 'pendiente') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </body>
  </html>

