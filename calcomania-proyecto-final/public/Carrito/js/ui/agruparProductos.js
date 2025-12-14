// Agrupa productos por ID y tamaño
export function agruparProductos(items) {
    const grupos = {};
    
    items.forEach(item => {
        let tamano = null;
        if (item.tamano !== undefined && item.tamano !== null) {
            tamano = String(item.tamano).trim();
            if (tamano === '' || tamano.toLowerCase() === 'null') {
                tamano = null;
            }
        } else if (item.tamanio_producto !== undefined && item.tamanio_producto !== null && !item.tamano) {
            tamano = String(item.tamanio_producto).trim();
            if (tamano === '' || tamano.toLowerCase() === 'null') {
                tamano = null;
            }
        }
        
        let categoria = '';
        if (item.nombre_c) {
            categoria = String(item.nombre_c).trim();
        } else if (item.categoria) {
            categoria = String(item.categoria).trim();
        }
        
        const esPack = ['pack', 'packs'].includes(categoria.toLowerCase());
        const clave = item.id_producto + '_' + (tamano || 'sin_tamano');
        
        if (grupos[clave]) {
            grupos[clave].cantidad += parseInt(item.cantidad);
            grupos[clave].subtotal += parseFloat(item.importe_total_detalle);
            // Agregar el id_fila a la lista de filas para este grupo
            if (!grupos[clave].id_filas) {
                grupos[clave].id_filas = [];
            }
            grupos[clave].id_filas.push(item.id_fila);
        } else {
            grupos[clave] = {
                id_producto: item.id_producto,
                nombre_p: item.nombre_p,
                descripcion_p: item.descripcion_p || '',
                precio_unitario: item.precio_unitario,
                cantidad: parseInt(item.cantidad),
                subtotal: parseFloat(item.importe_total_detalle),
                tamano: tamano,
                tamanio_producto: item.tamanio_producto || null,
                imagen_url: item.imagen_url || '/calcomania-proyecto-final/Assets/Iconos/icono.png',
                esPack: esPack,
                categoria: categoria,
                id_filas: [item.id_fila] // Guardar el id_fila para poder eliminar la fila específica
            };
        }
    });
    
    return grupos;
}

