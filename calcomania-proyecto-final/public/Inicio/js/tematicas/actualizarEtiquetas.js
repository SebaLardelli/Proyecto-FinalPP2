import { estado } from '../shared/index.js';

// Actualiza etiquetas de temáticas seleccionadas
export function actualizarEtiquetas() {
    const contenedor = document.getElementById('tematicas-seleccionadas');
    if (!contenedor) return;
    
    if (estado.tematicasSeleccionadas.length === 0) {
        contenedor.innerHTML = '<span class="tematicas-vacio">Ninguna temática seleccionada</span>';
    } else {
        const etiquetas = estado.tematicasSeleccionadas.map(id => {
            const tematica = estado.tematicasDisponibles.find(t => t.id_tematica === id);
            const nombre = tematica ? tematica.nombre_t : `Temática ${id}`;
            return `<span class="tematica-tag" onclick="window.inicioTematicas.remover(${id})">${nombre}</span>`;
        }).join('');
        contenedor.innerHTML = etiquetas;
    }
}

