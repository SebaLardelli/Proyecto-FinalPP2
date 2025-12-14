import { estadoMetodoPago } from './estado.js';

export function limpiarFormulario() {
    document.getElementById('formulario-metodos-pago').reset();
    
    const boton = document.querySelector('#formulario-metodos-pago button[type="submit"]');
    boton.textContent = 'Crear Método de Pago';
    
    estadoMetodoPago(null);
    
    const campos = document.querySelectorAll('#formulario-metodos-pago input');
    campos.forEach(campo => campo.classList.remove('has-content'));
}

