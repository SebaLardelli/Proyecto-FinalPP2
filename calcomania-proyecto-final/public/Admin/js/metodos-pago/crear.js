import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarMetodosPago } from './cargar.js';

export async function crearMetodoPago() {
    const datos = {
        descripcion_mp: document.getElementById('descripcion-metodo-pago').value
    };
    
    try {
        const respuesta = await fetch(`${API_BASE}/metodos-pago?action=crear`, {
            method: 'POST',
            headers: obtenerHeaders(),
            credentials: 'include',
            body: JSON.stringify(datos)
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            mostrarMensaje('Método de pago creado correctamente', true);
            limpiarFormulario();
            cargarMetodosPago();
        } else {
            mostrarMensaje(resultado.error || 'Error al crear método de pago');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

