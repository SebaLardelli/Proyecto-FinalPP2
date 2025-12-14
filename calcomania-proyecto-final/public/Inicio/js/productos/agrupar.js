// Agrupa productos por nombre para manejar variantes de tamaño
export function agruparProductos(productos) {
    const grupos = {};
    
    productos.forEach(producto => {
        const nombre = producto.nombre_p;
        
        if (!grupos[nombre]) {
            grupos[nombre] = {
                nombre: producto.nombre_p,
                categoria: (producto.nombre_categoria || 'otros').toLowerCase(),
                descripcion: producto.descripcion_p || '',
                variantes: []
            };
        }
        
        grupos[nombre].variantes.push({
            id: producto.id_producto,
            tamano: producto.tamanio || '',
            precio: producto.precio,
            imagen: producto.imagen_url || '/calcomania-proyecto-final/public/Assets/Iconos/icono.png',
            stock: producto.stock
        });
    });
    
    return Object.values(grupos);
}

