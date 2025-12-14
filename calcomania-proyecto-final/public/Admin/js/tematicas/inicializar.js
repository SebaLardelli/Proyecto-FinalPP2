import { cargarTematicas } from './cargar.js';
import { configurarFormulario } from './configurarFormulario.js';

export function inicializar() {
    cargarTematicas();
    configurarFormulario();
}

