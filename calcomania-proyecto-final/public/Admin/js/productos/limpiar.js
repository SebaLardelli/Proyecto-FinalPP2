import { estadoProducto } from './estado.js';

// Limpia el formulario y resetea estado
export function limpiarFormulario() {
    const formulario = document.getElementById('formulario-productos');
    formulario.reset();
    
    document.getElementById('id_categoria').value = '';
    document.getElementById('id_categoria').classList.remove('has-content');
    
    document.getElementById('id_tematica').value = '';
    document.getElementById('id_tematica').classList.remove('has-content');
    
    const campos = formulario.querySelectorAll('input, select');
    campos.forEach(campo => {
        campo.classList.remove('has-content');
        
        const label = campo.nextElementSibling;
        if (label && label.tagName === 'LABEL') {
            label.style.top = '';
            label.style.fontSize = '';
            label.style.color = '';
        }
    });
    
    const preview = document.getElementById('preview-imagen');
    if (preview) {
        preview.style.display = 'none';
        preview.src = '';
    }
    
    const mostrarRuta = document.getElementById('imagen-url-display');
    if (mostrarRuta) {
        mostrarRuta.remove();
    }
    
    const boton = document.querySelector('#formulario-productos button[type="submit"]');
    boton.textContent = 'Crear Producto';
    
    estadoProducto(null);
}

