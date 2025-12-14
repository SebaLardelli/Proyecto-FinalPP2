import { estado } from '../shared/index.js';

// Crea tarjeta HTML de un producto
export function crearTarjetaProducto(grupo) {
    const esPack = (grupo.categoria === 'pack' || grupo.categoria === 'packs');
    // Verificar si tiene tamaños: debe tener más de una variante o variantes con tamaños diferentes
    const tieneTamanos = !esPack && (
        grupo.variantes.length > 1 || 
        grupo.variantes.some(v => {
            const tamano = v.tamano ? String(v.tamano).trim() : '';
            return tamano !== '' && tamano.toLowerCase() !== 'no' && tamano.toLowerCase() !== 'null';
        })
    );
    const primera = grupo.variantes[0];
    const sinStock = grupo.variantes.every(v => (v.stock || 0) <= 0);
    
    let opcionesTamano = '';
    if (tieneTamanos) {
        // Siempre mostrar todos los tamaños disponibles, incluso si están agotados
        // Los tamaños agotados aparecerán deshabilitados con el texto "(Sin stock)"
        const variantesConTamano = grupo.variantes.filter(v => {
            const tamano = v.tamano ? String(v.tamano).trim() : '';
            return tamano !== '' && tamano.toLowerCase() !== 'no' && tamano.toLowerCase() !== 'null';
        });
        
        // Si hay variantes con tamaño válido, usarlas; si no, usar todas las variantes
        const variantesAUsar = variantesConTamano.length > 0 ? variantesConTamano : grupo.variantes;
        
        opcionesTamano = variantesAUsar
            .map((v, index) => {
                const agotado = (v.stock || 0) <= 0;
                const tamanoTexto = v.tamano ? String(v.tamano).trim() : 'Sin tamaño';
                const texto = `${tamanoTexto}${agotado ? ' (Sin stock)' : ''}`;
                // Si todos están agotados, seleccionar el primero para que se vean los tamaños
                const seleccionado = sinStock && index === 0 ? 'selected' : '';
                return `<option value="${v.id}" data-precio="${v.precio}" data-image="${v.imagen}" data-stock="${v.stock || 0}" ${agotado ? 'disabled' : ''} ${agotado ? 'class="opcion-sin-stock"' : ''} ${seleccionado}>${texto}</option>`;
            }).join('');
    }
    
    let botonDeshabilitado = false;
    let tituloBoton = 'title="Inicia sesión para agregar al carrito"';
    
    if (!tieneTamanos && (primera.stock || 0) <= 0) {
        botonDeshabilitado = true;
        tituloBoton = 'title="Sin stock"';
    } else if (tieneTamanos && !esPack && grupo.variantes.every(v => (v.stock || 0) <= 0)) {
        botonDeshabilitado = true;
        tituloBoton = 'title="Sin stock"';
    }
    
    return `
        <div class="${sinStock ? 'producto producto-item sin-stock' : 'producto producto-item'}" data-nombre="${grupo.nombre}" data-categoria="${grupo.categoria}">
            <img class="producto-imagen" src="${primera.imagen}" alt="${grupo.nombre}" loading="lazy" onerror="this.src='/calcomania-proyecto-final/public/Assets/Iconos/icono.png'">
            <div class="producto-detalles">
                <div class="producto-contenido-superior">
                    <h3 class="producto-titulo">${grupo.nombre}</h3>
                    <p class="producto-precio">Precio: $${primera.precio}</p>

                    <div class="producto-info-area">
                        ${tieneTamanos ? `
                            <p class="texto-seleccionar-tamano">Seleccionar tamaño</p>
                            <select class="producto-tamano">${opcionesTamano}</select>
                        ` : esPack ? `
                            <p class="texto-pack-contenido">Contiene:</p>
                            <p class="pack-descripcion">${grupo.descripcion || primera.tamano || 'Pack de productos'}</p>
                        ` : ''}
                    </div>
                </div>

                ${sinStock ? 
                    `<button class="producto-agregar" disabled title="Sin stock">Agotado</button>` : 
                    `<button class="producto-agregar" ${botonDeshabilitado ? 'disabled' : ''} ${tituloBoton}
                            data-id="${primera.id}" data-nombre="${grupo.nombre}" data-precio="${primera.precio}" 
                            data-image="${primera.imagen}" data-stock="${primera.stock ?? 0}"
                            onclick="window.inicioCarrito.agregarAlCarrito(${primera.id})">
                        Agregar
                    </button>`
                }
            </div>
        </div>
    `;
}

