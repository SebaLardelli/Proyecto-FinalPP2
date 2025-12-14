import { cargarProductos } from './cargar.js';
import { configurarFormulario } from './configurarFormulario.js';
import { configurarCampos } from './configurarCampos.js';

// Inicializa el módulo de productos
export function inicializar() {
    cargarProductos();
    configurarFormulario();
    configurarCampos();
}

