// Maneja el estado de edición de puntos de retiro
let puntoEnEdicion = null;

export function estadoPuntoVenta(id = null) {
    if (id === null) return puntoEnEdicion;
    puntoEnEdicion = id;
}

