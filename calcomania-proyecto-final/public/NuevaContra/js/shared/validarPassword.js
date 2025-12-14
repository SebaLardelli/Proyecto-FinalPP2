// Valida el formulario de cambio de contraseña
export function validarFormularioCambioContrasena(password1, password2) {
    if (!password1 || !password2) return 'Completá ambos campos.';
    if (password1 !== password2) return 'Las contraseñas no coinciden.';
    if (password1.length < 6) return 'La contraseña debe tener al menos 6 caracteres.';
    return null;
}

