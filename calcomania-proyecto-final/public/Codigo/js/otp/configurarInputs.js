import { soloDigitos } from '../shared/index.js';

// Configura los inputs OTP (eventos y navegación)
export function configurarInputsOTP(inputs) {
    inputs.forEach((input, indice) => {
        // Configura atributos básicos
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('autocomplete', 'one-time-code');
        input.maxLength = 1;

        // Cuando se escribe un dígito
        input.addEventListener('input', (evento) => {
            // Solo permite dígitos y máximo 1 carácter
            evento.target.value = soloDigitos(evento.target.value).slice(0, 1);
            
            // Si escribió algo y no es el último input, pasa al siguiente
            if (evento.target.value && indice < inputs.length - 1) {
                inputs[indice + 1].focus();
            }
        });

        // Cuando se presiona una tecla
        input.addEventListener('keydown', (evento) => {
            // Backspace: si está vacío, borra el anterior y va atrás
            if (evento.key === 'Backspace' && !input.value && indice > 0) {
                inputs[indice - 1].value = '';
                inputs[indice - 1].focus();
            }
            
            // Flecha izquierda: va al input anterior
            if (evento.key === 'ArrowLeft' && indice > 0) {
                inputs[indice - 1].focus();
            }
            
            // Flecha derecha: va al input siguiente
            if (evento.key === 'ArrowRight' && indice < inputs.length - 1) {
                inputs[indice + 1].focus();
            }
        });
    });

    // Enfoca el primer input al cargar
    inputs[0]?.focus();
}
