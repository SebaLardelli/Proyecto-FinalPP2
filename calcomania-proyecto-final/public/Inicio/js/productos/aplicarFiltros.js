import { estado } from '../shared/index.js';
import { mostrarProductos } from './mostrar.js';

// Aplica filtros activos (categoría + temáticas)
export function aplicarFiltros() {
    let productosFiltrados = estado.productos;
    
    if (estado.categoriaActual !== 'todos' && estado.categoriaActual.startsWith('categoria')) {
        const categoriaId = parseInt(estado.categoriaActual.replace('categoria', ''));
        productosFiltrados = productosFiltrados.filter(p => p.id_categoria == categoriaId);
    }
    
    if (estado.tematicasSeleccionadas.length > 0) {
        productosFiltrados = productosFiltrados.filter(p => 
            estado.tematicasSeleccionadas.includes(p.id_tematica)
        );
    }
    
    mostrarProductos(productosFiltrados);
}

