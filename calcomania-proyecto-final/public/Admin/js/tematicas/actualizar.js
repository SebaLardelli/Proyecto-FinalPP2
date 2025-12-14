import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { estadoTematica } from './estado.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarTematicas } from './cargar.js';

export async function actualizarTematica() {
    const tematicaEnEdicion = estadoTematica();
    if (!tematicaEnEdicion) return;
    
    const datos = {
        nombre_t: document.getElementById('nombre-tematica').value,
        descripcion_t: document.getElementById('descripcion-tematica').value
    };
    
    try {
        const respuesta = await fetch(`${API_BASE}/tematicas/${tematicaEnEdicion}`, {
            method: 'PUT',
            headers: obtenerHeaders(),
            credentials: 'include',
            body: JSON.stringify(datos)
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            mostrarMensaje('Temática actualizada correctamente', true);
            limpiarFormulario();
            cargarTematicas();
        } else {
            mostrarMensaje(resultado.error || 'Error al actualizar temática');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

