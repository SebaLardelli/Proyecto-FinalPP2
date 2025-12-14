import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';

export async function cargarPuntosVenta() {
    try {
        const respuesta = await fetch(`${API_BASE}/puntos-venta`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        
        if (!respuesta.ok) {
            if (respuesta.status === 401) {
                mostrarMensaje('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                setTimeout(() => {
                    localStorage.removeItem('jwt_token');
                    window.location.href = '/calcomania-proyecto-final/Login';
                }, 2000);
            } else {
                mostrarMensaje('Error al cargar puntos de retiro: ' + respuesta.status);
            }
            return;
        }
        
        const datos = await respuesta.json();
        
        if (!datos.ok || !datos.puntos) return;
        
        const tabla = document.getElementById('tabla-pos');
        tabla.innerHTML = '';
        
        // Ordenar por nombre
        datos.puntos.sort((a, b) => {
            const nombreA = (a.nombre_punto || '').toLowerCase();
            const nombreB = (b.nombre_punto || '').toLowerCase();
            return nombreA.localeCompare(nombreB);
        });
        
        datos.puntos.forEach(punto => {
            const fila = document.createElement('tr');
            
            // Convertir comillas simples en \' para que no rompan el código JavaScript del onclick
            const convertirComillas = (texto) => (texto || '').replace(/'/g, "\\'");
            
            // Preparar datos sin comillas problemáticas para usar en onclick
            const nombreSinComillas = convertirComillas(punto.nombre_punto);
            const direccionSinComillas = convertirComillas(punto.direccion);
            const horariosSinComillas = convertirComillas(punto.horarios);
            const codigoPostalSinComillas = convertirComillas(punto.codigo_postal);
            
            fila.innerHTML = `
                <td>${punto.nombre_punto || '-'}</td>
                <td>${punto.direccion || '-'}</td>
                <td>${punto.horarios || '-'}</td>
                <td>${punto.codigo_postal || '-'}</td>
                <td class="acciones">
                    <button onclick="window.adminPuntosVenta.editar(${punto.id_punto_retiro}, '${nombreSinComillas}', '${direccionSinComillas}', '${horariosSinComillas}', '${codigoPostalSinComillas}')" 
                            style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">Editar</button>
                    <button onclick="window.adminPuntosVenta.eliminar(${punto.id_punto_retiro})" 
                            style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Eliminar</button>
                </td>
            `;
            tabla.appendChild(fila);
        });
    } catch (error) {
        mostrarMensaje('Error cargando puntos de retiro');
    }
}

