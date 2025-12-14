import { API_BASE, estado, obtenerHeaders } from '../shared/index.js';

// Actualiza contador de productos en el carrito
export async function actualizarContador() {
    const contador = document.getElementById('numerito');
    if (!contador) return;
    
    if (!estado.sesion.autenticado) {
        contador.textContent = '0';
        return;
    }

    try {
        const respuesta = await fetch(`${API_BASE}/carrito`, {
            headers: obtenerHeaders(),
            credentials: 'include' 
        });

        if (respuesta.ok) {
            const datos = await respuesta.json();
            const total = datos.items ? datos.items.reduce((suma, item) => suma + parseInt(item.cantidad), 0) : 0;
            contador.textContent = total;
        } else {
            contador.textContent = '0';
        }
    } catch (error) {
        contador.textContent = '0';
    }
}

