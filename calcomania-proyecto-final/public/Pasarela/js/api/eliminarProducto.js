import { API_BASE, obtenerHeaders } from '../shared/index.js';

// Elimina un producto del carrito
export async function eliminarProducto(idFila) {
    if (!confirm('¿Eliminar este producto del carrito?')) return;
    
    const id = parseInt(idFila);
    if (!id || id <= 0) {
        alert('ID de producto inválido: ' + idFila);
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/carrito`, {
            method: 'DELETE',
            credentials: 'include',
            headers: obtenerHeaders(),
            body: JSON.stringify({ id_detalle: id })
        });
        
        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch (e) {
            alert('Error de comunicación con el servidor');
            return;
        }
        
        if (response.ok && data.ok) {
            window.location.reload();
        } else {
            alert(data.error || 'Error al eliminar producto');
        }
    } catch (error) {
        alert('Error al eliminar producto: ' + error.message);
    }
}

