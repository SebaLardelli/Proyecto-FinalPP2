import { crearPuntoVenta } from './crear.js';
import { actualizarPuntoVenta } from './actualizar.js';
import { estadoPuntoVenta } from './estado.js';

let formularioConfigurado = false;

export function configurarFormulario() {
    if (formularioConfigurado) return;
    
    const formulario = document.getElementById('formulario-pos');
    if (!formulario) return;
    
    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (estadoPuntoVenta()) {
            await actualizarPuntoVenta();
        } else {
            await crearPuntoVenta();
        }
    });
    
    formularioConfigurado = true;
}

