import { estadoTematica } from './estado.js';

export function limpiarFormulario() {
    document.getElementById('formulario-tematicas').reset();
    
    const boton = document.querySelector('#formulario-tematicas button[type="submit"]');
    boton.textContent = 'Crear Temática';
    
    estadoTematica(null);
    
    const campos = document.querySelectorAll('#formulario-tematicas input');
    campos.forEach(campo => campo.classList.remove('has-content'));
}

