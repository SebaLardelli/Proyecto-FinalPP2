import { API_BASE, estado } from '../shared/index.js';
import { actualizarEtiquetas } from './actualizarEtiquetas.js';

// Carga temáticas disponibles
export async function cargarTematicas() {
    const selector = document.getElementById('selector-tematica');
    if (!selector) return;

    try {
        const respuesta = await fetch(`${API_BASE}/tematicas`);
        if (!respuesta.ok) {
            throw new Error("No se pudieron cargar las temáticas");
        }
        
        const datos = await respuesta.json();
        const todasLasTematicas = datos.tematicas || [];
        
        let tematicasFiltradas = [];
        
        if (estado.categoriaActual === 'todos') {
            tematicasFiltradas = todasLasTematicas;
        } else {
            const categoriaId = parseInt(estado.categoriaActual.replace('categoria', ''));
            
            if (estado.productos.length > 0) {
                tematicasFiltradas = todasLasTematicas.filter(tematica => {
                    return estado.productos.some(producto => 
                        producto.id_categoria == categoriaId && 
                        producto.id_tematica == tematica.id_tematica
                    );
                });
            } else {
                tematicasFiltradas = todasLasTematicas;
            }
        }
        
        estado.tematicasDisponibles = tematicasFiltradas;
        
        selector.innerHTML = '<option value="">Seleccionar temática...</option>';
        
        tematicasFiltradas.forEach(tematica => {
            if (!estado.tematicasSeleccionadas.includes(tematica.id_tematica)) {
                const opcion = document.createElement('option');
                opcion.value = tematica.id_tematica;
                opcion.textContent = tematica.nombre_t;
                selector.appendChild(opcion);
            }
        });
        
        actualizarEtiquetas();
        
    } catch (error) {
        selector.innerHTML = '<option value="">Error cargando temáticas</option>';
    }
}

