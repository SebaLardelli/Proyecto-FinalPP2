import { cargarMetodosPago } from './cargar.js';
import { configurarFormulario } from './configurarFormulario.js';

export function inicializar() {
    cargarMetodosPago();
    configurarFormulario();
}

