// Validaciones de cliente (adaptado a Laravel: submit normal si todo bien)

const regexValidations = {
  nombre: /^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ]+ [a-zA-ZáéíóúüñÁÉÍÓÚÜÑ ]+$/,
  email: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/,
  documento: /^[0-9XYZ][0-9]{7}[A-Z]$|^[0-9]{8,12}$/,
  telefono: /^(\+\d{1,3}\s?)?\d{9,15}$/,
  direccion: /^[a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ\s,.-]{5,100}$/,
  mascota: /^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/,
  edad: /^[0-9]{1,2}$/
};

const errorMessages = {
  nombre: "Ingrese nombre y apellido completos (solo letras y espacios)",
  email: "Ingrese un correo electrónico válido",
  documento: "Ingrese un número de documento válido",
  telefono: "Ingrese un número de teléfono válido",
  direccion: "Ingrese una dirección válida (5-100 caracteres)",
  mascota: "Ingrese un nombre válido para la mascota (2-30 caracteres)",
  edad: "Ingrese una edad válida",
  password: "La contraseña debe cumplir todos los requisitos"
};

const passwordRequirements = {
  length: value => value.length >= 8 && value.length <= 20,
  uppercase: value => /[A-Z]/.test(value),
  lowercase: value => /[a-z]/.test(value),
  number: value => /[0-9]/.test(value),
  special: value => /[!@#$%^&*]/.test(value)
};

function updateFieldStatus(input, isValid, errorMessage) {
  const errorElement = input.nextElementSibling;
  input.classList.toggle('error', !isValid);
  input.classList.toggle('success', isValid);
  if (errorElement && errorElement.classList.contains('error-message')) {
    errorElement.textContent = isValid ? '' : (errorMessage || 'Campo inválido');
    errorElement.classList.toggle('visible', !isValid);
  }
}

function validatePassword(input) {
  const value = input.value || '';
  let isValid = true;
  Object.entries(passwordRequirements).forEach(([k, fn]) => {
    const el = document.querySelector(`[data-requirement="${k}"]`);
    const ok = fn(value);
    if (el) el.classList.toggle('valid', ok);
    if (!ok) isValid = false;
  });
  updateFieldStatus(input, isValid, errorMessages.password);
  return isValid;
}

function validateField(input) {
  const field = input.getAttribute('data-validation');
  const value = (input.value || '').trim();
  if (field === 'password') return validatePassword(input);

  if (field === 'edad') {
    const edad = parseInt(value, 10);
    const unidad = document.getElementById('unidad_edad')?.value || '';
    let max = 50, ok = false;
    if (unidad === 'dias') { max = 365; ok = !!value && edad >= 1 && edad <= max; }
    else if (unidad === 'meses') { max = 240; ok = !!value && edad >= 1 && edad <= max; }
    else if (unidad === 'años') { max = 30; ok = !!value && edad >= 1 && edad <= max; }
    else { ok = !value; } // si no hay unidad y no hay valor, no marcar error
    const msg = !unidad && value ? 'Seleccione la unidad de tiempo' : `Ingrese una edad válida (1-${max} ${unidad || ''})`;
    updateFieldStatus(input, ok, msg);
    return ok;
  }

  const isValid = regexValidations[field] ? regexValidations[field].test(value) : !!value;
  updateFieldStatus(input, isValid, errorMessages[field]);
  return isValid;
}

function sanitizeInput(s) { return (s || '').replace(/[<>\'\"]/g, ''); }

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registroForm');
  if (!form) return;

  const inputs = form.querySelectorAll('input[data-validation]');
  const unidadEdadSelect = document.getElementById('unidad_edad');
  const edadInput = document.getElementById('edad');

  unidadEdadSelect?.addEventListener('change', () => { if (edadInput?.value) validateField(edadInput); });

  inputs.forEach(input => {
    input.addEventListener('input', e => { e.target.value = sanitizeInput(e.target.value); validateField(e.target); });
    input.addEventListener('blur', e => validateField(e.target));
  });

  form.addEventListener('submit', e => {
    let isFormValid = true;
    const requiredFields = form.querySelectorAll('input[required], select[required]');
    requiredFields.forEach(field => {
      if (field.hasAttribute('data-validation')) {
        if (!validateField(field)) isFormValid = false;
      } else if (!field.value.trim()) {
        isFormValid = false; updateFieldStatus(field, false, 'Este campo es obligatorio');
      } else {
        updateFieldStatus(field, true, '');
      }
    });
    if (!isFormValid) { e.preventDefault(); alert('Por favor, corrige los errores antes de continuar.'); }
  });
});

