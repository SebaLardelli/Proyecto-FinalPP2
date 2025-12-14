import { crearCategoria } from './crear.js';
import { actualizarCategoria } from './actualizar.js';
import { estadoCategoria } from './estado.js';

let formularioConfigurado = false;

// Configura el formulario de categorías
export function configurarFormulario() {
    if (formularioConfigurado) return;
    
    const formulario = document.getElementById('formulario-categorias');
    if (!formulario) return;
    
    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (estadoCategoria()) {
            await actualizarCategoria();
        } else {
            await crearCategoria();
        }
    });
    
    formularioConfigurado = true;
}

