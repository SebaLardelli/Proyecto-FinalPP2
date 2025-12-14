// Inicialización tienda
import * as Auth from './auth/index.js';
import * as Productos from './productos/index.js';
import * as Tematicas from './tematicas/index.js';
import * as Carrito from './carrito/index.js';

// Exponer para HTML
window.inicioAuth = Auth;
window.inicioProductos = Productos;
window.inicioTematicas = Tematicas;
window.inicioCarrito = Carrito;

document.addEventListener('DOMContentLoaded', function() {
    configurarEventos();
    Auth.verificarSesion();
    Productos.cargarProductos();
    Productos.cargarCategorias();
    Tematicas.cargarTematicas();
});

function configurarEventos() {
    // Botón "Todos los productos"
    const botonTodos = document.getElementById('todos');
    if (botonTodos) {
        botonTodos.addEventListener('click', function() {
            Productos.filtrarPorCategoria('todos');
        });
    }
    
    // Categorías dinámicas (se configuran en productos.js después de cargarlas)
    document.querySelectorAll('.boton-categoria').forEach(boton => {
        boton.addEventListener('click', function() {
            Productos.filtrarPorCategoria(this.id);
        });
    });
    
    // Menú móvil
    const abrirMenu = document.getElementById('abrir-menu');
    if (abrirMenu) {
        abrirMenu.addEventListener('click', () => {
            document.querySelector('aside').classList.add('open');
        });
    }
    
    const cerrarMenu = document.getElementById('close-menu');
    if (cerrarMenu) {
        cerrarMenu.addEventListener('click', () => {
            document.querySelector('aside').classList.remove('open');
        });
    }
    
    // Temáticas
    const selectorTematica = document.getElementById('selector-tematica');
    if (selectorTematica) {
        selectorTematica.addEventListener('change', Tematicas.manejarSeleccion);
    }
}
