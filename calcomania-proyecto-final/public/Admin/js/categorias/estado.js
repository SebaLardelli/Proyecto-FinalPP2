// Maneja el estado de edición de categorías
let categoriaEnEdicion = null;

export function estadoCategoria(id = null) {
    if (id === null) return categoriaEnEdicion;
    categoriaEnEdicion = id;
}

