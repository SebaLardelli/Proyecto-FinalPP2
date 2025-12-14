import { API_BASE, estado, obtenerHeaders } from '../shared/index.js';

// Obtiene productos del carrito desde la API
export async function cargarCarrito() {
    try {
        const headers = obtenerHeaders();
        headers['Cache-Control'] = 'no-cache';
        const response = await fetch(`${API_BASE}/carrito?_=` + Date.now(), {
            method: 'GET',
            credentials: 'include',
            headers: headers
        });
        
        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Error al parsear respuesta');
        }
        
        if (!response.ok || !data || !data.ok) {
            if (data?.error !== 'No hay productos en el carrito') {
                alert(data?.error || 'Error al obtener el carrito');
                window.location.href = '/calcomania-proyecto-final/Carrito';
                return;
            }
            estado.carrito = [];
            estado.total = 0;
            return;
        }
        
        if (!data.items || data.items.length === 0) {
            estado.carrito = [];
            estado.total = 0;
            alert('No hay productos en el carrito');
            window.location.href = '/calcomania-proyecto-final/Carrito';
            return;
        }
        
        estado.carrito = data.items.map(item => {
            let tamano = item.tamano || item.tamanio_producto || null;
            if (tamano) {
                tamano = String(tamano).trim();
                if (tamano === '' || tamano.toLowerCase() === 'null') tamano = null;
            }
            
            const categoria = (item.nombre_c || '').trim();
            const esPack = categoria.toLowerCase() === 'pack' || categoria.toLowerCase() === 'packs';
            
            let imagen = item.imagen_url || '';
            if (imagen && !imagen.startsWith('http') && !imagen.startsWith('/')) {
                imagen = '/calcomania-proyecto-final/' + imagen;
            }
            
            const idFila = parseInt(item.id_fila) || parseInt(item.ID_FILA) || 0;
            if (!idFila || idFila <= 0) {
                return null;
            }
            
            return {
                id: idFila,
                nombre: item.nombre_p,
                descripcion: item.descripcion_p || '',
                precio: parseFloat(item.precio_unitario),
                cantidad: parseInt(item.cantidad),
                imagen: imagen,
                tamano: tamano,
                esPack: esPack,
                categoria: categoria
            };
        }).filter(p => p !== null);
        
        estado.total = parseFloat(data.total) || estado.carrito.reduce((sum, p) => sum + (p.precio * p.cantidad), 0);
        
    } catch (error) {
        alert('Error al cargar el carrito');
        window.location.href = '/calcomania-proyecto-final/Carrito';
    }
}

