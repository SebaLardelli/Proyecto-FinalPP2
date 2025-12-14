import { obtenerHeaders } from '../shared/index.js';

// Actualiza la cantidad de un producto en el carrito usando id_detalle (id_fila) - cambio puede ser positivo para agregar o negativo para quitar, si llega a 0 se elimina
export async function actualizarCantidadPorDetalle(idDetalle, cambio) {
    const respuesta = await fetch('/calcomania-proyecto-final/api/carrito/actualizar', {
        method: 'PUT',
        credentials: 'include',
        headers: obtenerHeaders(),
        body: JSON.stringify({
            id_detalle: parseInt(idDetalle),
            cantidad: parseInt(cambio)
        })
    });
    
    if (!respuesta.ok) {
        let errorText = await respuesta.text();
        try {
            const errorJson = JSON.parse(errorText);
            errorText = errorJson.error || errorText;
        } catch (e) {
            // Si no es JSON, usar el texto tal cual
        }
        throw new Error(errorText || 'Error al actualizar cantidad');
    }
    
    return await respuesta.json();
}

