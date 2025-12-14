// Valida que las contraseñas coincidan
export function validarContrasenas(password, confirmarPassword) {
    if (password !== confirmarPassword) {
        return 'Las contraseñas no coinciden';
    }
    return null;
}

// Obtiene los datos del formulario
export function obtenerDatosFormulario() {
    return {
        nombre_usuario: document.getElementById('nombre').value.trim(),
        apellido: document.getElementById('apellido').value.trim(),
        email: document.getElementById('email').value.trim().toLowerCase(),
        password: document.getElementById('password').value,
        confirmar_password: document.getElementById('confirmar_password').value,
        telefono: document.getElementById('telefono').value.trim(),
        direccion: document.getElementById('direccion').value.trim(),
        localidad: document.getElementById('localidad').value.trim(),
        codigo_postal: document.getElementById('codigo_postal').value.trim()
    };
}

