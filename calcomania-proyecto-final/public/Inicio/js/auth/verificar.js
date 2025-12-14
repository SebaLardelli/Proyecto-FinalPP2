import { API_BASE, estado, obtenerHeaders } from '../shared/index.js';
import { actualizarInterfaz } from './actualizarInterfaz.js';
import { actualizarContador } from './actualizarContador.js';

// Verifica si hay sesión activa
export async function verificarSesion() {
    // Verificar si el token está expirado
    try {
        const token = localStorage.getItem('jwt_token');
        if (token) {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const exp = payload.exp;
            if (exp && Date.now() >= exp * 1000) {
                localStorage.removeItem('jwt_token');
                estado.sesion = { autenticado: false, usuario: null };
                actualizarInterfaz(false);
                await actualizarContador();
                return;
            }
        }
    } catch (error) {
        try {
            localStorage.removeItem('jwt_token');
        } catch (e) {}
        estado.sesion = { autenticado: false, usuario: null };
        actualizarInterfaz(false);
        await actualizarContador();
        return;
    }
    
    try {
        const respuesta = await fetch(`${API_BASE}/auth/verificar-sesion`, {
            headers: obtenerHeaders(),
            credentials: 'include'
        });

        if (respuesta.ok) {
            const datos = await respuesta.json();
            estado.sesion = {
                autenticado: true,
                usuario: datos.usuario
            };
            actualizarInterfaz(true);
            await actualizarContador();
        } else {
            // Token inválido, eliminar
            try {
                localStorage.removeItem('jwt_token');
            } catch (error) {
                // Error silencioso
            }
            estado.sesion = { autenticado: false, usuario: null };
            actualizarInterfaz(false);
            await actualizarContador();
        }
    } catch (error) {
        try {
            localStorage.removeItem('jwt_token');
        } catch (e) {
            // Error silencioso
        }
        estado.sesion = { autenticado: false, usuario: null };
        actualizarInterfaz(false);
    }
}

