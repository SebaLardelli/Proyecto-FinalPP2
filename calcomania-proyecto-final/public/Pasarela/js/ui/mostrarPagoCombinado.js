import { estado } from '../shared/index.js';

// Muestra inputs para distribuir pago entre 2 métodos
export function mostrarPagoCombinado(callbackConfiguracion = null) {
    const container = document.getElementById('distribucion-pagos');
    container.innerHTML = '';
    
    if (estado.metodosSeleccionados.length === 2) {
        const metodo1 = estado.metodosPago.find(m => m.id_metodo_pago == estado.metodosSeleccionados[0]);
        const metodo2 = estado.metodosPago.find(m => m.id_metodo_pago == estado.metodosSeleccionados[1]);
        const monto1 = Math.max(1, Math.floor(estado.total / 2));
        const monto2 = Math.max(1, estado.total - monto1);
        
        container.innerHTML = `
            <h3>Distribución de Pagos</h3>
            <div class="distribucion-info">
                <p>Ingresa el monto para cada método de pago:</p>
                <p>Total a distribuir: <strong>$${estado.total.toFixed(2)}</strong></p>
            </div>
            <div class="pago-distribucion">
                <label>${metodo1.descripcion_mp}:</label>
                <input type="number" id="monto-${metodo1.id_metodo_pago}" value="${monto1}" min="0" max="${estado.total}" step="0.01">
            </div>
            <div class="pago-distribucion">
                <label>${metodo2.descripcion_mp}:</label>
                <input type="number" id="monto-${metodo2.id_metodo_pago}" value="${monto2}" min="0" max="${estado.total}" step="0.01">
            </div>
        `;
        
        if (callbackConfiguracion) {
            callbackConfiguracion();
        }
    }
}

