// Muestra mensaje de carrito vacío
export function mostrarCarritoVacio() {
    const carritoVacio = document.getElementById('carrito-vacio');
    const carritoContenido = document.getElementById('carrito-contenido');
    const botonPago = document.getElementById('boton-pago-container');
    
    if (carritoVacio) carritoVacio.style.display = 'block';
    if (carritoContenido) carritoContenido.style.display = 'none';
    if (botonPago) botonPago.style.display = 'none';
    
    const total = document.getElementById('total');
    const contador = document.getElementById('numerito');
    
    if (total) total.textContent = '$0.00';
    if (contador) contador.textContent = '0';
}

