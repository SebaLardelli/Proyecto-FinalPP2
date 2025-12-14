import { obtenerHeaders } from '../shared/index.js';

// Obtiene el contenido del carrito
export async function obtenerCarrito() {
    const respuesta = await fetch('/calcomania-proyecto-final/api/carrito', {
        method: 'GET',
        headers: obtenerHeaders(),
        credentials: 'include'
    });
    
    if (respuesta.ok) {
        return await respuesta.json();
    }
    return null;
}

