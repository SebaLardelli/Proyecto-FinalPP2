// Utilidades para manejar JWT
const JWT_STORAGE_KEY = 'jwt_token';

// Obtiene headers con Authorization si hay token
export function obtenerHeaders() {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
    
    try {
        const token = localStorage.getItem(JWT_STORAGE_KEY);
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
    } catch (error) {
        // Error silencioso
    }
    
    return headers;
}

