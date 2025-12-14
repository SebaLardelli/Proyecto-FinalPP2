import { estado } from '../shared/index.js';
import { cargarTematicas } from './cargar.js';
import { aplicarFiltros } from '../productos/aplicarFiltros.js';

// Remueve una temática seleccionada
export function remover(tematicaId) {
    estado.tematicasSeleccionadas = estado.tematicasSeleccionadas.filter(id => id !== tematicaId);
    cargarTematicas();
    aplicarFiltros();
}

