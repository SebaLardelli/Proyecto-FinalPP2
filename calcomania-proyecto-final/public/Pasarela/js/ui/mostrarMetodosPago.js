import { estado } from '../shared/index.js';

// Renderiza métodos de pago disponibles
export function mostrarMetodosPago() {
    const container = document.getElementById('metodos-pago-container');
    if (!container) return;
    
    container.innerHTML = estado.metodosPago.map(metodo => `
        <div class="metodo-pago" data-metodo-id="${metodo.id_metodo_pago}">
            <input type="checkbox" id="metodo-${metodo.id_metodo_pago}" value="${metodo.id_metodo_pago}">
            <label for="metodo-${metodo.id_metodo_pago}">${metodo.descripcion_mp}</label>
        </div>
    `).join('');
}

