document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('adminRegisterForm');
  if (!form) return;

  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const nitRe = /^\d{5}$/; // NIT de 5 digitos (se verifica en servidor)

  const fields = [
    { id: 'nombre_completo', required: true },
    { id: 'email', required: true, test: v => emailRe.test(v) },
    { id: 'telefono', required: false },
    { id: 'cedula', required: true, test: v => /^\d{6,10}$/.test(v) },
    { id: 'empresa_nombre', required: true },
    { id: 'nit', required: true, test: v => nitRe.test(v) },
    { id: 'password', required: true, test: v => v.length >= 8 },
    { id: 'password_confirmation', required: true, test: v => v.length >= 8 && v === (document.getElementById('password')?.value || '') },
  ];

  function setStatus(input, ok, msg) {
    const errorEl = input.parentElement?.querySelector('.error-message') || input.nextElementSibling;
    input.classList.toggle('error', !ok);
    if (errorEl) {
      errorEl.textContent = ok ? '' : (msg || 'Campo no valido');
      errorEl.classList.toggle('visible', !ok);
    }
  }

  function validateInput(input) {
    const def = fields.find(f => f.id === input.id);
    const value = (input.value || '').trim();
    let ok = true, msg = '';
    if (def?.required && !value) { ok = false; msg = 'Este campo es obligatorio'; }
    if (ok && def?.test) { ok = !!def.test(value); if (!ok && !msg) msg = 'Formato no valido'; }
    if (input.id === 'password_confirmation' && ok) {
      const p = document.getElementById('password')?.value || '';
      if (value !== p) { ok = false; msg = 'Las contrasenas no coinciden'; }
    }
    setStatus(input, ok, msg);
    return ok;
  }

  form.querySelectorAll('input').forEach(i => {
    i.addEventListener('blur', () => validateInput(i));
    i.addEventListener('input', () => validateInput(i));
  });

  const toggle = document.getElementById('togglePassword');
  if (toggle) toggle.addEventListener('click', () => {
    const p = document.getElementById('password');
    if (!p) return;
    p.type = p.type === 'password' ? 'text' : 'password';
    toggle.classList.toggle('fa-eye-slash');
  });

  form.addEventListener('submit', (e) => {
    let ok = true;
    fields.forEach(f => {
      const input = document.getElementById(f.id);
      if (input && !validateInput(input)) ok = false;
    });
    if (!ok) {
      e.preventDefault();
      alert('Por favor, corrige los campos marcados en rojo.');
    }
  });
});
