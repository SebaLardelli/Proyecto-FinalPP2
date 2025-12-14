import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarTematicas } from './cargar.js';

export async function crearTematica() {
    const datos = {
        nombre_t: document.getElementById('nombre-tematica').value,
        descripcion_t: document.getElementById('descripcion-tematica').value
    };
    
    try {
        const respuesta = await fetch(`${API_BASE}/tematicas?action=crear`, {
            method: 'POST',
            headers: obtenerHeaders(),
            credentials: 'include',
            body: JSON.stringify(datos)
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            mostrarMensaje('Temática creada correctamente', true);
            limpiarFormulario();
            cargarTematicas();
            
            if (window.adminSelects) {
                window.adminSelects.cargarTematicas();
            }
        } else {
            mostrarMensaje(resultado.error || 'Error al crear temática');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

