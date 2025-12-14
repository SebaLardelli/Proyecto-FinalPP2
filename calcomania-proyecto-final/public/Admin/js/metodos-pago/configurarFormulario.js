import { crearMetodoPago } from './crear.js';
import { actualizarMetodoPago } from './actualizar.js';
import { estadoMetodoPago } from './estado.js';

let formularioConfigurado = false;

export function configurarFormulario() {
    if (formularioConfigurado) return;
    
    const formulario = document.getElementById('formulario-metodos-pago');
    if (!formulario) return;
    
    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (estadoMetodoPago()) {
            await actualizarMetodoPago();
        } else {
            await crearMetodoPago();
        }
    });
    
    formularioConfigurado = true;
}

