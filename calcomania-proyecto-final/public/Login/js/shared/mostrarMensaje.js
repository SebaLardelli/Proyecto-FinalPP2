import { $ } from './getElement.js';

// Muestra mensaje debajo del botón
export function mostrarMensaje(texto, esExito = false) {
    let mensaje = $('mensaje-login');
    
    if (!mensaje) {
        mensaje = document.createElement('div');
        mensaje.id = 'mensaje-login';
        mensaje.style.marginTop = '10px';
        mensaje.style.textAlign = 'center';
        mensaje.style.padding = '10px';
        mensaje.style.borderRadius = '5px';
        $('login-button').parentElement.appendChild(mensaje);
    }
    
    mensaje.style.backgroundColor = esExito ? '#d4edda' : '#f8d7da';
    mensaje.style.color = esExito ? '#155724' : '#721c24';
    mensaje.style.border = '1px solid ' + (esExito ? '#c3e6cb' : '#f5c6cb');
    mensaje.textContent = texto;
}

