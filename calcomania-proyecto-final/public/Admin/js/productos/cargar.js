import { API_BASE, mostrarMensaje, obtenerHeaders } from '../shared/index.js';

// Carga y muestra todos los productos en la tabla del panel de administración (incluye productos con stock 0, a diferencia de la vista pública)
export async function cargarProductos() {
    try {
        const headers = obtenerHeaders();
        const token = localStorage.getItem('jwt_token');
        
        // Verificar que el token JWT no esté expirado antes de hacer la petición al servidor
        if (token) {
            try {
                const parts = token.split('.');
                if (parts.length === 3) {
                    const payloadBase64 = parts[1];
                    const padding = payloadBase64.length % 4;
                    const paddedBase64 = padding ? payloadBase64 + '='.repeat(4 - padding) : payloadBase64;
                    const payload = JSON.parse(atob(paddedBase64));
                    const ahora = Math.floor(Date.now() / 1000);
                    const tiempoRestante = payload.exp - ahora;
                    
                    // Si el token está expirado, mostrar mensaje y redirigir al login
                    if (tiempoRestante <= 0) {
                        mostrarMensaje('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                        setTimeout(() => {
                            localStorage.removeItem('jwt_token');
                            window.location.href = '/calcomania-proyecto-final/Login';
                        }, 2000);
                        return;
                    }
                }
            } catch (e) {
                // Error silencioso en verificación de token (continuar con la petición)
            }
        }
        
        // Solicitar todos los productos al servidor usando la ruta protegida para administradores
        const respuesta = await fetch(`${API_BASE}/productos/admin`, {
            method: 'GET',
            credentials: 'include',
            headers: headers
        });
        
        // Manejar errores de autenticación (401) o otros errores HTTP
        if (!respuesta.ok) {
            if (respuesta.status === 401) {
                mostrarMensaje('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
                setTimeout(() => {
                    localStorage.removeItem('jwt_token');
                    window.location.href = '/calcomania-proyecto-final/Login';
                }, 2000);
            } else {
                mostrarMensaje('Error al cargar productos: ' + respuesta.status);
            }
            return;
        }
        
        // Parsear la respuesta JSON del servidor
        let datos;
        try {
            datos = await respuesta.json();
        } catch (e) {
            mostrarMensaje('Error: Respuesta inválida del servidor');
            return;
        }
        
        if (!datos.ok) {
            mostrarMensaje(datos.error || 'Error al cargar productos');
            return;
        }
        
        // Obtener la tabla del DOM y limpiar su contenido para mostrar los nuevos productos
        const tabla = document.getElementById('tabla-productos');
        if (!tabla) {
            return;
        }
        
        tabla.innerHTML = '';
        
        // Si no hay productos, mostrar mensaje
        if (!datos.productos || datos.productos.length === 0) {
            const fila = document.createElement('tr');
            fila.innerHTML = '<td colspan="7" style="text-align: center; padding: 20px;">No hay productos registrados</td>';
            tabla.appendChild(fila);
            return;
        }
        
        // Renderizar cada producto en una fila de la tabla con sus datos e imágenes
        datos.productos.forEach(producto => {
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td>${producto.nombre_p || '-'}</td>
                <td>${producto.tamanio || '-'}</td>
                <td>${producto.stock ?? 0}</td>
                <td>$${producto.precio || '0.00'}</td>
                <td>${producto.descripcion_p || '-'}</td>
                <td>
                    <img src="${producto.imagen_url || '/calcomania-proyecto-final/Assets/Iconos/icono.png'}" 
                         alt="Producto" loading="lazy"
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" 
                         onerror="this.src='/calcomania-proyecto-final/Assets/Iconos/icono.png'">
                </td>
                <td class="acciones">
                    <button onclick="window.adminProductos.editar(${producto.id_producto})" 
                            style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">
                        Editar
                    </button>
                    <button onclick="window.adminProductos.eliminar(${producto.id_producto})" 
                            style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                        Eliminar
                    </button>
                </td>
            `;
            tabla.appendChild(fila);
        });
    } catch (error) {
        // Error de conexión o red al intentar cargar los productos desde el servidor
        mostrarMensaje('Error de conexión al cargar productos');
    }
}

