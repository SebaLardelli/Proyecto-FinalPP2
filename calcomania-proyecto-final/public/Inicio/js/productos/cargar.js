import { API_BASE, estado } from '../shared/index.js';
import { mostrarProductos } from './mostrar.js';

// Carga todos los productos desde la API
export async function cargarProductos() {
    try {
        const respuesta = await fetch(`${API_BASE}/productos`);
        
        if (!respuesta.ok) {
            mostrarProductos([]);
            return [];
        }
        
        const datos = await respuesta.json();
        
        if (datos.ok) {
            estado.productos = datos.productos || [];
            mostrarProductos(estado.productos);
            return estado.productos;
        } else {
            mostrarProductos([]);
            return [];
        }
    } catch (error) {
        mostrarProductos([]);
        return [];
    }
}

