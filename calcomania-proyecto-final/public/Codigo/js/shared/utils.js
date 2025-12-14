// Utilidades para inputs OTP
export function obtenerCodigo(inputs) {
    return inputs.map(input => input.value).join('');
}

export function limpiarCodigo(inputs) {
    inputs.forEach(input => input.value = '');
    inputs[0]?.focus();
}

export function soloDigitos(texto) {
    return (texto || '').replace(/\D/g, '');
}

