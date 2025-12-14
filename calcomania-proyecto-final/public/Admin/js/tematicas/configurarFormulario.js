import { crearTematica } from './crear.js';
import { actualizarTematica } from './actualizar.js';
import { estadoTematica } from './estado.js';

let formularioConfigurado = false;

export function configurarFormulario() {
    if (formularioConfigurado) return;
    
    const formulario = document.getElementById('formulario-tematicas');
    if (!formulario) return;
    
    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (estadoTematica()) {
            await actualizarTematica();
        } else {
            await crearTematica();
        }
    });
    
    formularioConfigurado = true;
}

