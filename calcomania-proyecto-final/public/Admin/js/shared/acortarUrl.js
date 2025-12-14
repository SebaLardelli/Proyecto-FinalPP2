// Acorta URLs largas para mostrar
export function acortarURL(url) {
    if (!url) return '';
    
    const nombreArchivo = url.split('/').pop();
    
    if (nombreArchivo.length > 25) {
        const extension = nombreArchivo.split('.').pop();
        const nombre = nombreArchivo.replace('.' + extension, '');
        return nombre.substring(0, 20) + '...' + extension;
    }
    
    return nombreArchivo;
}

