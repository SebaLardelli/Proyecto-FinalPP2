import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';

export async function cargarMetodosPago() {
    try {
        const respuesta = await fetch(`${API_BASE}/metodos-pago`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        const datos = await respuesta.json();
        
        if (!datos.ok || !datos.metodos) return;
        
        const tabla = document.getElementById('tabla-metodos-pago');
        tabla.innerHTML = '';
        
        datos.metodos.forEach(metodo => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${metodo.descripcion_mp}</td>
                <td class="acciones">
                    <button onclick="window.adminMetodosPago.editar(${metodo.id_metodo_pago}, '${metodo.descripcion_mp}')" 
                            style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">Editar</button>
                    <button onclick="window.adminMetodosPago.eliminar(${metodo.id_metodo_pago})" 
                            style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Eliminar</button>
                </td>
            `;
            tabla.appendChild(fila);
        });
    } catch (error) {
        mostrarMensaje('Error cargando métodos de pago');
    }
}

