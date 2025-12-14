// Inicialización de la pasarela de pago
import { API_BASE, estado, obtenerHeaders } from './shared/index.js';
import { cargarCarrito, cargarDatos, eliminarProducto, realizarPago } from './api/index.js';
import { mostrarResumen, mostrarMetodosPago, mostrarPuntosRetiro, actualizarBoton } from './ui/index.js';
import { toggleMetodo, configurarInputsPagoCombinado } from './pago/index.js';
import { seleccionarPunto } from './puntos/index.js';

// Exponer funciones para HTML
window.pasarela = {
    eliminarProducto,
    toggleMetodo,
    seleccionarPunto,
    configurarInputsPagoCombinado
};

// Inicializa la pasarela: verifica sesión, carga carrito, datos y configura eventos
async function inicializar() {
    try {
        // Verificar autenticación
        const response = await fetch(`${API_BASE}/auth/usuario`, {
            method: 'GET',
            credentials: 'include',
            headers: obtenerHeaders()
        });
        
        const userData = await response.json();
        
        if (!response.ok || !userData || !userData.ok) {
            alert('Debes iniciar sesión para continuar');
            window.location.href = '/calcomania-proyecto-final/Login';
            return;
        }
        
        // Cargar carrito
        await cargarCarrito();
        
        if (estado.carrito.length === 0) {
            alert('No hay productos en el carrito');
            window.location.href = '/calcomania-proyecto-final/Carrito';
            return;
        }
        
        // Mostrar resumen y cargar datos
        mostrarResumen();
        await cargarDatos();
        mostrarMetodosPago();
        mostrarPuntosRetiro();
        actualizarBoton();
        
        // Configurar eventos del formulario
        const form = document.getElementById('pasarela-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                realizarPago();
            });
        }
        
        // Botón volver
        const btnVolver = document.getElementById('volver-carrito');
        if (btnVolver) {
            btnVolver.addEventListener('click', () => {
                window.location.href = '/calcomania-proyecto-final/Carrito';
            });
        }
        
        // Eventos de métodos de pago (delegación de eventos)
        const containerMetodos = document.getElementById('metodos-pago-container');
        if (containerMetodos) {
            containerMetodos.addEventListener('click', (e) => {
                const metodoElement = e.target.closest('.metodo-pago');
                if (metodoElement) {
                    const idMetodo = parseInt(metodoElement.getAttribute('data-metodo-id'));
                    toggleMetodo(idMetodo);
                }
            });
        }
        
        // Eventos de puntos de retiro (delegación de eventos)
        const containerPuntos = document.getElementById('puntos-retiro-container');
        if (containerPuntos) {
            containerPuntos.addEventListener('click', (e) => {
                const puntoElement = e.target.closest('.punto-retiro');
                if (puntoElement) {
                    const idPunto = parseInt(puntoElement.getAttribute('data-punto-id'));
                    seleccionarPunto(idPunto);
                }
            });
        }
        
    } catch (error) {
        alert('Error al inicializar la pasarela de pago');
    }
}

document.addEventListener('DOMContentLoaded', inicializar);
