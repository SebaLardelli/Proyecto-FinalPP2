import { obtenerCodigo, limpiarCodigo } from '../shared/index.js';

const API_URL = '/calcomania-proyecto-final/api/auth/verificar-codigo';
const URL_SIGUIENTE = '/calcomania-proyecto-final/NuevaContra';

// Verifica el código OTP
export function inicializarVerificacion(formulario, inputs, boton) {
    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const email = localStorage.getItem('otp_email') || '';
        if (!email) {
            alert('No encontramos tu email. Volvé a "Recuperar Contraseña" e ingresalo de nuevo.');
            location.assign('/calcomania-proyecto-final/Recuperacion');
            return;
        }

        const codigo = obtenerCodigo(inputs);
        if (!/^\d{6}$/.test(codigo)) {
            alert('Ingresá los 6 dígitos del código.');
            return;
        }

        const textoOriginal = boton?.textContent || '';
        
        try {
            if (boton) {
                boton.disabled = true;
                boton.textContent = 'Verificando...';
            }

            const respuesta = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, codigo })
            });

            const tipoContenido = respuesta.headers.get('content-type') || '';
            if (!tipoContenido.includes('application/json')) {
                alert('Respuesta inválida del servidor.');
                return;
            }
            
            const datos = await respuesta.json();

            if (datos.ok) {
                location.assign(URL_SIGUIENTE);
                return;
            }

            const mensaje = datos.error || 'No pudimos validar el código.';
            alert(mensaje);

            if (datos.error && datos.error.includes('incorrecto')) {
                limpiarCodigo(inputs);
            }

        } catch (error) {
            alert('Error de red: ' + (error?.message || error));
        } finally {
            if (boton) {
                boton.disabled = false;
                boton.textContent = textoOriginal;
            }
        }
    });
}

