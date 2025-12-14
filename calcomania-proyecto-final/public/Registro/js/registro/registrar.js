import { API_URL } from '../shared/index.js';

// Registra un nuevo usuario
export async function registrar(datos) {
    try {
        const respuesta = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        const resultado = await respuesta.json();

        if (resultado.ok) {
            return { 
                ok: true, 
                mensaje: resultado.mensaje || 'Usuario registrado correctamente' 
            };
        } else {
            return { 
                ok: false, 
                error: resultado.error || 'Error en el registro' 
            };
        }
    } catch (error) {
        return { 
            ok: false, 
            error: 'Error de conexión: ' + (error?.message || error) 
        };
    }
}

