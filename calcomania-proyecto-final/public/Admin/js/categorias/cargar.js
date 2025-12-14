import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';

// Carga y muestra categorías en la tabla
export async function cargarCategorias() {
    try {
        const respuesta = await fetch(`${API_BASE}/categorias`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        const datos = await respuesta.json();
        
        if (!datos.ok) return;
        
        const tabla = document.getElementById('tabla-categorias');
        tabla.innerHTML = '';
        
        datos.categorias.forEach(categoria => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${categoria.nombre_c}</td>
                <td>${categoria.descripcion_c || '-'}</td>
                <td class="acciones">
                    <button onclick="window.adminCategorias.editar(${categoria.id_categoria}, '${categoria.nombre_c}', '${categoria.descripcion_c}')" 
                            style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">
                        Editar
                    </button>
                    <button onclick="window.adminCategorias.eliminar(${categoria.id_categoria})" 
                            style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                        Eliminar
                    </button>
                </td>
            `;
            tabla.appendChild(fila);
        });
    } catch (error) {
        mostrarMensaje('Error cargando categorías');
    }
}

