// Registro de usuarios
import { validarContrasenas, obtenerDatosFormulario, toggleFocus, togglePasswordVisibility, showPasswordToggle } from './shared/index.js';
import { registrar } from './registro/index.js';

// Exponer funciones para uso en HTML
window.toggleFocus = toggleFocus;
window.togglePasswordVisibility = togglePasswordVisibility;
window.showPasswordToggle = showPasswordToggle;

let enviando = false;

document.querySelector('form').addEventListener('submit', async (evento) => {
    evento.preventDefault();
    
    // Prevenir envíos duplicados
    if (enviando) return;

    const datos = obtenerDatosFormulario();

    const errorValidacion = validarContrasenas(datos.password, datos.confirmar_password);
    if (errorValidacion) {
        alert(errorValidacion);
        return;
    }

    const boton = evento.target.querySelector('button[type="submit"]');
    const textoOriginal = boton.textContent;
    boton.disabled = true;
    boton.textContent = 'Registrando...';
    enviando = true;

    try {
        const resultado = await registrar(datos);

        if (resultado.ok) {
            alert(resultado.mensaje);
            window.location.href = '/calcomania-proyecto-final/Login';
        } else {
            alert(resultado.error);
            boton.disabled = false;
            boton.textContent = textoOriginal;
            enviando = false;
        }
    } catch (error) {
        alert('Error inesperado: ' + (error?.message || error));
        boton.disabled = false;
        boton.textContent = textoOriginal;
        enviando = false;
    }
});
