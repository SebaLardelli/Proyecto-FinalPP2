// Maneja el estado de edición de productos
let productoEnEdicion = null;

export function estadoProducto(id = null) {
    if (id === null) return productoEnEdicion;
    productoEnEdicion = id;
}

