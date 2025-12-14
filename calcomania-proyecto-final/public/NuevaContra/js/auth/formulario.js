// Inicializa el formulario de cambio de contraseña
import { cambiarContrasena } from './cambiar.js';
import { validarFormularioCambioContrasena } from '../shared/index.js';

export function inicializarFormularioCambioContrasena(formulario, inputPassword1, inputPassword2) {
    if (!formulario || !inputPassword1 || !inputPassword2) return;

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const email = localStorage.getItem('otp_email') || '';
        if (!email) {
            alert('Falta el email. Volvé a verificar el código OTP.');
            return;
        }

        const password1 = (inputPassword1?.value || '').trim();
        const password2 = (inputPassword2?.value || '').trim();

        const errorValidacion = validarFormularioCambioContrasena(password1, password2);
        if (errorValidacion) {
            alert(errorValidacion);
            return;
        }

        const boton = formulario.querySelector('button[type="submit"]');
        const textoOriginal = boton?.textContent || '';
        
        try {
            if (boton) {
                boton.disabled = true;
                boton.textContent = 'Guardando...';
            }

            const resultado = await cambiarContrasena(email, password1);

            if (!resultado.ok) {
                alert('No se pudo actualizar la contraseña: ' + (resultado.error || 'Error desconocido'));
                return;
            }

            localStorage.removeItem('otp_email');
            alert('¡Contraseña actualizada con éxito! Ya podés iniciar sesión.');
            window.location.href = '/calcomania-proyecto-final/Login';

        } catch (error) {
            alert('Error inesperado: ' + (error?.message || error));
        } finally {
            if (boton) {
                boton.disabled = false;
                boton.textContent = textoOriginal;
            }
        }
    });
}

