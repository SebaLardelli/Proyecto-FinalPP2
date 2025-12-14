// Archivo principal - Inicializa todos los módulos de verificación OTP
import { configurarInputsOTP, configurarPasteOTP, inicializarVerificacion, reenviarCodigo } from './otp/index.js';
import { moveToNext } from './shared/index.js';

const formulario = document.querySelector('#form');
const inputs = Array.from(document.querySelectorAll('.otp-inputs input'));
const boton = formulario?.querySelector('button');

if (formulario && inputs.length > 0) {
    configurarInputsOTP(inputs);
    configurarPasteOTP(formulario, inputs);
    inicializarVerificacion(formulario, inputs, boton);
}

// Exponer funciones para uso en HTML
window.reenviarCodigo = reenviarCodigo;
window.moveToNext = moveToNext;
