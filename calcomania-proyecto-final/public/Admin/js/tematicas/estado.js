// Maneja el estado de edición de temáticas
let tematicaEnEdicion = null;

export function estadoTematica(id = null) {
    if (id === null) return tematicaEnEdicion;
    tematicaEnEdicion = id;
}

