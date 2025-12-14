// Muestra un mensaje temporal en pantalla
export function mostrarMensaje(texto, esExito = false) {
    const mensaje = document.createElement('div');
    mensaje.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${esExito ? '#d4edda' : '#f8d7da'};
        color: ${esExito ? '#155724' : '#721c24'};
        border: 1px solid ${esExito ? '#c3e6cb' : '#f5c6cb'};
        padding: 12px 16px;
        border-radius: 4px;
        z-index: 9999;
    `;
    mensaje.textContent = texto;
    document.body.appendChild(mensaje);
    
    setTimeout(() => mensaje.remove(), 4000);
}

