import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';
import { estadoProducto } from './estado.js';
import { limpiarFormulario } from './limpiar.js';
import { cargarProductos } from './cargar.js';

// Actualiza un producto existente usando FormData para enviar datos del formulario incluyendo archivos de imagen (usa POST en lugar de PUT porque PHP no parsea multipart/form-data en $_POST para PUT)
export async function actualizarProducto() {
    // Obtener el ID del producto que se está editando desde el estado
    const productoEnEdicion = estadoProducto();
    if (!productoEnEdicion) return;
    
    // Validar que los campos requeridos (nombre, precio, stock, categoría) tengan valores
    const nombreP = document.getElementById('nombre_p').value.trim();
    const precio = document.getElementById('precio').value.trim();
    const stock = document.getElementById('stock').value.trim();
    const idCategoria = document.getElementById('id_categoria').value;
    
    if (!nombreP || !precio || !stock || !idCategoria) {
        mostrarMensaje('Por favor, completa todos los campos requeridos');
        return;
    }
    
    // Crear FormData para enviar datos del formulario (permite enviar archivos de imagen)
    const datosFormulario = new FormData();
    datosFormulario.append('id_categoria', idCategoria);
    datosFormulario.append('id_tematica', document.getElementById('id_tematica').value || '');
    datosFormulario.append('nombre_p', nombreP);
    datosFormulario.append('tamanio', document.getElementById('tamanio').value);
    datosFormulario.append('precio', precio);
    datosFormulario.append('stock', stock);
    datosFormulario.append('descripcion_p', document.getElementById('descripcion_p').value);
    
    // Agregar imagen al FormData si el usuario seleccionó una nueva imagen
    const imagen = document.getElementById('imagen_producto').files[0];
    if (imagen) {
        datosFormulario.append('imagen', imagen);
    }
    
    try {
        const headers = obtenerHeaders();
        delete headers['Content-Type']; // FormData establece el Content-Type automáticamente con el boundary necesario para multipart/form-data
        
        // Usar POST en lugar de PUT porque PHP no parsea multipart/form-data en $_POST para PUT (el backend detecta que es una actualización por el parámetro _method=PUT)
        datosFormulario.append('_method', 'PUT');
        datosFormulario.append('id_producto', productoEnEdicion);
        
        // Enviar petición POST al servidor con el FormData que contiene todos los datos del producto
        const respuesta = await fetch(`${API_BASE}/productos/${productoEnEdicion}`, {
            method: 'POST',
            credentials: 'include',
            headers: headers,
            body: datosFormulario
        });
        
        const resultado = await respuesta.json();
        
        if (resultado.ok) {
            // Si la actualización fue exitosa, mostrar mensaje de éxito, limpiar formulario y recargar la lista de productos
            mostrarMensaje('Producto actualizado correctamente', true);
            limpiarFormulario();
            cargarProductos();
        } else {
            mostrarMensaje(resultado.error || 'Error al actualizar producto');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión');
    }
}

