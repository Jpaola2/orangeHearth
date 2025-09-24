<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Orange Hearth</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a2e0e6e6f1.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
  <main>
    @yield('content')
  </main>
  <script src="{{ asset('js/landing.js') }}"></script>
</body>
<!-- Coloca las imágenes usadas en public/img: LogoOrangeHearth.png, Usuario.png, MedicoVeterinario.png, Admin.png, QueSomos.png, Precios.png, contacto.png -->
</html>

