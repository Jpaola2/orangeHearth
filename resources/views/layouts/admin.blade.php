{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Administrador - Orange Hearth')</title>

  {{-- Fuente e iconos --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  {{-- Estilos base del dashboard (ligeros) --}}
  <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">

  {{-- Solo carga librerías pesadas si la vista lo solicita --}}
  @hasSection('force_dashboard_assets')
    @php($__forceAssets = true)
  @else
    @php($__forceAssets = false)
  @endif

  @if($__forceAssets)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" defer></script>
    <script src="{{ asset('js/admin/dashboard.js') }}" defer></script>
  @else
    @stack('needs-dashboard-assets')
    @push('head:end')
      <script>
        // Este bloque evita repetir Chart.js si la vista no lo necesita
        document.addEventListener('DOMContentLoaded', function () {
          // no-op
        });
      </script>
    @endpush
  @endif

  {{-- Hook para que una vista hija inyecte <meta> adicionales, estilos o libs --}}
  @stack('head')

  {{-- Cierre del head por si la vista empuja algo al final del <head> --}}
  @stack('head:end')
</head>
<body>
  <div id="app" class="app-shell">
    {{-- Región de alertas/notificaciones simple --}}
    <div id="toast-root" aria-live="polite" aria-atomic="true" style="position:fixed;right:16px;top:16px;z-index:9999;"></div>

    {{-- Contenido: preferimos body, pero soportamos content para flexibilidad --}}
    @hasSection('body')
      @yield('body')
    @else
      @yield('content')
    @endif
  </div>

  {{-- Expone CSRF y utilidades globales seguras --}}
  <script>
    (function () {
      try {
        window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      } catch (e) {
        window.CSRF_TOKEN = '{{ csrf_token() }}';
      }

      // Fallback básico para navegación entre secciones
      window.adminShowSection = window.adminShowSection || function(id, ev) {
        try { if (ev) ev.preventDefault(); } catch(e) {}

        var sections = document.querySelectorAll('.dashboard-section');
        if (sections && sections.length) {
          sections.forEach(function(s){
            if (s.id === id) {
              s.style.display = 'block';
              s.classList.add('active');
            } else {
              s.style.display = 'none';
              s.classList.remove('active');
            }
          });
        }

        var links = document.querySelectorAll('.sidebar a[data-section]');
        if (links && links.length) {
          links.forEach(function(a){
            var match = (a.dataset && a.dataset.section === id);
            a.classList.toggle('active', !!match);
            a.setAttribute('aria-current', match ? 'page' : 'false');
          });
        }
        return false;
      };

      // Activa navegación del sidebar (si existe)
      document.addEventListener('DOMContentLoaded', function(){
        var links = document.querySelectorAll('.sidebar a[data-section]');
        if (!links || !links.length) return;
        links.forEach(function(a){
          a.addEventListener('click', function(e){
            window.adminShowSection(this.dataset.section, e);
          });
        });
      });

      // Utilidad mínima de toasts
      window.showToast = function (message, type) {
        var root = document.getElementById('toast-root');
        if (!root) return alert(message);
        var toast = document.createElement('div');
        toast.role = 'status';
        toast.style.cssText = 'background:#fff;border:1px solid #e5e7eb;border-left:4px solid '
          + (type === 'error' ? '#dc2626' : type === 'success' ? '#16a34a' : '#f59e0b')
          + ';padding:10px 12px;margin-top:8px;border-radius:8px;min-width:240px;box-shadow:0 2px 10px rgba(0,0,0,.05);';
        toast.textContent = message || 'Operación realizada';
        root.appendChild(toast);
        setTimeout(function(){ toast.remove(); }, 3500);
      };
    })();
  </script>

  {{-- Scripts que inyecten las vistas --}}
  @stack('scripts')
</body>
</html>
