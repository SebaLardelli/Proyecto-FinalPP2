import { estado } from '../shared/index.js';

// Actualiza estado del botón de pago
export function actualizarBoton() {
    const boton = document.getElementById('realizar-pago');
    const esValido = estado.puntoRetiroSeleccionado && estado.metodosSeleccionados.length > 0;
    
    boton.disabled = !esValido;
    
    if (!estado.puntoRetiroSeleccionado) {
        boton.textContent = 'Selecciona punto de retiro';
    } else if (estado.metodosSeleccionados.length === 0) {
        boton.textContent = 'Selecciona método';
    } else {
        boton.textContent = 'Realizar Pago';
    }
}

