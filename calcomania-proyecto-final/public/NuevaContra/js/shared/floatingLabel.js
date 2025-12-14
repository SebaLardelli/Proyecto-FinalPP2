// Configura labels flotantes para inputs
export function configurarLabelsFlotantes(inputs) {
    if (!inputs || inputs.length === 0) return;
    
    function aplicarFocus(input) {
        if (!input) return;
        input.classList.toggle('has-content', input.value.trim() !== '');
    }
    
    inputs.forEach(input => {
        if (input) {
            input.addEventListener('input', () => aplicarFocus(input));
            window.addEventListener('DOMContentLoaded', () => aplicarFocus(input));
        }
    });
}

