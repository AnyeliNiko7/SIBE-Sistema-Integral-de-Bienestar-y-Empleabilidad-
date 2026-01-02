// Función única para validar contraseña reutilizable
function validarContrasena(contrasena, context = 'main') {
    // Determinar los IDs basados en el contexto
    let prefix = '';
    let submitButtonId = 'submitButton';
    
    if (context === 'modal') {
        prefix = '';
        submitButtonId = 'submitButtonContrasena';
    }
    
    const reqLength = document.getElementById(prefix + 'reqLength');
    const reqUppercase = document.getElementById(prefix + 'reqUppercase');
    const reqLowercase = document.getElementById(prefix + 'reqLowercase');
    const reqNumber = document.getElementById(prefix + 'reqNumber');
    const reqSpecial = document.getElementById(prefix + 'reqSpecial');
    const submitButton = document.getElementById(submitButtonId);
    
    // Si no se encuentran los elementos, retornar true para no bloquear el envío
    if (!reqLength || !reqUppercase || !reqLowercase || !reqNumber || !reqSpecial) {
        console.warn('Elementos de validación de contraseña no encontrados');
        return true;
    }
    
    let isValid = true;
    
    // Validar longitud
    if (contrasena.length >= 8 && contrasena.length <= 16) {
        reqLength.className = 'requirement valid';
    } else {
        reqLength.className = 'requirement invalid';
        isValid = false;
    }
    
    // Validar mayúscula
    if (/[A-Z]/.test(contrasena)) {
        reqUppercase.className = 'requirement valid';
    } else {
        reqUppercase.className = 'requirement invalid';
        isValid = false;
    }
    
    // Validar minúscula
    if (/[a-z]/.test(contrasena)) {
        reqLowercase.className = 'requirement valid';
    } else {
        reqLowercase.className = 'requirement invalid';
        isValid = false;
    }
    
    // Validar número
    if (/[0-9]/.test(contrasena)) {
        reqNumber.className = 'requirement valid';
    } else {
        reqNumber.className = 'requirement invalid';
        isValid = false;
    }
    
    // Validar símbolo especial
    if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(contrasena)) {
        reqSpecial.className = 'requirement valid';
    } else {
        reqSpecial.className = 'requirement invalid';
        isValid = false;
    }
    
    // Habilitar/deshabilitar botón de envío si existe
    if (submitButton) {
        submitButton.disabled = !isValid;
    }
    
    return isValid;
}

// Función para manejar toggle de visibilidad de contraseña
function setupPasswordToggle(passwordInputId, toggleButtonId) {
    const passwordInput = document.getElementById(passwordInputId);
    const toggleButton = document.getElementById(toggleButtonId);
    
    if (!passwordInput || !toggleButton) return;
    
    // Ocultar inicialmente si el campo está vacío
    if (passwordInput.value === "") {
        toggleButton.style.display = 'none';
    }
    
    passwordInput.addEventListener('input', function() {
        toggleButton.style.display = this.value.length > 0 ? 'block' : 'none';
    });
    
    passwordInput.addEventListener('focus', function() {
        if (this.value.length > 0) {
            toggleButton.style.display = 'block';
        }
    });
    
    passwordInput.addEventListener('blur', function() {
        if (this.value === "") {
            toggleButton.style.display = 'none';
        }
    });
    
    toggleButton.addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.classList.toggle('bx-show');
        this.classList.toggle('bx-hide');
        passwordInput.focus();
    });
}

function cambiarEstado(userId, estadoActual, area) {
    if (area === 'Administrador') {
        alert('No se puede cambiar el estado de un usuario Administrador');
        return;
    }
    
    const nuevoEstado = estadoActual === 'activo' ? 'inactivo' : 'activo';
    const confirmacion = confirm(`¿Estás seguro de que quieres ${nuevoEstado === 'activo' ? 'desactivar' : 'activar'} este usuario?`);
    
    if (confirmacion) {
        fetch('cambiar_estado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${userId}&estado=${nuevoEstado}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cambiar el estado');
        });
    }
}

function desbloquearUsuario(userId) {
    if (confirm('¿Estás seguro de que quieres desbloquear este usuario?')) {
        fetch('desbloquear_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${userId}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al desbloquear el usuario');
        });
    }
}

function abrirModalContrasena(registro) {
    document.getElementById('modalContrasena').style.display = 'block';
    document.getElementById('id_contrasena').value = registro.id;
    document.getElementById('contrasena').value = '';
    validarContrasena('', 'modal'); // Usar contexto modal
}

function cerrarModalContrasena() {
    document.getElementById('modalContrasena').style.display = 'none';
    document.getElementById('contrasena').value = '';
}

function abrirModalEditar(registro) {
    document.getElementById('modalEditarUsuario').style.display = 'block';
    document.getElementById('edit_id').value = registro.id;
    document.getElementById('edit_usuario').value = registro.usuario;
    document.getElementById('edit_correo').value = registro.correo;
    document.getElementById('edit_area_id').value = registro.area_id;
}

function cerrarModalEditar() {
    document.getElementById('modalEditarUsuario').style.display = 'none';
}

// Inicialización cuando el DOM está listo
document.addEventListener("DOMContentLoaded", function() {
    // Configurar toggles de contraseña para ambos formularios
    setupPasswordToggle('contraseña', 'togglePassword'); // Formulario principal
    setupPasswordToggle('contrasena', 'togglePasswordModal'); // Modal
    
    // Auto-ocultar mensajes después de 5 segundos
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert, #error-message');
        alerts.forEach(alert => {
            if (alert) alert.style.display = 'none';
        });
    }, 5000);
    
    // Validar formulario de crear usuario (si existe)
    const formCrearUsuario = document.getElementById('formCrearUsuario');
    if (formCrearUsuario) {
        formCrearUsuario.addEventListener('submit', function(event) {
            const contrasena = document.getElementById('contraseña')?.value || '';
            if (!validarContrasena(contrasena, 'main')) {
                event.preventDefault();
                alert('Por favor, corrige los errores en la contraseña antes de enviar.');
            }
        });
    }
    
    // Validar formulario de cambiar contraseña (modal)
    const formContrasena = document.getElementById('formContrasena');
    if (formContrasena) {
        formContrasena.addEventListener('submit', function(event) {
            const contrasena = document.getElementById('contrasena')?.value || '';
            if (!validarContrasena(contrasena, 'modal')) {
                event.preventDefault();
                alert('Por favor, corrige los errores en la contraseña antes de enviar.');
            }
        });
        
        // Validar en tiempo real en el modal
        const contrasenaInput = document.getElementById('contrasena');
        if (contrasenaInput) {
            contrasenaInput.addEventListener('input', function() {
                validarContrasena(this.value, 'modal');
            });
        }
    }
});

// Cerrar modales al hacer clic fuera o presionar ESC
window.onclick = function(event) {
    const modalContrasena = document.getElementById('modalContrasena');
    const modalEditar = document.getElementById('modalEditarUsuario');
    
    if (event.target === modalContrasena) {
        cerrarModalContrasena();
    }
    if (event.target === modalEditar) {
        cerrarModalEditar();
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalContrasena();
        cerrarModalEditar();
    }
});