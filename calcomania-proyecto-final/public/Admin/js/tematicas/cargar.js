import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';

export async function cargarTematicas() {
    try {
        const respuesta = await fetch(`${API_BASE}/tematicas`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        const datos = await respuesta.json();
        
        if (!datos.ok) return;
        
        const tabla = document.getElementById('tabla-tematicas');
        tabla.innerHTML = '';
        
        datos.tematicas.forEach(tematica => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${tematica.nombre_t}</td>
                <td>${tematica.descripcion_t || '-'}</td>
                <td class="acciones">
                    <button onclick="window.adminTematicas.editar(${tematica.id_tematica}, '${tematica.nombre_t}', '${tematica.descripcion_t}')" 
                            style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">
                        Editar
                    </button>
                    <button onclick="window.adminTematicas.eliminar(${tematica.id_tematica})" 
                            style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                        Eliminar
                    </button>
                </td>
            `;
            tabla.appendChild(fila);
        });
    } catch (error) {
        mostrarMensaje('Error cargando temáticas');
    }
}

