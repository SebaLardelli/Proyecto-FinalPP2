import * as API from '../api/index.js';
import { cargarProductos } from './cargarProductos.js';

// Elimina una fila específica del carrito usando id_detalle (id_fila)
export async function eliminar(idDetalle) {
    if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
        return;
    }
    
    try {
        await API.eliminarProductoPorDetalle(idDetalle);
        alert('Producto eliminado del carrito');
        cargarProductos();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

