import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { estadoMetodoPago } from './estado.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarMetodosPago } from './cargar.js';

export async function actualizarMetodoPago() {
    const metodoEnEdicion = estadoMetodoPago();
    if (!metodoEnEdicion) return;
    
    const datos = {
        descripcion_mp: document.getElementById('descripcion-metodo-pago').value
    };
    
    try {
        const respuesta = await fetch(`${API_BASE}/metodos-pago/${metodoEnEdicion}`, {
            method: 'PUT',
            headers: obtenerHeaders(),
            credentials: 'include',
            body: JSON.stringify(datos)
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            mostrarMensaje('Método de pago actualizado correctamente', true);
            limpiarFormulario();
            cargarMetodosPago();
        } else {
            mostrarMensaje(resultado.error || 'Error al actualizar método de pago');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

