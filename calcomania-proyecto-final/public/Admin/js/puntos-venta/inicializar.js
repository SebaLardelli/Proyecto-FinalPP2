import { cargarPuntosVenta } from './cargar.js';
import { configurarFormulario } from './configurarFormulario.js';

export function inicializar() {
    cargarPuntosVenta();
    configurarFormulario();
}

