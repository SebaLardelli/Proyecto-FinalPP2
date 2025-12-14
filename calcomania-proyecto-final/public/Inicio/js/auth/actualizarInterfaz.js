import { estado } from '../shared/index.js';

// Actualiza interfaz según estado de sesión
export function actualizarInterfaz(estaLogueado) {
    const enlaceLogin = document.getElementById('login-link');
    const enlaceCarrito = document.getElementById('carrito-link');
    
    if (!enlaceLogin) return;

    if (estaLogueado && estado.sesion.usuario) {
        // Mostrar nombre y apellido, o solo nombre, o email como fallback
        const nombre = estado.sesion.usuario.nombre || '';
        const apellido = estado.sesion.usuario.apellido || '';
        let nombreCompleto = '';
        if (nombre && apellido) {
            nombreCompleto = `${nombre} ${apellido}`;
        } else if (nombre) {
            nombreCompleto = nombre;
        } else {
            nombreCompleto = estado.sesion.usuario.email || 'Usuario';
        }
        enlaceLogin.innerHTML = `
            ${nombreCompleto} 
            <button onclick="window.inicioAuth.cerrar()" class="btn-logout">
                Cerrar sesión
            </button>
        `;
        enlaceLogin.classList.add('usuario-autenticado');
        enlaceLogin.href = '#';
        
        if (enlaceCarrito) {
            enlaceCarrito.href = '/calcomania-proyecto-final/public/Carrito';
        }
    } else {
        enlaceLogin.innerHTML = 'Iniciar sesión';
        enlaceLogin.classList.remove('usuario-autenticado');
        enlaceLogin.href = '/calcomania-proyecto-final/public/Login';
        
        if (enlaceCarrito) {
            enlaceCarrito.href = '/calcomania-proyecto-final/public/Login';
        }
    }
}

