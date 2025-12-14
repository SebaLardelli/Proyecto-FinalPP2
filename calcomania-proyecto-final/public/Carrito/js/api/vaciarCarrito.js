import { obtenerHeaders } from '../shared/index.js';

// Vacía el carrito completamente eliminando todos los productos del carrito
export async function vaciarCarrito() {
    const respuesta = await fetch('/calcomania-proyecto-final/api/carrito', {
        method: 'DELETE',
        credentials: 'include',
        headers: obtenerHeaders()
    });
    
    if (!respuesta.ok) {
        const errorText = await respuesta.text();
        throw new Error(errorText || 'Error al vaciar el carrito');
    }
    
    return await respuesta.json();
}

