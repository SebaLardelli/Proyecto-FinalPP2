import { estado } from '../shared/index.js';

// Muestra resumen de productos agrupados
export function mostrarResumen() {
    const container = document.getElementById('resumen-productos');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (!estado.carrito || estado.carrito.length === 0) {
        container.innerHTML = '<p>No hay productos en el carrito</p>';
        return;
    }
    
    const productosAgrupados = {};
    estado.carrito.forEach(producto => {
        const clave = `${producto.nombre}_${producto.tamano || 'sin_tamano'}`;
        
        if (productosAgrupados[clave]) {
            productosAgrupados[clave].cantidad += producto.cantidad || 1;
            productosAgrupados[clave].subtotal = (productosAgrupados[clave].precio * productosAgrupados[clave].cantidad).toFixed(2);
        } else {
            const subtotal = ((producto.precio || 0) * (producto.cantidad || 1));
            productosAgrupados[clave] = {
                ...producto,
                cantidad: producto.cantidad || 1,
                subtotal: subtotal.toFixed(2)
            };
        }
    });
    
    const productosHtml = Object.values(productosAgrupados).map(producto => {
        const esPack = producto.esPack;
        let infoSection = '';
        
        if (esPack && producto.descripcion) {
            const d = String(producto.descripcion).trim();
            if (d && d !== 'null' && d !== '') {
                infoSection = `<div class="tamano-resumen-section"><div class="tamano-resumen-label">Descripción</div><div class="tamano-resumen-valor">${d}</div></div>`;
            }
        } else if (!esPack && producto.tamano) {
            const t = String(producto.tamano).trim();
            if (t && t !== 'null' && t !== '') {
                infoSection = `<div class="tamano-resumen-section"><div class="tamano-resumen-label">Tamaño</div><div class="tamano-resumen-valor">${t}</div></div>`;
            }
        }
        
        return `<div class="producto-resumen" data-id="${producto.id}">
            <div class="producto-resumen-imagen">
                <img src="${producto.imagen || ''}" alt="${producto.nombre || ''}" loading="lazy" onerror="this.src='/calcomania-proyecto-final/Assets/Iconos/icono.png'">
                <div class="producto-resumen-nombre"><strong>${producto.nombre || 'Producto'}</strong></div>
            </div>
            ${infoSection}
            <div class="cantidad-resumen-section">
                <div class="cantidad-resumen-label">Cantidad</div>
                <div class="cantidad-resumen-valor">${producto.cantidad}</div>
            </div>
            <div class="precio-resumen-section">
                <div class="precio-resumen-label">Subtotal</div>
                <div class="precio-resumen-valor">$${producto.subtotal}</div>
            </div>
        </div>`;
    }).join('');
    
    container.innerHTML = productosHtml + `<div class="total-resumen"><strong>Total: $${estado.total.toFixed(2)}</strong></div>`;
}

