import { agruparProductos } from './agrupar.js';
import { crearTarjetaProducto } from './crearTarjeta.js';
import { configurarSelectoresTamano } from './configurarSelectoresTamano.js';

// Muestra productos en el contenedor
export function mostrarProductos(productos) {
    const contenedor = document.getElementById('contenedor-productos');
    
    if (productos.length === 0) {
        contenedor.innerHTML = `
            <div style="text-align: center; color: #fff; padding: 40px;">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #666;"></i>
                <p style="margin-top: 15px; font-size: 1.2rem;">No hay productos disponibles</p>
            </div>
        `;
        return;
    }
    
    const grupos = agruparProductos(productos);
    
    contenedor.innerHTML = grupos.map(grupo => crearTarjetaProducto(grupo)).join('');
    
    configurarSelectoresTamano();
}

