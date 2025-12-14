import { estado } from '../shared/index.js';

// Renderiza puntos de retiro disponibles
export function mostrarPuntosRetiro() {
    const container = document.getElementById('puntos-retiro-container');
    if (!container) return;
    
    container.innerHTML = estado.puntosRetiro.map(punto => `
        <div class="punto-retiro" data-punto-id="${punto.id_punto_retiro}">
            <input type="radio" id="punto-${punto.id_punto_retiro}" name="punto-retiro" value="${punto.id_punto_retiro}">
            <label for="punto-${punto.id_punto_retiro}">
                <div class="punto-retiro-nombre">${punto.nombre_punto}</div>
                <div class="punto-retiro-direccion">${punto.direccion}</div>
                <div class="punto-retiro-horarios">Horarios: ${punto.horarios}</div>
            </label>
        </div>
    `).join('');
}

