@extends('layouts.landing')

@section('content')
  <div id="inicio"></div>
  <div class="menu-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </div>

  <aside class="sidebar" id="sidebar">
    <div class="close-btn" onclick="toggleSidebar()">✖</div>
    <h2>Orange Hearth</h2>
    <ul>
      <li><a href="#inicio"><i class="fas fa-home"></i> Inicio</a></li>
      <li><a href="#servicios"><i class="fas fa-stethoscope"></i> Servicios</a></li>
      <li><a href="#planes"><i class="fas fa-file-medical"></i> Planes</a></li>
      <li><a href="#contacto"><i class="fas fa-envelope"></i> Contacto</a></li>
    </ul>
  </aside>

  <section class="hero">
    <div class="hero-text">
      <h1>Bienvenido a OrangeHearth</h1>
      <p>Tu espacio digital para el cuidado animal. Con nuestra plataforma, veterinarios y tutores pueden gestionar citas, acceder a historiales y mucho más.</p>
      <p>Todo lo que necesitas para cuidar a tus mascotas, en un solo lugar.</p>
    </div>
    <div class="hero-logo">
      <div class="logo-box">
        <img src="{{ asset('img/LogoOrangeHearth.png') }}" alt="Logo Orange Hearth">
      </div>
    </div>
  </section>

  <section class="roles-section">
    <h2>Selecciona tu rol para continuar</h2>
    <div class="role-boxes">
      <div class="role-card" onclick="window.location.href='{{ route('login.tutor') }}'">
        <img src="{{ asset('img/Usuario.png') }}" alt="Tutor">
        <span>Tutor</span>
      </div>
      <div class="role-card" onclick="window.location.href='{{ route('login.veterinario') }}'">
        <img src="{{ asset('img/MedicoVeterinario.png') }}" alt="Veterinario">
        <span>Veterinario</span>
      </div>
      <div class="role-card" onclick="window.location.href='{{ route('login.admin') }}'">
        <img src="{{ asset('img/Admin.png') }}" alt="Administrador">
        <span>Administrador</span>
      </div>
    </div>
    <!-- Rutas futuras (ejemplo):
         Tutor dashboard: route('tutor.dashboard')
         Vet dashboard:   route('vet.dashboard')
         Admin dashboard: route('admin.dashboard')
    -->
  </section>

  <section class="info-section">
    <div class="info-row" id="servicios">
      <div class="info-text">
        <h3>Nuestros servicios</h3>
        <p>OrangeHearth ofrece servicios dinámicos para el agendamiento de citas médicas veterinarias, facilitando tanto a los tutores como a las clínicas la gestión de una tarea que suele ser compleja y estresante. Con este sistema, los clientes pueden agendar y modificar sus citas, recibir notificaciones anticipadas como recordatorio, y garantizar que cualquier cambio sea comunicado de forma inmediata tanto a la clínica como al médico encargado. A su vez, los profesionales pueden ajustar su agenda con libertad, generando notificaciones automáticas para mantener la coherencia operativa. Además, el administrador cuenta con toda la información centralizada y organizada, gracias a un sistema segmentado por roles que distribuye eficientemente las funciones según el perfil de cada usuario.</p>
      </div>
      <div class="info-image">
        <img src="{{ asset('img/QueSomos.png') }}" alt="Ilustración Orange Hearth">
      </div>
    </div>

    <div class="info-row reverse" id="planes">
      <div class="info-image">
        <img src="{{ asset('img/Precios.png') }}" alt="Planes y precios">
      </div>
      <div class="info-text">
        <h3>Planes y costos</h3>
        <p>Tenemos planes flexibles para clínicas y profesionales:<br>
        - Básico: $50.000/mes<br>
        - Profesional: $90.000/mes<br>
        - Empresarial: $150.000/mes</p>
      </div>
    </div>

    <div class="info-row" id="contacto">
      <div class="info-text">
        <h3>Contacto</h3>
        <p>Estamos para ayudarte:<br>
        📞 WhatsApp: +57 301 555 1234<br>
        📧 Correo: contacto@orangehearth.com<br>
        🐾 Instagram: @orangehearth_vet</p>
      </div>
      <div class="info-image">
        <img src="{{ asset('img/contacto.png') }}" alt="Imagen de contacto">
      </div>
    </div>
  </section>
@endsection
