import { estado } from '../shared/index.js';
import { aplicarFiltros } from './aplicarFiltros.js';

// Filtra productos por categoría
export function filtrarPorCategoria(categoria) {
    estado.categoriaActual = categoria;
    
    document.querySelectorAll('.boton-categoria').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(categoria).classList.add('active');
    
    estado.tematicasSeleccionadas = [];
    
    if (window.inicioTematicas && window.inicioTematicas.cargar) {
        window.inicioTematicas.cargar();
    }
    
    const titulo = document.getElementById('titulo-principal');
    if (categoria === 'todos') {
        titulo.textContent = 'Todos los productos';
    } else {
        const boton = document.getElementById(categoria);
        if (boton) {
            const textoCompleto = boton.textContent.trim();
            const nombreCategoria = textoCompleto.split(/\s+/).slice(1).join(' ');
            titulo.textContent = nombreCategoria || textoCompleto;
        }
    }
    
    aplicarFiltros();
}

