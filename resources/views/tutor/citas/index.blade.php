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
    .chip{display:inline-block;padding:2px 8px;border-radius:999px;background:#fff2e0;color:#d86f00;font-size:12px;margin-left:6px;border:1px solid #ffd6a6}
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
          <th>Hora</th>
          <th>Mascota</th>
          <th>Motivo</th>
          <th>Estado</th>
          <th>Veterinario</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($citas as $c)
          <tr data-id="{{ $c->id_cita_medi }}">
            <td>{{ \Carbon\Carbon::parse($c->fech_cons)->format('d/m/Y') }}</td>
            <td>{{ $c->hora_cons ? substr($c->hora_cons,0,5) : '—' }}</td>
            <td>{{ $c->mascota->nom_masc ?? '—' }}</td>
            <td>{{ $c->motiv_cons ?? '—' }}</td>
            <td>{{ ucfirst($c->estado ?? '—') }}</td>
            <td>{{ $c->medico ? trim(($c->medico->nombre_mv ?? '').' '.($c->medico->apell_mv ?? '')) : '—' }}</td>
            <td>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <button class="btn btn-confirm" data-id="{{ $c->id_cita_medi }}" data-action="confirm" style="border:1px solid #2ecc71;color:#2ecc71;background:#fff;border-radius:8px;padding:6px 10px;height:auto">Confirmar</button>
                <button class="btn btn-cancel" data-id="{{ $c->id_cita_medi }}" data-action="cancel" style="border:1px solid #e74c3c;color:#e74c3c;background:#fff;border-radius:8px;padding:6px 10px;height:auto">Cancelar</button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="empty">Sin citas en el historial</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <script>
    const token = '{{ csrf_token() }}';
    document.querySelectorAll('.btn-confirm, .btn-cancel').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const estado = action === 'confirm' ? 'confirmada' : 'cancelada';
        if (action === 'cancel' && !confirm('¿Seguro que deseas cancelar esta cita?')) return;
        try {
          const url = `{{ url('/tutor/citas') }}/${id}/estado`;
          const res = await fetch(url, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: new URLSearchParams({ estado }) });
          if (!res.ok) { const t = await res.text(); throw new Error(t); }
          location.reload();
        } catch (err) {
          console.error(err);
          alert('No se pudo actualizar el estado');
        }
      });
    });
  </script>
</body>
</html>

