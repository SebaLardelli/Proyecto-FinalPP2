// Panel de administración
// Verificación de autenticación (debe ejecutarse primero)
import { verificarAdmin } from './auth/verificar.js';
verificarAdmin();

import * as Productos from './productos/index.js';
import * as Categorias from './categorias/index.js';
import * as Tematicas from './tematicas/index.js';
import * as MetodosPago from './metodos-pago/index.js';
import * as PuntosVenta from './puntos-venta/index.js';
import * as Selects from './selects/index.js';
import { API_BASE, mostrarMensaje, toggleFocus, obtenerHeaders } from './shared/index.js';

// Exponer módulos para HTML
window.adminProductos = Productos;
window.adminCategorias = Categorias;
window.adminTematicas = Tematicas;
window.adminMetodosPago = MetodosPago;
window.adminPuntosVenta = PuntosVenta;
window.adminSelects = Selects;
window.toggleFocus = toggleFocus;

document.addEventListener('DOMContentLoaded', () => {
    // Logout
    const botonLogout = document.getElementById('logout-btn');
    if (botonLogout) {
        botonLogout.addEventListener('click', async () => {
            try {
                try {
                    localStorage.removeItem('jwt_token');
                } catch (error) {
                    // Error silencioso
                }
                await fetch(`${API_BASE}/auth/logout`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: obtenerHeaders()
                });
                window.location.href = '/calcomania-proyecto-final/Login';
            } catch (error) {
                mostrarMensaje('Error al cerrar sesión');
            }
        });
    }
    
    // Inicializar módulos
    Selects.inicializar();
    Productos.inicializar();
    Categorias.inicializar();
    Tematicas.inicializar();
    MetodosPago.inicializar();
    PuntosVenta.inicializar();
});
