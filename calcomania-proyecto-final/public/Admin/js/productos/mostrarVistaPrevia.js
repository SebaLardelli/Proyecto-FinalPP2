import { acortarURL } from '../shared/index.js';

// Muestra preview de la imagen seleccionada
export function mostrarVistaPrevia(input) {
    const archivo = input.files[0];
    if (!archivo) return;
    
    const lector = new FileReader();
    lector.onload = function(e) {
        const preview = document.getElementById('preview-imagen');
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    lector.readAsDataURL(archivo);
    
    const nombreArchivo = archivo.name;
    const rutaImagen = `../Uploads/ImagenProducto/${nombreArchivo}`;
    
    input.classList.add('has-content');
    
    let mostrarRuta = document.getElementById('imagen-url-display');
    if (!mostrarRuta) {
        mostrarRuta = document.createElement('div');
        mostrarRuta.id = 'imagen-url-display';
        mostrarRuta.style.cssText = `
            position: absolute; top: 0; left: 0; width: 100%; height: 56px;
            background: transparent; border: none; font-size: 1.1rem; color: #fff;
            padding: 0 45px 0 40px; display: flex; align-items: center;
            pointer-events: none; z-index: 2;
        `;
        
        const contenedor = input.parentElement;
        contenedor.style.position = 'relative';
        contenedor.appendChild(mostrarRuta);
    }
    
    mostrarRuta.textContent = acortarURL(rutaImagen);
    
    const label = input.nextElementSibling;
    label.style.top = '-10px';
    label.style.fontSize = '0.95rem';
    label.style.color = '#ffffff';
}

