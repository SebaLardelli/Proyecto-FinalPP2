import { estado } from '../shared/index.js';
import { actualizarBoton } from '../ui/index.js';

// Selecciona un punto de retiro
export function seleccionarPunto(idPunto) {
    document.querySelectorAll('.punto-retiro').forEach(el => el.classList.remove('selected'));
    
    const puntoElement = document.querySelector(`[data-punto-id="${idPunto}"]`);
    if (puntoElement) {
        puntoElement.classList.add('selected');
        const radio = puntoElement.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
        estado.puntoRetiroSeleccionado = idPunto;
    }
    
    actualizarBoton();
}

