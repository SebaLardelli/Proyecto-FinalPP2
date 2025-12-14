import { $ } from './getElement.js';

// Alterna mostrar/ocultar contraseña
export function togglePasswordVisibility() {
    const campo = $("password");
    const iconoVer = $("eye-icon");
    const iconoOcultar = $("eye-off-icon");

    if (!campo || !iconoVer || !iconoOcultar) return;
    
    if (campo.type === "password") {
        campo.type = "text";
        iconoVer.style.display = "none";
        iconoOcultar.style.display = "inline";
    } else {
        campo.type = "password";
        iconoVer.style.display = "inline";
        iconoOcultar.style.display = "none";
    }
}

