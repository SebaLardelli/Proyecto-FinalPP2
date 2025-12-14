import { estado } from '../shared/index.js';
import { actualizarBoton } from '../ui/index.js';
import { mostrarPagoCombinado } from '../ui/index.js';
import { configurarInputsPagoCombinado } from './configurarInputsPagoCombinado.js';

// Selecciona/deselecciona método de pago (máximo 2)
export function toggleMetodo(idMetodo) {
    const checkbox = document.getElementById(`metodo-${idMetodo}`);
    if (!checkbox) return;
    
    const metodoElement = checkbox.closest('.metodo-pago');
    if (!metodoElement) return;
    
    if (estado.metodosSeleccionados.includes(idMetodo)) {
        checkbox.checked = false;
        metodoElement.classList.remove('selected');
        estado.metodosSeleccionados = estado.metodosSeleccionados.filter(id => id != idMetodo);
    } else {
        if (estado.metodosSeleccionados.length >= 2) {
            alert('Solo puedes seleccionar máximo 2 métodos de pago');
            return;
        }
        checkbox.checked = true;
        metodoElement.classList.add('selected');
        estado.metodosSeleccionados.push(idMetodo);
    }
    
    actualizarBoton();
    mostrarPagoCombinado(configurarInputsPagoCombinado);
}

