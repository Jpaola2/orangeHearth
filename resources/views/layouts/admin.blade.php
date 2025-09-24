<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Administrador - Orange Hearth')</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" defer></script>
  <script src="https://kit.fontawesome.com/a2e0e6e6f1.js" crossorigin="anonymous" defer></script>
  <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
  <script src="{{ asset('js/admin/dashboard.js') }}" defer></script>
  @stack('head')
</head>
<body>
  @yield('body')
  @stack('scripts')

  <script>
    // Fallback básico para navegación entre secciones
    window.adminShowSection = window.adminShowSection || function(id, ev) {
      try { if (ev) ev.preventDefault(); } catch(e) {}
      var sections = document.querySelectorAll('.dashboard-section');
      if (!sections || !sections.length) return false;
      sections.forEach(function(s){ s.classList.toggle('active', s.id === id); });
      var links = document.querySelectorAll('.sidebar a');
      links.forEach(function(a){ var match = a.dataset && a.dataset.section === id; a.classList && a.classList.toggle('active', match); });
      return false;
    };
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.sidebar a').forEach(function(a){
        a.addEventListener('click', function(e){ window.adminShowSection(this.dataset.section, e); });
      });
    });
  </script>
</body>
</html>
