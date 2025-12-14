import { API_BASE, obtenerHeaders } from '../shared/index.js';

export async function cargarTematicas() {
    try {
        const respuesta = await fetch(`${API_BASE}/tematicas`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        const datos = await respuesta.json();
        
        if (!datos.ok || !datos.tematicas) return;
        
        const select = document.getElementById('id_tematica');
        if (!select) return;
        
        select.innerHTML = '<option value="">Seleccionar Temática</option>';
        datos.tematicas.forEach(tematica => {
            const opcion = document.createElement('option');
            opcion.value = tematica.id_tematica;
            opcion.textContent = tematica.nombre_t;
            select.appendChild(opcion);
        });
    } catch (error) {
        // Error silencioso
    }
}

