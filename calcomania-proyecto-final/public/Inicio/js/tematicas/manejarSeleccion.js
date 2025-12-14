import { estado } from '../shared/index.js';
import { actualizarEtiquetas } from './actualizarEtiquetas.js';
import { aplicarFiltros } from '../productos/aplicarFiltros.js';

// Maneja selección de una temática
export function manejarSeleccion() {
    const selector = document.getElementById('selector-tematica');
    const tematicaId = parseInt(selector.value);
    
    if (tematicaId && !estado.tematicasSeleccionadas.includes(tematicaId)) {
        estado.tematicasSeleccionadas.push(tematicaId);
        selector.value = '';
        actualizarEtiquetas();
        aplicarFiltros();
    }
}

