// Módulo de temáticas - Exporta todas las funciones
export { cargarTematicas } from './cargar.js';
export { manejarSeleccion } from './manejarSeleccion.js';
export { remover } from './remover.js';

// Alias para compatibilidad
import { cargarTematicas as cargarTematicasFunc } from './cargar.js';
export function cargar() {
    cargarTematicasFunc();
}

