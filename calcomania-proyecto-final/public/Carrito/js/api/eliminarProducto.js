import { obtenerHeaders } from '../shared/index.js';

// Elimina una fila específica del carrito usando id_detalle (id_fila) - elimina solo esa fila
export async function eliminarProductoPorDetalle(idDetalle) {
    const respuesta = await fetch('/calcomania-proyecto-final/api/carrito/eliminar', {
        method: 'DELETE',
        headers: obtenerHeaders(),
        credentials: 'include',
        body: JSON.stringify({
            id_detalle: idDetalle
        })
    });
    
    if (respuesta.ok) {
        return await respuesta.json();
    }
    throw new Error('Error al eliminar producto');
}

