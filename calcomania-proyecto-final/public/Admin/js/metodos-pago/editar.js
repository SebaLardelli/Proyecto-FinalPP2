import { aplicarFocusCampos } from '../shared/index.js';
import { estadoMetodoPago } from './estado.js';

export function editar(id, descripcion) {
    document.getElementById('descripcion-metodo-pago').value = descripcion;
    
    const boton = document.querySelector('#formulario-metodos-pago button[type="submit"]');
    boton.textContent = 'Actualizar Método';
    
    estadoMetodoPago(id);
    
    setTimeout(() => aplicarFocusCampos(), 100);
    document.getElementById('formulario-metodos-pago').scrollIntoView({ behavior: 'smooth' });
}

