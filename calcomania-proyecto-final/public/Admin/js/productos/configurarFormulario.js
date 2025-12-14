import { crearProducto } from './crear.js';
import { actualizarProducto } from './actualizar.js';
import { mostrarVistaPrevia } from './mostrarVistaPrevia.js';
import { estadoProducto } from './estado.js';

let formularioConfigurado = false;

// Configura el formulario de productos
export function configurarFormulario() {
    if (formularioConfigurado) return;
    
    const formulario = document.getElementById('formulario-productos');
    if (!formulario) return;
    
    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (estadoProducto()) {
            await actualizarProducto();
        } else {
            await crearProducto();
        }
    });
    
    const inputImagen = document.getElementById('imagen_producto');
    if (inputImagen) {
        inputImagen.addEventListener('change', function() {
            mostrarVistaPrevia(this);
        });
    }
    
    formularioConfigurado = true;
}

