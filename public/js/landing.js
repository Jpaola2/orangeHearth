function toggleSidebar() {
  const el = document.getElementById('sidebar');
  if (el) el.classList.toggle('active');
}

// Exponer en window para uso inline si fuera necesario
window.toggleSidebar = toggleSidebar;

