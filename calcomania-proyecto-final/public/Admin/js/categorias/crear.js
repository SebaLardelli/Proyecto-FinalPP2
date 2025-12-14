import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarCategorias } from './cargar.js';

// Crea una nueva categoría en la base de datos obteniendo los datos del formulario y enviando una petición POST al servidor
export async function crearCategoria() {
    // Recopilar los datos del formulario (nombre y descripción)
    const datos = {
        nombre_c: document.getElementById('nombre-categoria').value,
        descripcion_c: document.getElementById('descripcion-categoria').value
    };
    
    try {
        // Enviar petición POST al servidor para crear la categoría con los datos del formulario
        const respuesta = await fetch(`${API_BASE}/categorias`, {
            method: 'POST',
            headers: obtenerHeaders(),
            credentials: 'include',
            body: JSON.stringify(datos)
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            // Si la creación fue exitosa, mostrar mensaje, limpiar formulario, recargar la lista y actualizar el select de categorías
            mostrarMensaje('Categoría creada correctamente', true);
            limpiarFormulario();
            cargarCategorias();
            
            if (window.adminSelects) {
                window.adminSelects.cargarCategorias();
            }
        } else {
            mostrarMensaje(resultado.error || 'Error al crear categoría');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

