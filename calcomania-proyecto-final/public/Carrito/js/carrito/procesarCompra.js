// Procesa la compra y redirige a la pasarela de pago
export function procesarCompra(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
    }
    window.location.replace('/calcomania-proyecto-final/public/Pasarela');
    return false;
}

// Configura el botón de proceder al pago
export function configurarBotonPago() {
    const btn = document.getElementById('proceder-pago');
    if (btn) {
        // Remover cualquier listener anterior
        const nuevoBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(nuevoBtn, btn);
        
        // Agregar listener limpio
        nuevoBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            window.location.replace('/calcomania-proyecto-final/public/Pasarela');
            return false;
        }, true); // true = capture phase para ejecutarse primero
    }
}

