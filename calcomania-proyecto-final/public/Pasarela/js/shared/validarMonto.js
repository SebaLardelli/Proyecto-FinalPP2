import { estado } from './config.js';
import { actualizarBoton } from '../ui/index.js';

// Valida y ajusta montos de pago combinado
export function validarMonto(inputActual, inputOtro, nombreActual, nombreOtro) {
    let montoActual = parseFloat(inputActual.value) || 0;
    
    if (montoActual < 0) {
        alert('El monto para ' + nombreActual + ' no puede ser negativo');
        inputActual.value = 1;
        montoActual = 1;
    }
    
    if (montoActual === 0) {
        alert('En pago combinado, el monto para ' + nombreActual + ' debe ser mayor a $0');
        inputActual.value = 1;
        montoActual = 1;
    }
    
    if (montoActual > estado.total) {
        alert('El monto para ' + nombreActual + ' no puede ser mayor al total');
        inputActual.value = estado.total.toFixed(2);
        montoActual = estado.total;
    }
    
    const montoOtro = estado.total - montoActual;
    
    if (montoOtro < 0) {
        alert('El monto para ' + nombreOtro + ' no puede ser negativo');
        inputActual.value = (estado.total - 1).toFixed(2);
        inputOtro.value = 1;
        return;
    }
    
    inputOtro.value = montoOtro.toFixed(2);
    actualizarBoton();
}

