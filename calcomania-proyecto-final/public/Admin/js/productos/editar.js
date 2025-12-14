import { API_BASE, mostrarMensaje, obtenerHeaders, acortarURL, aplicarFocusCampos } from '../shared/index.js';
import { estadoProducto } from './estado.js';

// Carga datos del producto en el formulario para editar
export async function editar(id) {
    try {
        // Usar la ruta de admin para obtener todos los productos (incluye los de stock 0)
        const respuesta = await fetch(`${API_BASE}/productos/admin`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        const datos = await respuesta.json();
        
        if (!datos.ok || !datos.productos) return;
        
        const producto = datos.productos.find(p => p.id_producto == id);
        if (!producto) return;
        
        document.getElementById('id_categoria').value = producto.id_categoria;
        document.getElementById('id_tematica').value = producto.id_tematica || '';
        document.getElementById('nombre_p').value = producto.nombre_p;
        document.getElementById('tamanio').value = producto.tamanio || '';
        document.getElementById('precio').value = producto.precio;
        document.getElementById('stock').value = producto.stock;
        document.getElementById('descripcion_p').value = producto.descripcion_p || '';
        
        if (producto.imagen_url) {
            const preview = document.getElementById('preview-imagen');
            preview.src = producto.imagen_url;
            preview.style.display = 'block';
            
            const input = document.getElementById('imagen_producto');
            input.classList.add('has-content');
            
            let mostrarRuta = document.getElementById('imagen-url-display');
            if (!mostrarRuta) {
                mostrarRuta = document.createElement('div');
                mostrarRuta.id = 'imagen-url-display';
                mostrarRuta.style.cssText = `
                    position: absolute; top: 0; left: 0; width: 100%; height: 56px;
                    background: transparent; border: none; font-size: 1.1rem; color: #fff;
                    padding: 0 45px 0 40px; display: flex; align-items: center;
                    pointer-events: none; z-index: 2;
                `;
                
                const contenedor = input.parentElement;
                contenedor.style.position = 'relative';
                contenedor.appendChild(mostrarRuta);
            }
            
            mostrarRuta.textContent = acortarURL(producto.imagen_url);
            
            const label = input.nextElementSibling;
            label.style.top = '-10px';
            label.style.fontSize = '0.95rem';
            label.style.color = '#ffffff';
        }
        
        const boton = document.querySelector('#formulario-productos button[type="submit"]');
        boton.textContent = 'Actualizar Producto';
        estadoProducto(id);
        
        setTimeout(() => aplicarFocusCampos(), 100);
        document.getElementById('formulario-productos').scrollIntoView({ behavior: 'smooth' });
    } catch (error) {
        mostrarMensaje('Error al cargar el producto');
    }
}

