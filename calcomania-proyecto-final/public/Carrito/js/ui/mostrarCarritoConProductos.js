import { agruparProductos } from './agruparProductos.js';
import { crearTarjetaProducto } from './crearTarjetaProducto.js';
import { actualizarTotales } from './actualizarTotales.js';

// Muestra el carrito con productos
export function mostrarCarritoConProductos(datos) {
    const carritoVacio = document.getElementById('carrito-vacio');
    const carritoContenido = document.getElementById('carrito-contenido');
    const botonPago = document.getElementById('boton-pago-container');
    
    if (carritoVacio) carritoVacio.style.display = 'none';
    if (carritoContenido) carritoContenido.style.display = 'block';
    if (botonPago) botonPago.style.display = 'block';
    
    const productosAgrupados = agruparProductos(datos.items);
    
    const lista = document.getElementById('lista-productos');
    if (lista) {
        lista.innerHTML = Object.values(productosAgrupados).map(grupo => crearTarjetaProducto(grupo)).join('');
    }
    
    actualizarTotales(datos);
}

