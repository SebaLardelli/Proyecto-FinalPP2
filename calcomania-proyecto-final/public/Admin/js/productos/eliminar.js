import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { cargarProductos } from './cargar.js';

// Elimina un producto de la base de datos solicitando confirmación antes de eliminar y enviando una petición DELETE al servidor
export async function eliminar(id) {
    // Solicitar confirmación antes de eliminar el producto
    if (!confirm('¿Estás seguro de eliminar este producto?')) return;
    
    try {
        // Enviar petición DELETE al servidor para eliminar el producto con el ID especificado
        const respuesta = await fetch(`${API_BASE}/productos/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            // Si la eliminación fue exitosa, mostrar mensaje de éxito y recargar la lista de productos
            mostrarMensaje('Producto eliminado correctamente', true);
            cargarProductos();
        } else {
            mostrarMensaje(resultado.error || 'Error al eliminar producto');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

