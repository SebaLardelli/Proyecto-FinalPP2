import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { cargarPuntosVenta } from './cargar.js';

export async function eliminar(id) {
    if (!confirm('¿Estás seguro de eliminar este punto de retiro?')) return;
    
    try {
        const respuesta = await fetch(`${API_BASE}/puntos-venta/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        
        const resultado = await respuesta.json();
        
        if (respuesta.ok && resultado.ok) {
            mostrarMensaje('Punto de retiro eliminado correctamente', true);
            cargarPuntosVenta();
        } else {
            const mensajeError = resultado.error || 'Error al eliminar punto de retiro';
            mostrarMensaje(mensajeError);
        }
    } catch (error) {
        console.error('Error al eliminar punto de retiro:', error);
        mostrarMensaje('Error de conexión: ' + error.message);
    }
}

