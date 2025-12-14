import { estadoPuntoVenta } from './estado.js';

export function limpiarFormulario() {
    document.getElementById('formulario-pos').reset();
    
    const boton = document.querySelector('#formulario-pos button[type="submit"]');
    boton.textContent = 'Crear Punto de Retiro';
    
    estadoPuntoVenta(null);
    
    const campos = document.querySelectorAll('#formulario-pos input');
    campos.forEach(campo => campo.classList.remove('has-content'));
}

