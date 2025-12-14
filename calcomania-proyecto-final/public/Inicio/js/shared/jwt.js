// Obtiene headers con Authorization si hay token
export function obtenerHeaders() {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
    
    try {
        const token = localStorage.getItem('jwt_token');
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
    } catch (error) {
        // Error silencioso - no mostrar en consola
    }
    
    return headers;
}

