import { estado } from '../shared/index.js';
import { validarMonto } from '../shared/index.js';

// Configura eventos de inputs de pago combinado
export function configurarInputsPagoCombinado() {
    if (estado.metodosSeleccionados.length === 2) {
        const metodo1 = estado.metodosPago.find(m => m.id_metodo_pago == estado.metodosSeleccionados[0]);
        const metodo2 = estado.metodosPago.find(m => m.id_metodo_pago == estado.metodosSeleccionados[1]);
        
        const input1 = document.getElementById(`monto-${metodo1.id_metodo_pago}`);
        const input2 = document.getElementById(`monto-${metodo2.id_metodo_pago}`);
        
        if (input1 && input2) {
            input1.addEventListener('input', () => {
                validarMonto(input1, input2, metodo1.descripcion_mp, metodo2.descripcion_mp);
            });
            input2.addEventListener('input', () => {
                validarMonto(input2, input1, metodo2.descripcion_mp, metodo1.descripcion_mp);
            });
        }
    }
}

