// Configuración y estado global
export const API_BASE = '/calcomania-proyecto-final/api';

// Estado global de la aplicación
export const estado = {
    // Datos
    productos: [],
    tematicasDisponibles: [],
    
    // Filtros activos
    categoriaActual: 'todos',
    tematicasSeleccionadas: [],
    
    // Sesión del usuario para acceso rápido en el frontend
    sesion: {
        autenticado: false,
        usuario: null
    }
};

