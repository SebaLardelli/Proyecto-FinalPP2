// Verificación de autenticación para panel de administración
export function verificarAdmin() {
    const token = localStorage.getItem('jwt_token');
    
    if (!token) {
        alert('No estás autenticado. Por favor, inicia sesión.');
        window.location.href = '/calcomania-proyecto-final/Login';
        return;
    }
    
    try {
        const parts = token.split('.');
        if (parts.length !== 3) {
            throw new Error('Token inválido: formato incorrecto');
        }
        
        const payloadBase64 = parts[1];
        const padding = payloadBase64.length % 4;
        const paddedBase64 = padding ? payloadBase64 + '='.repeat(4 - padding) : payloadBase64;
        
        const payloadJson = atob(paddedBase64);
        const payload = JSON.parse(payloadJson);
        
        if (!payload.exp) {
            throw new Error('Token inválido: no tiene fecha de expiración');
        }
        
        const ahora = Math.floor(Date.now() / 1000);
        const tiempoRestante = payload.exp - ahora;
        
        if (tiempoRestante < -60) {
            alert('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
            localStorage.removeItem('jwt_token');
            window.location.href = '/calcomania-proyecto-final/Login';
            return;
        }
        
        const data = payload.data || payload;
        const idRol = data.id_rol || data.rol;
        
        if (idRol !== 1) {
            alert('No tienes permisos de administrador');
            window.location.href = '/calcomania-proyecto-final/Inicio';
            return;
        }
    } catch (error) {
        alert('Error verificando autenticación: ' + error.message + '\n\nPor favor, inicia sesión nuevamente.');
        localStorage.removeItem('jwt_token');
        window.location.href = '/calcomania-proyecto-final/Login';
    }
}

