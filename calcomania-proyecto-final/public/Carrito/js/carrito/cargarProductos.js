import * as API from '../api/index.js';
import * as UI from '../ui/index.js';

// Carga productos del carrito
export async function cargarProductos() {
    try {
        const datos = await API.obtenerCarrito();
        
        if (datos && datos.items && datos.items.length > 0) {
            UI.mostrarCarritoConProductos(datos);
        } else {
            UI.mostrarCarritoVacio();
        }
    } catch (error) {
        UI.mostrarCarritoVacio();
    }
}

