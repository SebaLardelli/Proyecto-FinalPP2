// Actualiza totales y contador
export function actualizarTotales(datos) {
    const total = document.getElementById('total');
    if (total) {
        total.textContent = '$' + parseFloat(datos.total).toFixed(2);
    }
    
    const contador = document.getElementById('numerito');
    if (contador) {
        const totalItems = datos.items.reduce((suma, item) => suma + parseInt(item.cantidad), 0);
        contador.textContent = totalItems;
    }
}

