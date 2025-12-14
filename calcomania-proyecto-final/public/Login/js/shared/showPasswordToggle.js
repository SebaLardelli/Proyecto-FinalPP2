import { $ } from './getElement.js';

// Muestra u oculta botón de toggle password
export function showPasswordToggle(visible) {
    const toggle = $("password-toggle");
    const campo = $("password");
    
    if (toggle && campo) {
        toggle.style.display = (visible || campo.value.trim() !== "") ? "inline" : "none";
    }
}

