import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { estadoCategoria } from './estado.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarCategorias } from './cargar.js';

// Actualiza una categoría existente en la base de datos obteniendo los datos del formulario y enviando una petición PUT al servidor
export async function actualizarCategoria() {
    // Obtener el ID de la categoría que se está editando
    const categoriaEnEdicion = estadoCategoria();
    if (!categoriaEnEdicion) return;
    
    // Recopilar los datos del formulario (nombre y descripción)
    const datos = {
        nombre_c: document.getElementById('nombre-categoria').value,
        descripcion_c: document.getElementById('descripcion-categoria').value
    };
    
    try {
        // Obtener headers con el token JWT para autenticación y enviar petición PUT al servidor
        const headers = obtenerHeaders();
        const respuesta = await fetch(`${API_BASE}/categorias/${categoriaEnEdicion}`, {
            method: 'PUT',
            headers: headers,
            credentials: 'include',
            body: JSON.stringify(datos)
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            // Si la actualización fue exitosa, mostrar mensaje, limpiar formulario, recargar la lista y actualizar el select de categorías
            mostrarMensaje('Categoría actualizada correctamente', true);
            limpiarFormulario();
            cargarCategorias();
            
            if (window.adminSelects) {
                window.adminSelects.cargarCategorias();
            }
        } else {
            mostrarMensaje(resultado.error || 'Error al actualizar categoría');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

