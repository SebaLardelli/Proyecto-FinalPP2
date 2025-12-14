// Alterna visibilidad de contraseña
import { toggleFocus } from './toggleFocus.js';

export function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + "-eye");
    const eyeOff = document.getElementById(fieldId + "-eye-off");
    const isPw = input.type === "password";
    input.type = isPw ? "text" : "password";
    eye.style.display = isPw ? "none" : "inline-block";
    eyeOff.style.display = isPw ? "inline-block" : "none";
}

// Muestra/oculta el toggle de contraseña según si hay contenido
export function showPasswordToggle(inputElement) {
    const toggle = inputElement.nextElementSibling;
    toggle.style.display = inputElement.value.trim() ? "inline" : "none";
    toggleFocus(inputElement);
}

