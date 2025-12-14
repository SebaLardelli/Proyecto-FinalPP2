import * as API from '../api/index.js';
import { cargarProductos } from './cargarProductos.js';

// Cambia la cantidad de un producto en el carrito usando id_detalle (id_fila) - si la cantidad llega a 0, elimina el producto
export async function cambiarCantidad(idDetalle, cambio) {
    try {
        const datos = await API.actualizarCantidadPorDetalle(idDetalle, cambio);
        
        if (datos.error) {
            let mensajeError = datos.error;
            if (typeof mensajeError === 'string') {
                try {
                    const errorParsed = JSON.parse(mensajeError);
                    mensajeError = errorParsed.error || mensajeError;
                } catch (e) {
                    // Si no es JSON, usar el mensaje tal cual
                }
            }
            alert(mensajeError || 'Error al actualizar cantidad');
            return;
        }
        
        cargarProductos();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

