// Carrito
import { cargarProductos, cambiarCantidad, eliminar, vaciar } from './carrito/index.js';
import { procesarCompra, configurarBotonPago } from './carrito/procesarCompra.js';

window.carritoUI = {
    cambiarCantidad,
    eliminar,
    vaciar
};

// Exponer procesarCompra globalmente para el onclick del HTML
window.procesarCompra = procesarCompra;

document.addEventListener('DOMContentLoaded', function() {
    const botonVaciar = document.getElementById('vaciar-carrito');
    if (botonVaciar) {
        botonVaciar.addEventListener('click', vaciar);
    }
    
    configurarBotonPago();
    cargarProductos();
});
