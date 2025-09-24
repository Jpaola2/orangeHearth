<section id="registro-vet" class="dashboard-section">
  <h3><i class="fas fa-user-md"></i> Registrar nuevo medico veterinario</h3>
  <form id="vetForm" class="form-grid">
    <label class="form-group">
      <span><i class="fas fa-user"></i> Nombre completo</span>
      <input type="text" id="nombre" name="nombre" required placeholder="Ej: Dr. Juan Perez">
    </label>
    <label class="form-group">
      <span><i class="fas fa-envelope"></i> Correo electronico</span>
      <input type="email" id="correo" name="correo" required placeholder="veterinario@orangehearth.com">
    </label>
    <label class="form-group">
      <span><i class="fas fa-lock"></i> Contrasena</span>
      <input type="password" id="clave" name="clave" required placeholder="Contrasena segura">
    </label>
    <label class="form-group">
      <span><i class="fas fa-stethoscope"></i> Especialidad</span>
      <select id="especialidad" name="especialidad" required>
        <option value="">Seleccione especialidad...</option>
        <option value="Medicina General">Medicina General</option>
        <option value="Cirugia">Cirugia</option>
        <option value="Dermatologia">Dermatologia</option>
        <option value="Cardiologia">Cardiologia</option>
        <option value="Neurologia">Neurologia</option>
        <option value="Oncologia">Oncologia</option>
      </select>
    </label>
    <label class="form-group">
      <span><i class="fas fa-id-card"></i> Tarjeta profesional *</span>
      <input type="text" id="tarjeta_profesional" name="tarjeta_profesional" required placeholder="Ej: TP123456">
    </label>
    <label class="form-group">
      <span><i class="fas fa-phone"></i> Telefono</span>
      <input type="tel" id="telefono" name="telefono" required placeholder="+57 300 123 4567">
    </label>
    <div class="form-actions">
      <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Registrar veterinario</button>
    </div>
  </form>
</section>