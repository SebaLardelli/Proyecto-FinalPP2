import * as API from '../api/index.js';
import * as UI from '../ui/index.js';

// Vacía el carrito eliminando todos los productos que se cargaron al carrito
export async function vaciar() {
    if (!confirm('¿Estás seguro de que quieres vaciar todo el carrito? Se eliminarán todos los productos.')) {
        return;
    }
    
    try {
        await API.vaciarCarrito();
        alert('Carrito vaciado completamente');
        UI.mostrarCarritoVacio();
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

