import { estadoCategoria } from './estado.js';

// Limpia formulario y resetea estado
export function limpiarFormulario() {
    document.getElementById('formulario-categorias').reset();
    
    const boton = document.querySelector('#formulario-categorias button[type="submit"]');
    boton.textContent = 'Crear Categoría';
    
    estadoCategoria(null);
    
    const campos = document.querySelectorAll('#formulario-categorias input');
    campos.forEach(campo => campo.classList.remove('has-content'));
}

