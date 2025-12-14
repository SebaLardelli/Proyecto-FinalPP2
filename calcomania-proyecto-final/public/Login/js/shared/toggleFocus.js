// Alterna clase 'has-content' en campos de entrada
export function toggleFocus(campo) {
    if (campo.value.trim() !== '') {
        campo.classList.add('has-content');
    } else {
        campo.classList.remove('has-content');
    }
}

