import { aplicarFocusCampos } from '../shared/index.js';
import { estadoCategoria } from './estado.js';

// Carga datos en el formulario para editar
export function editar(id, nombre, descripcion) {
    document.getElementById('nombre-categoria').value = nombre;
    document.getElementById('descripcion-categoria').value = descripcion;
    
    const formulario = document.getElementById('formulario-categorias');
    const boton = formulario.querySelector('button[type="submit"]');
    boton.textContent = 'Actualizar Categoría';
    
    estadoCategoria(id);
    
    setTimeout(() => aplicarFocusCampos(), 100);
    formulario.scrollIntoView({ behavior: 'smooth' });
}

