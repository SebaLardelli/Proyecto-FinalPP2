import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { cargarMetodosPago } from './cargar.js';

export async function eliminar(id) {
    if (!confirm('¿Estás seguro de eliminar este método de pago?')) return;
    
    try {
        const respuesta = await fetch(`${API_BASE}/metodos-pago/${id}`, {
            method: 'DELETE',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            mostrarMensaje('Método de pago eliminado correctamente', true);
            cargarMetodosPago();
        } else {
            mostrarMensaje(resultado.error || 'Error al eliminar método de pago');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

