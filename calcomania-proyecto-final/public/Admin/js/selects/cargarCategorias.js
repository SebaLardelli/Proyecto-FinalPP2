import { API_BASE, obtenerHeaders } from '../shared/index.js';

export async function cargarCategorias() {
    try {
        const respuesta = await fetch(`${API_BASE}/categorias`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        const datos = await respuesta.json();
        
        if (!datos.ok || !datos.categorias) return;
        
        const select = document.getElementById('id_categoria');
        if (!select) return;
        
        select.innerHTML = '<option value="">Seleccionar Categoría</option>';
        datos.categorias.forEach(categoria => {
            const opcion = document.createElement('option');
            opcion.value = categoria.id_categoria;
            opcion.textContent = categoria.nombre_c;
            select.appendChild(opcion);
        });
    } catch (error) {
        // Error silencioso
    }
}

