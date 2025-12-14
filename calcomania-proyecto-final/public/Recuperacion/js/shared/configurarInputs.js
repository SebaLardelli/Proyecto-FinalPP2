// Verifica si un elemento tiene contenido
export function tieneContenido(elemento) {
    return elemento && elemento.value && elemento.value.trim() !== '';
}

// Configura el evento input para alternar clase has-content
export function configurarInputEmail(input) {
    if (!input) return;
    
    input.addEventListener('input', () => {
        input.classList.toggle('has-content', tieneContenido(input));
    });
    
    // Aplicar estado inicial
    input.classList.toggle('has-content', tieneContenido(input));
}

