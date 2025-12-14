import { cargarCategorias } from './cargar.js';
import { configurarFormulario } from './configurarFormulario.js';

// Inicializa el módulo de categorías
export function inicializar() {
    cargarCategorias();
    configurarFormulario();
}

