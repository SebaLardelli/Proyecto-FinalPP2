// Cambia la contraseña del usuario
export async function cambiarContrasena(email, password) {
    const API_URL = '/calcomania-proyecto-final/api/auth/cambiar-password';
    
    try {
        const respuesta = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const tipoContenido = respuesta.headers.get('content-type') || '';
        let datos = {};
        
        if (tipoContenido.includes('application/json')) {
            try {
                datos = await respuesta.json();
            } catch (error) {
                return { ok: false, error: 'Error al procesar la respuesta del servidor.' };
            }
        } else {
            return { ok: false, error: 'Respuesta inválida del servidor.' };
        }

        if (!respuesta.ok || !datos.ok) {
            return { ok: false, error: datos.error || datos.msg || `Error HTTP ${respuesta.status}` };
        }

        return { ok: true, ...datos };
    } catch (error) {
        return { ok: false, error: 'Error de red: ' + (error?.message || error) };
    }
}

