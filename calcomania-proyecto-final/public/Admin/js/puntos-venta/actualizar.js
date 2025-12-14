import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { estadoPuntoVenta } from './estado.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarPuntosVenta } from './cargar.js';

export async function actualizarPuntoVenta() {
    const puntoEnEdicion = estadoPuntoVenta();
    if (!puntoEnEdicion) return;
    
    const datos = {
        nombre_punto: document.getElementById('nombre-pos').value,
        direccion: document.getElementById('direccion-pos').value,
        horarios: document.getElementById('horarios-pos').value || '',
        codigo_postal: document.getElementById('codigo-postal-pos').value || ''
    };
    
    try {
        const respuesta = await fetch(`${API_BASE}/puntos-venta/${puntoEnEdicion}`, {
            method: 'PUT',
            headers: obtenerHeaders(),
            credentials: 'include',
            body: JSON.stringify(datos)
        });
        
        const resultado = await respuesta.json();
        
        if (respuesta.ok && resultado.ok) {
            mostrarMensaje('Punto de retiro actualizado correctamente', true);
            limpiarFormulario();
            cargarPuntosVenta();
        } else {
            const mensajeError = resultado.error || 'Error al actualizar punto de retiro';
            mostrarMensaje(mensajeError);
        }
    } catch (error) {
        console.error('Error al actualizar punto de retiro:', error);
        mostrarMensaje('Error de conexión: ' + error.message);
    }
}

