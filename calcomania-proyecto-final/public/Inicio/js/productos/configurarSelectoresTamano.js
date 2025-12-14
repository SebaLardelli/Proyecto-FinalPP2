import { estado } from '../shared/index.js';

// Configura eventos de selectores de tamaño
export function configurarSelectoresTamano() {
    document.querySelectorAll('.producto-tamano').forEach(select => {
        select.addEventListener('change', function() {
            const tarjeta = this.closest('.producto');
            const precio = tarjeta.querySelector('.producto-precio');
            const boton = tarjeta.querySelector('.producto-agregar');
            
            if (!this.value || !precio || !boton) {
                // Si no hay valor seleccionado (opción deshabilitada), deshabilitar el botón
                if (boton) {
                    boton.disabled = true;
                    boton.textContent = 'Agotado';
                    boton.setAttribute('title', 'Sin stock');
                }
                return;
            }
            
            const opcion = this.options[this.selectedIndex];
            const nuevoPrecio = opcion.getAttribute('data-precio');
            const nuevoId = this.value;
            const nuevoStock = opcion.getAttribute('data-stock');
            const nuevaImagen = opcion.getAttribute('data-image');
            
            precio.textContent = `Precio: $${parseFloat(nuevoPrecio).toFixed(2)}`;
            
            boton.setAttribute('data-id', nuevoId);
            boton.setAttribute('data-precio', nuevoPrecio);
            boton.setAttribute('data-stock', nuevoStock);
            boton.setAttribute('data-image', nuevaImagen);
            
            if (parseInt(nuevoStock) <= 0) {
                // Si el tamaño seleccionado está agotado, deshabilitar el botón
                boton.disabled = true;
                boton.textContent = 'Agotado';
                boton.setAttribute('title', 'Sin stock');
                boton.removeAttribute('onclick');
            } else {
                // Si hay stock, habilitar el botón
                boton.disabled = false;
                boton.textContent = 'Agregar';
                boton.setAttribute('onclick', `window.inicioCarrito.agregarAlCarrito(${nuevoId})`);
                boton.setAttribute('title', estado.sesion.autenticado ? '' : 'Inicia sesión para agregar al carrito');
            }
        });
        
        // Configurar estado inicial del botón según el tamaño seleccionado
        const tarjeta = select.closest('.producto');
        const boton = tarjeta?.querySelector('.producto-agregar');
        if (boton && select.options.length > 0) {
            // Asegurarse de que siempre haya una opción seleccionada para mostrar los tamaños
            if (!select.value && select.options.length > 0) {
                // Seleccionar la primera opción disponible (aunque esté deshabilitada)
                select.selectedIndex = 0;
            }
            
            // Si hay un valor seleccionado, actualizar el botón según ese tamaño
            if (select.value) {
                const opcion = select.options[select.selectedIndex];
                if (opcion) {
                    const stock = parseInt(opcion.getAttribute('data-stock') || '0');
                    
                    if (stock <= 0) {
                        boton.disabled = true;
                        boton.textContent = 'Agotado';
                        boton.setAttribute('title', 'Sin stock');
                        boton.removeAttribute('onclick');
                    } else {
                        boton.disabled = false;
                        boton.textContent = 'Agregar';
                        const nuevoId = select.value;
                        boton.setAttribute('onclick', `window.inicioCarrito.agregarAlCarrito(${nuevoId})`);
                        boton.setAttribute('title', estado.sesion.autenticado ? '' : 'Inicia sesión para agregar al carrito');
                    }
                }
            } else {
                // Si no hay valor seleccionado, buscar la primera opción habilitada
                let primeraOpcionHabilitada = null;
                for (let i = 0; i < select.options.length; i++) {
                    if (!select.options[i].disabled && select.options[i].value) {
                        primeraOpcionHabilitada = select.options[i];
                        break;
                    }
                }
                
                // Si hay opciones habilitadas pero ninguna está seleccionada, seleccionar la primera
                if (primeraOpcionHabilitada) {
                    select.value = primeraOpcionHabilitada.value;
                    select.dispatchEvent(new Event('change'));
                } else if (select.options.length > 0) {
                    // Si todas están deshabilitadas, seleccionar la primera para que se vean los tamaños
                    select.selectedIndex = 0;
                    select.dispatchEvent(new Event('change'));
                }
            }
        }
    });
}

