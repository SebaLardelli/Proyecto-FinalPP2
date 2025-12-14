import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { cargarCategorias } from './cargar.js';

// Elimina una categoría de la base de datos solicitando confirmación antes de eliminar y enviando una petición DELETE al servidor
export async function eliminar(id) {
    // Solicitar confirmación antes de eliminar
    if (!confirm('¿Estás seguro de eliminar esta categoría?')) return;
    
    try {
        // Enviar petición DELETE al servidor para eliminar la categoría con el ID especificado
        const respuesta = await fetch(`${API_BASE}/categorias/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            // Si la eliminación fue exitosa, mostrar mensaje, recargar la lista y actualizar el select de categorías
            mostrarMensaje('Categoría eliminada correctamente', true);
            cargarCategorias();
            
            if (window.adminSelects) {
                window.adminSelects.cargarCategorias();
            }
        } else {
            mostrarMensaje(resultado.error || 'Error al eliminar categoría');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

