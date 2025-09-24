// Toggle password visibility
const togglePassword = document.getElementById('togglePassword');
if (togglePassword) {
  togglePassword.addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) return;
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
  });
}

// Helpers de error
function mostrarError(campo, mensaje) {
  const input = document.getElementById(campo);
  const errorDiv = document.getElementById(campo + 'Error');
  if (!input || !errorDiv) return;
  input.classList.add('error');
  input.classList.remove('success');
  errorDiv.textContent = mensaje;
  errorDiv.classList.add('visible');
}

function limpiarError(campo) {
  const input = document.getElementById(campo);
  const errorDiv = document.getElementById(campo + 'Error');
  if (!input || !errorDiv) return;
  input.classList.remove('error');
  input.classList.add('success');
  errorDiv.classList.remove('visible');
}

function limpiarTodosLosErrores() {
  ['email', 'password', 'tarjeta_profesional'].forEach(campo => {
    const input = document.getElementById(campo);
    const errorDiv = document.getElementById(campo + 'Error');
    if (input) input.classList.remove('error', 'success');
    if (errorDiv) errorDiv.classList.remove('visible');
  });
}

// Validación en tiempo real
const emailInput = document.getElementById('email');
if (emailInput) {
  emailInput.addEventListener('input', function () {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
      mostrarError('email', 'Ingrese un correo electrónico válido');
    } else if (email) {
      limpiarError('email');
    }
  });
}

const tarjetaInput = document.getElementById('tarjeta_profesional');
if (tarjetaInput) {
  tarjetaInput.addEventListener('input', function () {
    this.value = this.value.toUpperCase();
    const tarjeta = this.value;
    const tarjetaRegex = /^[A-Z]{2}[0-9]{4,8}$/;
    if (tarjeta && !tarjetaRegex.test(tarjeta)) {
      mostrarError('tarjeta_profesional', 'Formato: 2 letras + 4-8 números (ej: TP123456)');
    } else if (tarjeta) {
      limpiarError('tarjeta_profesional');
    }
  });
}

// Envío del formulario con validación básica
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', function (e) {
    const email = (document.getElementById('email')?.value || '').trim();
    const password = document.getElementById('password')?.value || '';
    const tarjeta_profesional = (document.getElementById('tarjeta_profesional')?.value || '').trim();
    const loginButton = document.getElementById('loginButton');
    const loading = document.querySelector('.loading');

    limpiarTodosLosErrores();

    let hasErrors = false;
    if (!email) {
      mostrarError('email', 'El correo electrónico es obligatorio');
      hasErrors = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      mostrarError('email', 'Ingrese un correo electrónico válido');
      hasErrors = true;
    }

    if (!password) {
      mostrarError('password', 'La contraseña es obligatoria');
      hasErrors = true;
    } else if (password.length < 6) {
      mostrarError('password', 'La contraseña debe tener al menos 6 caracteres');
      hasErrors = true;
    }

    if (tarjeta_profesional && !/^[A-Z]{2}[0-9]{4,8}$/.test(tarjeta_profesional)) {
      mostrarError('tarjeta_profesional', 'Formato: 2 letras + 4-8 números (ej: TP123456)');
      hasErrors = true;
    }

    if (hasErrors) {
      e.preventDefault();
      return;
    }

    if (loginButton && loading) {
      loginButton.disabled = true;
      loading.classList.add('active');
    }
  });
}

// Ayuda
function mostrarAyuda() {
  alert(`Soporte Técnico OrangeHearth

📧 Email: soporte@orangehearth.com
📞 Teléfono: +57 (1) 234-5678
🕒 Horario: Lunes a Viernes 8:00 AM - 6:00 PM

Para obtener acceso:
1. Debe contar con un usuario registrado
2. Sus credenciales deben ser válidas
3. Su cuenta debe estar activa

Si ya está registrado y tiene problemas:
- Verifique su correo y contraseña
- Si aplica, confirme su identificación profesional
- Contacte al administrador si su cuenta está inactiva`);
}

// Limpiar errores al enfocar
['email', 'password', 'tarjeta_profesional'].forEach(campo => {
  const el = document.getElementById(campo);
  if (!el) return;
  el.addEventListener('focus', function () {
    this.classList.remove('error');
    const msg = document.getElementById(campo + 'Error');
    if (msg) msg.classList.remove('visible');
  });
});
