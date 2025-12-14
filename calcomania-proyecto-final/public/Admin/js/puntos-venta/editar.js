import { aplicarFocusCampos } from '../shared/index.js';
import { estadoPuntoVenta } from './estado.js';

export function editar(id, nombre, direccion, horarios = '', codigoPostal = '') {
    document.getElementById('nombre-pos').value = nombre;
    document.getElementById('direccion-pos').value = direccion;
    document.getElementById('horarios-pos').value = horarios || '';
    document.getElementById('codigo-postal-pos').value = codigoPostal || '';
    
    const boton = document.querySelector('#formulario-pos button[type="submit"]');
    boton.textContent = 'Actualizar Punto';
    
    estadoPuntoVenta(id);
    
    setTimeout(() => aplicarFocusCampos(), 100);
    document.getElementById('formulario-pos').scrollIntoView({ behavior: 'smooth' });
}

