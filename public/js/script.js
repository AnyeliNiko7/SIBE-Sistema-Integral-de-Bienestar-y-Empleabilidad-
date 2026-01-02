const sidebarToggleBtns = document.querySelectorAll(".sidebar-toggle");
const sidebar = document.querySelector(".sidebar");
const menuLinks = document.querySelectorAll(".menu-link");
const homeSection = document.querySelector('.home-section');

// Crear tooltip global
const tooltip = document.createElement('div');
tooltip.className = 'custom-tooltip';
document.body.appendChild(tooltip);

// Toggle sidebar collapsed state on buttons click
sidebarToggleBtns.forEach((btn) => {
  btn.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    updateToggleIcon();
    updateHomeSectionMargin();
    // Ocultar tooltip al colapsar/expandir
    hideTooltip();
  });
});

// Función para actualizar el margen del contenido principal
function updateHomeSectionMargin() {
  if (homeSection) {
    if (sidebar.classList.contains('collapsed')) {
      homeSection.style.left = '90px';
      homeSection.style.width = 'calc(100% - 90px)';
    } else {
      homeSection.style.left = '270px';
      homeSection.style.width = 'calc(100% - 270px)';
    }
  }
}

// Función para actualizar el ícono del botón
function updateToggleIcon() {
  const toggleIcon = document.querySelector('.sidebar-toggle i');
  if (sidebar.classList.contains('collapsed')) {
    toggleIcon.className = 'bx bx-menu';
  } else {
    toggleIcon.className = 'bx bx-menu-alt-left';
  }
}

// Expand sidebar by default on large screens
if (window.innerWidth > 768) {
  sidebar.classList.remove("collapsed");
}
updateToggleIcon(); // Asegurar que el ícono sea correcto al cargar
updateHomeSectionMargin(); // Asegurar que el margen sea correcto al cargar

// Funcionalidad de tooltips
menuLinks.forEach(link => {
  link.addEventListener('mouseenter', function(e) {
    if (!sidebar.classList.contains('collapsed')) return;
    
    const tooltipText = this.getAttribute('data-tooltip');
    if (!tooltipText) return;
    
    showTooltip(this, tooltipText);
  });
  
  link.addEventListener('mouseleave', function() {
    hideTooltip();
  });
  
  link.addEventListener('click', function() {
    hideTooltip();
  });
});

// Función para mostrar tooltip
function showTooltip(element, text) {
  const rect = element.getBoundingClientRect();
  
  tooltip.textContent = text;
  tooltip.style.left = (rect.right + 10) + 'px';
  tooltip.style.top = (rect.top + (rect.height / 2)) + 'px';
  tooltip.style.transform = 'translateY(-50%)';
  
  // Verificar si el tooltip se sale de la pantalla
  const tooltipRect = tooltip.getBoundingClientRect();
  if (tooltipRect.right > window.innerWidth - 10) {
    tooltip.style.left = (rect.left - tooltipRect.width - 10) + 'px';
    tooltip.style.transform = 'translateY(-50%)';
  }
  
  tooltip.classList.add('show');
}

// Función para ocultar tooltip
function hideTooltip() {
  tooltip.classList.remove('show');
}

// Ocultar tooltip al hacer scroll o redimensionar
window.addEventListener('scroll', hideTooltip);
window.addEventListener('resize', hideTooltip);

// Cerrar tooltip al hacer click fuera
document.addEventListener('click', function(e) {
  if (!e.target.closest('.menu-link') && !e.target.closest('.custom-tooltip')) {
    hideTooltip();
  }
});