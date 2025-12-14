// Recuperación de contraseña
import { validarEmail, configurarInputEmail } from './shared/index.js';
import { enviarCodigo } from './recuperacion/index.js';

const formulario = document.querySelector('#otp-form');
const inputEmail = document.querySelector('#email-input');
const boton = formulario?.querySelector('button');

// Configurar input de email
configurarInputEmail(inputEmail);

formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    
    const email = (inputEmail?.value || '').trim();
    
    if (!email) {
        inputEmail?.focus();
        return;
    }

    if (!validarEmail(email)) {
        alert('Ingresá un correo válido.');
        inputEmail?.focus();
        return;
    }

    try {
        if (boton) {
            boton.disabled = true;
            boton.dataset.textoOriginal = boton.textContent;
            boton.textContent = 'Generando y enviando...';
        }

        const resultado = await enviarCodigo(email);

        if (!resultado.ok) {
            alert('No pudimos generar el código: ' + resultado.error);
            return;
        }

        alert(resultado.mensaje + '. Revisa tu email (incluyendo spam).');
        window.location.href = '/calcomania-proyecto-final/Codigo';
        
    } catch (error) {
        alert('Error inesperado: ' + (error?.message || error));
    } finally {
        if (boton) {
            boton.disabled = false;
            if (boton.dataset.textoOriginal) {
                boton.textContent = boton.dataset.textoOriginal;
            }
        }
    }
});
