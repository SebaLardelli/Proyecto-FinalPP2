import { soloDigitos } from '../shared/index.js';

// Configura el evento de paste para inputs OTP
export function configurarPasteOTP(formulario, inputs) {
    formulario.addEventListener('paste', (evento) => {
        // Obtiene el texto pegado del portapapeles
        const texto = (evento.clipboardData || window.clipboardData)?.getData('text') || '';
        
        // Extrae solo los dígitos del texto
        const digitos = soloDigitos(texto);
        
        // Si son exactamente 6 dígitos, llena los inputs automáticamente
        if (/^\d{6}$/.test(digitos)) {
            evento.preventDefault(); // Evita que se pegue normalmente
            
            // Llena cada input con un dígito
            for (let i = 0; i < 6; i++) {
                inputs[i].value = digitos[i] || '';
            }
            
            // Enfoca el último input
            inputs[5].focus();
        }
    });
}
