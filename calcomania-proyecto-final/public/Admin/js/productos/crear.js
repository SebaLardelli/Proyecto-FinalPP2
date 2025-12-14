import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarProductos } from './cargar.js';

// Crea un nuevo producto desde el formulario usando FormData para enviar datos del formulario incluyendo archivos de imagen
export async function crearProducto() {
    // Crear FormData para recopilar todos los datos del formulario (permite enviar archivos de imagen)
    const datosFormulario = new FormData();
    datosFormulario.append('id_categoria', document.getElementById('id_categoria').value);
    datosFormulario.append('id_tematica', document.getElementById('id_tematica').value || '');
    datosFormulario.append('nombre_p', document.getElementById('nombre_p').value);
    datosFormulario.append('tamanio', document.getElementById('tamanio').value);
    datosFormulario.append('precio', document.getElementById('precio').value);
    datosFormulario.append('stock', document.getElementById('stock').value);
    datosFormulario.append('descripcion_p', document.getElementById('descripcion_p').value);
    
    // Agregar imagen al FormData si el usuario seleccionó una imagen
    const imagen = document.getElementById('imagen_producto').files[0];
    if (imagen) {
        datosFormulario.append('imagen', imagen);
    }
    
    try {
        const headers = obtenerHeaders();
        // FormData no necesita Content-Type, el navegador lo establece automáticamente con el boundary necesario para multipart/form-data
        delete headers['Content-Type'];
        
        // Enviar petición POST al servidor para crear el producto con todos los datos del formulario
        const respuesta = await fetch(`${API_BASE}/productos`, {
            method: 'POST',
            credentials: 'include',
            headers: headers,
            body: datosFormulario
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            // Si la creación fue exitosa, mostrar mensaje de éxito, limpiar formulario y recargar la lista de productos
            mostrarMensaje('Producto creado correctamente', true);
            limpiarFormulario();
            cargarProductos();
        } else {
            mostrarMensaje(resultado.error || 'Error al crear producto');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

