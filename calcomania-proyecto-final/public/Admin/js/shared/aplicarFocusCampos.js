import { toggleFocus } from './toggleFocus.js';

// Aplica toggleFocus a todos los campos
export function aplicarFocusCampos() {
    const campos = document.querySelectorAll('.campo-entrada input, .campo-entrada select');
    campos.forEach(campo => toggleFocus(campo));
}

