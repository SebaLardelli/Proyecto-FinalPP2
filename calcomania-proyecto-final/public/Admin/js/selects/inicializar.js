import { cargarCategorias } from './cargarCategorias.js';
import { cargarTematicas } from './cargarTematicas.js';

export function inicializar() {
    cargarCategorias();
    cargarTematicas();
}

