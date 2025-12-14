// Maneja el estado de edición de métodos de pago
let metodoEnEdicion = null;

export function estadoMetodoPago(id = null) {
    if (id === null) return metodoEnEdicion;
    metodoEnEdicion = id;
}

