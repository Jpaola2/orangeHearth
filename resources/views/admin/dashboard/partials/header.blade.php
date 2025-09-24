<header class="dashboard-header">
  <h1><i class="fas fa-paw"></i> ORANGE HEARTH - Panel Administrativo</h1>
  <form class="logout-form" method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="logout-button"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</button>
  </form>
</header>