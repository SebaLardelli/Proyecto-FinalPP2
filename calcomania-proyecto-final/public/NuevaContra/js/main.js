// Archivo principal - Inicializa todos los módulos
import { configurarLabelsFlotantes, configurarTogglesContrasena } from './shared/index.js';
import { inicializarFormularioCambioContrasena } from './auth/index.js';

const formulario = document.querySelector('#reset-form');
const inputPassword1 = document.querySelector('#new-password');
const inputPassword2 = document.querySelector('#confirm-password');

if (inputPassword1 && inputPassword2) {
    configurarLabelsFlotantes([inputPassword1, inputPassword2]);
}

if (formulario) {
    inicializarFormularioCambioContrasena(formulario, inputPassword1, inputPassword2);
}

configurarTogglesContrasena();
