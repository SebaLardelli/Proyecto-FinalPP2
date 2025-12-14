import { aplicarFocusCampos } from '../shared/index.js';
import { estadoTematica } from './estado.js';

export function editar(id, nombre, descripcion) {
    document.getElementById('nombre-tematica').value = nombre;
    document.getElementById('descripcion-tematica').value = descripcion;
    
    const boton = document.querySelector('#formulario-tematicas button[type="submit"]');
    boton.textContent = 'Actualizar Temática';
    
    estadoTematica(id);
    
    setTimeout(() => aplicarFocusCampos(), 100);
    document.getElementById('formulario-tematicas').scrollIntoView({ behavior: 'smooth' });
}

