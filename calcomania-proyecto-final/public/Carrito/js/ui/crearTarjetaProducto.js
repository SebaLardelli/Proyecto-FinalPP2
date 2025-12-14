// Crea la tarjeta HTML de un producto
export function crearTarjetaProducto(grupo) {
    const tamano = grupo.tamano || grupo.tamanio_producto;
    const esPack = esProductoPack(grupo.categoria);
    
    // Usar el primer id_fila del grupo (si hay múltiples, se eliminará el primero)
    // Si hay múltiples filas agrupadas, se mostrarán como una sola tarjeta pero cada una tiene su id_fila
    const idFila = grupo.id_filas && grupo.id_filas.length > 0 ? grupo.id_filas[0] : null;
    
    const infoExtra = construirInfoExtra(esPack, grupo.descripcion_p, tamano);
    const controles = construirControles(grupo.id_producto, grupo.cantidad, tamano, idFila);
    
    return `
        <div style="border: 1px solid white; padding: 15px; margin: 10px; color: white; border-radius: 8px; background: rgba(0,0,0,0.3); display: flex; align-items: center; gap: 15px;">
            ${construirImagen(grupo.imagen_url, grupo.nombre_p)}
            ${construirInfo(grupo.nombre_p, infoExtra, grupo.precio_unitario, grupo.subtotal)}
            ${controles}
        </div>
    `;
}

// Verifica si es un pack
function esProductoPack(categoria) {
    return categoria && ['pack', 'packs'].includes(categoria.toLowerCase());
}

// Verifica si un valor es válido (no null, no vacío, no 'null')
function esValido(valor) {
    return valor && valor !== 'null' && valor !== '';
}

// Construye la información extra (descripción o tamaño)
function construirInfoExtra(esPack, descripcion, tamano) {
    if (esPack && esValido(descripcion)) {
        return `<p style="margin: 3px 0; font-size: 14px; color: #ccc; font-style: italic;">${descripcion}</p>`;
    }
    
    if (esValido(tamano)) {
        return `<p style="margin: 3px 0; font-size: 14px; color: #fff;"><strong>Tamaño:</strong> ${tamano}</p>`;
    }
    
    return '';
}

// Construye la imagen del producto
function construirImagen(imagenUrl, nombre) {
    return `
        <div style="flex-shrink: 0;">
            <img src="${imagenUrl}" alt="${nombre}" loading="lazy"
                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc;">
        </div>
    `;
}

// Construye la información del producto
function construirInfo(nombre, infoExtra, precioUnitario, subtotal) {
    return `
        <div style="flex-grow: 1;">
            <h4 style="color: #c329c1; margin: 0 0 8px 0; font-size: 18px;">${nombre}</h4>
            ${infoExtra}
            <p style="margin: 3px 0; font-size: 16px;"><strong>Precio:</strong> $${parseFloat(precioUnitario).toFixed(2)}</p>
            <p style="margin: 3px 0; font-size: 16px; color: #28a745;"><strong>Subtotal:</strong> $${parseFloat(subtotal).toFixed(2)}</p>
        </div>
    `;
}

// Construye los controles (botones de cantidad y eliminar) usando id_detalle para operar sobre una fila específica
function construirControles(idProducto, cantidad, tamano, idFila) {
    // Usar id_fila (id_detalle) para los botones + y - para que afecten solo a esa fila específica
    const idDetalle = idFila || idProducto;
    
    return `
        <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: center; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                ${crearBoton('-', 'window.carritoUI.cambiarCantidad', idDetalle, -1, null, '#dc3545', 'Reducir cantidad')}
                <span style="margin: 0 10px; font-weight: bold; font-size: 18px; color: #fff; min-width: 30px; text-align: center;">${cantidad}</span>
                ${crearBoton('+', 'window.carritoUI.cambiarCantidad', idDetalle, 1, null, '#28a745', 'Agregar más')}
                ${crearBoton('X', 'window.carritoUI.eliminar', idFila, null, null, 'transparent', 'Eliminar producto', '#999', 'none')}
            </div>
        </div>
    `;
}

// Crea un botón con estilos
function crearBoton(texto, funcion, idDetalle, cambio, tamano, colorFondo, titulo, colorTexto = 'white', borde = 'none') {
    let onclick = '';
    
    if (funcion === 'window.carritoUI.cambiarCantidad') {
        // Para cambiar cantidad, usar id_detalle (id_fila) para operar sobre una fila específica
        onclick = `${funcion}(${idDetalle}, ${cambio})`;
    } else if (funcion === 'window.carritoUI.eliminar') {
        // Para eliminar, usar id_detalle (id_fila) en lugar de id_producto
        onclick = `${funcion}(${idDetalle})`;
    } else {
        onclick = `${funcion}(${idDetalle}, '${tamano || ''}')`;
    }
    
    const estiloBase = cambio !== null 
        ? `background: ${colorFondo}; color: ${colorTexto}; border: ${borde}; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;`
        : `background: ${colorFondo}; color: ${colorTexto}; border: ${borde}; cursor: pointer; font-size: 16px; padding: 5px;`;
    
    return `<button onclick="${onclick}" style="${estiloBase}" title="${titulo}">${texto}</button>`;
}
