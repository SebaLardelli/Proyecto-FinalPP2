import { API_URL } from '../shared/index.js';

// Envía código OTP al email del usuario
export async function enviarCodigo(email) {
    try {
        const respuesta = await fetch(API_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ email })
        });

        const tipoContenido = respuesta.headers.get('content-type') || '';
        let datos = {};
        
        if (tipoContenido.includes('application/json')) {
            try {
                datos = await respuesta.json();
            } catch (error) {
                // Error silencioso
            }
        }

        if (!respuesta.ok || !datos.ok) {
            const mensaje = datos.error || datos.msg || `Error HTTP ${respuesta.status}`;
            return { ok: false, error: mensaje };
        }

        // Guardar email en localStorage para usarlo en la siguiente pantalla
        localStorage.setItem('otp_email', email);
        
        return { 
            ok: true, 
            mensaje: datos.mensaje || 'Código enviado correctamente'
        };
        
    } catch (error) {
        return { 
            ok: false, 
            error: 'Error de red: ' + (error?.message || error) 
        };
    }
}

