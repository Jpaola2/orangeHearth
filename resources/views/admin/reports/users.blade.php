<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-width: 150px; }
        .header h1 { margin: 0; }
        .header p { margin: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $logo }}" alt="Logo">
        <h1>{{ $title }}</h1>
        <p>Fecha de generación: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Tipo</th>
                <th>Detalle</th>
                <th>Especialidad</th>
                <th>Fecha registro</th>

            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user['nombre'] }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['tipo'] }}</td>
                    <td>{{ $user['detalle'] }}</td>
                    <td>{{ $user['especialidad'] }}</td>
                    <td>{{ $user['fecha_registro'] }}</td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No hay usuarios para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
