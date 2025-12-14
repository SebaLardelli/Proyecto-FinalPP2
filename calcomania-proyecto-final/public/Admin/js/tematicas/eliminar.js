import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { cargarTematicas } from './cargar.js';

export async function eliminar(id) {
    if (!confirm('¿Estás seguro de eliminar esta temática?')) return;
    
    try {
        const respuesta = await fetch(`${API_BASE}/tematicas/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            mostrarMensaje('Temática eliminada correctamente', true);
            cargarTematicas();
        } else {
            mostrarMensaje(resultado.error || 'Error al eliminar temática');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

