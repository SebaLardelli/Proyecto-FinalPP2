import { API_BASE, obtenerHeaders } from '../shared/index.js';

const CLAVE_RECORDAR = 'remember_email';

// Cierra la sesión
export async function cerrar() {
    let emailRecordado = '';
    try {
        emailRecordado = localStorage.getItem(CLAVE_RECORDAR) || '';
    } catch (error) {
        emailRecordado = '';
    }
    
    try {
        // Eliminar token JWT
        try {
            localStorage.removeItem('jwt_token');
        } catch (error) {
            // Error silencioso
        }
        
        // Limpiar storage
        localStorage.clear();
        
        // Restaurar email recordado
        if (emailRecordado) {
            localStorage.setItem(CLAVE_RECORDAR, emailRecordado);
        }
        
        // Notificar al servidor (opcional con JWT)
        try {
            await fetch(`${API_BASE}/auth?action=logout`, {
                method: 'POST',
                headers: obtenerHeaders()
            });
        } catch (e) {
            // Ignorar errores de logout
        }
        
        window.location.replace('/calcomania-proyecto-final/public/Login');
        
    } catch (error) {
        try {
            localStorage.removeItem('jwt_token');
        } catch (error) {
            // Error silencioso
        }
        localStorage.clear();
        
        if (emailRecordado) {
            try {
                localStorage.setItem(CLAVE_RECORDAR, emailRecordado);
            } catch (error2) {
                // Ignorar
            }
        }
        window.location.replace('/calcomania-proyecto-final/public/Login');
    }
}

