import { API_BASE } from '../shared/index.js';
import { filtrarPorCategoria } from './filtrarPorCategoria.js';

// Carga categorías y crea botones desde el endpoint de categorías (muestra todas, incluso sin productos)
export async function cargarCategorias() {
    try {
        const respuesta = await fetch(`${API_BASE}/categorias`);
        
        if (!respuesta.ok) {
            return;
        }
        
        const datos = await respuesta.json();
        
        if (!datos.ok || !datos.categorias) return;
        
        // Ordenar categorías por ID
        const categorias = datos.categorias.sort((a, b) => a.id_categoria - b.id_categoria);
        
        const botonesHTML = categorias.map(cat => `
            <li>
                <button id="categoria${cat.id_categoria}" class="boton-menu boton-categoria">
                    <i class="bi bi-hand-index-thumb"></i> ${cat.nombre_c}
                </button>
            </li>
        `).join('');
        
        const contenedor = document.querySelector('.categorias-container');
        if (contenedor) {
            contenedor.innerHTML = botonesHTML;
            
            document.querySelectorAll('.boton-categoria').forEach(boton => {
                boton.addEventListener('click', function() {
                    filtrarPorCategoria(this.id);
                });
            });
        }
    } catch (error) {
        // Error silencioso
    }
}

