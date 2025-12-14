import { toggleFocus } from '../shared/index.js';

// Aplica estilos de label flotante a los campos
export function configurarCampos() {
    const campos = document.querySelectorAll('.campo-entrada input, .campo-entrada select');
    campos.forEach(campo => {
        campo.addEventListener('input', () => toggleFocus(campo));
        campo.addEventListener('blur', () => toggleFocus(campo));
        campo.addEventListener('focus', () => toggleFocus(campo));
    });
}

