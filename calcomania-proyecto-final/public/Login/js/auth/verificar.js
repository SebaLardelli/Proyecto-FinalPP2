// Verifica si ya hay sesión activa y redirige
export async function verificarSesion() {
    try {
        const respuesta = await fetch('/calcomania-proyecto-final/api/auth/usuario', {
            method: 'GET',
            credentials: 'include'
        });
        
        if (respuesta.ok) {
            const datos = await respuesta.json();
            if (datos.ok && datos.usuario) {
                const destino = datos.usuario.id_rol === 1 
                    ? '/calcomania-proyecto-final/Admin' 
                    : '/calcomania-proyecto-final/Inicio';
                window.location.href = destino;
                return true;
            }
        }
        return false;
    } catch (error) {
        return false;
    }
}

