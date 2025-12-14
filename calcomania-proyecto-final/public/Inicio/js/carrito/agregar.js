import { API_BASE, estado, obtenerHeaders } from '../shared/index.js';
import { actualizarContador } from '../auth/actualizarContador.js';

// Agrega un producto al carrito
export async function agregarAlCarrito(idProducto) {
    if (!estado.sesion.autenticado) {
        alert('Debes iniciar sesión para agregar productos al carrito');
        return;
    }

    try {
        const producto = estado.productos.find(p => p.id_producto == idProducto);
        if (!producto) {
            alert('Producto no encontrado');
            return;
        }

        let tamano = null;
        const boton = document.querySelector(`.producto-agregar[data-id="${idProducto}"]`);
        
        if (boton) {
            const tarjeta = boton.closest('.producto');
            if (tarjeta) {
                const selectTamano = tarjeta.querySelector('.producto-tamano');
                if (selectTamano && selectTamano.value) {
                    const opcionSeleccionada = selectTamano.options[selectTamano.selectedIndex];
                    tamano = opcionSeleccionada.textContent.trim().split(' ')[0];
                } else if (producto.tamanio) {
                    tamano = producto.tamanio;
                }
            }
        }

        const respuesta = await fetch(`${API_BASE}/carrito/agregar`, {
            method: 'POST',
            headers: obtenerHeaders(),
            credentials: 'include',
            body: JSON.stringify({
                id_producto: idProducto,
                cantidad: 1,
                tamano: tamano
            })
        });

        const resultado = await respuesta.json();

        if (resultado.ok) {
            alert('✓ Producto agregado al carrito');
            await actualizarContador();
        } else {
            alert('Error: ' + (resultado.error || 'Error desconocido'));
        }

    } catch (error) {
        alert('Error de conexión');
    }
}

