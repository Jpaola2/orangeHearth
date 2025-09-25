<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reporte OrangeHearth</title>
  <style>
    /* Usa fuentes estándar que Dompdf conozca */
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 12px; color: #111; }
    .box { border: 2px solid #333; padding: 16px; }
    h1 { font-size: 18px; margin: 0 0 8px; text-align: center; }
    .sep { border-top: 1px dashed #aaa; margin: 14px 0; }
    .title { font-weight: bold; margin: 8px 0 4px; }
    ul { margin: 6px 0 0 18px; padding: 0; }
    li { margin: 2px 0; }
    .muted { color: #555; }
    .mono { font-family: DejaVu Sans, sans-serif; }
    /* Sustituimos flex por tabla para compatibilidad */
    .grid { width:100%; border-collapse:collapse; margin-top:6px; }
    .grid td { width:50%; vertical-align:top; padding-right:24px; }
  </style>
</head>
<body>
  <div class="box">
    <h1>===== REPORTE DEL SISTEMA ORANGEHEARTH =====</h1>
    <p><strong>Fecha:</strong> {{ $now->format('d/m/Y, H:i:s') }}</p>

    <div class="sep"></div>

    <p class="title">USUARIOS REGISTRADOS:</p>
    <ul class="mono">
      <li>Tutores: {{ $totTutores }}</li>
      <li>Veterinarios: {{ $totVets }}</li>
      <li>Total: {{ $totUsers }}</li>
    </ul>

    <table class="grid">
      <tr>
        <td>
          <p class="title">MASCOTAS REGISTRADAS:</p>
          <p class="mono">{{ $totMascotas }}</p>
        </td>
        <td>
          <p class="title">CITAS AGENDADAS:</p>
          <p class="mono">{{ $totCitas }}</p>
        </td>
      </tr>
    </table>

    <div class="sep"></div>

    <p class="title">ÚLTIMOS 5 USUARIOS REGISTRADOS:</p>
    <ol class="mono">
      @forelse ($lastUsers as $u)
        <li>{{ $u->name }} - {{ $u->email }}</li>
      @empty
        <li class="muted">No hay registros.</li>
      @endforelse
    </ol>

    <div class="sep"></div>
    <p class="mono" style="text-align:center;">===============================================</p>
  </div>
</body>
</html>
