import { API_BASE, estado, obtenerHeaders } from '../shared/index.js';

// Carga métodos de pago y puntos de retiro
export async function cargarDatos() {
    try {
        const headers = obtenerHeaders();
        const responseMetodos = await fetch(`${API_BASE}/metodos-pago`, {
            method: 'GET',
            credentials: 'include',
            headers: headers
        });
        
        if (responseMetodos.ok) {
            const data = await responseMetodos.json();
            if (data.ok && data.datos) {
                estado.metodosPago = data.datos;
            }
        }
        
        const responsePuntos = await fetch(`${API_BASE}/puntos-retiro`, {
            method: 'GET',
            credentials: 'include',
            headers: headers
        });
        
        if (responsePuntos.ok) {
            const data = await responsePuntos.json();
            if (data.ok && data.datos) {
                estado.puntosRetiro = data.datos;
            }
        }
    } catch (error) {
        alert('Error al cargar los datos');
    }
}

