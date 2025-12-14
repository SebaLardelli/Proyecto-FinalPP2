// Login
import { $, toggleFocus, showPasswordToggle, togglePasswordVisibility } from './shared/index.js';
import { hacerLogin, verificarSesion } from './auth/index.js';

const STORAGE_KEY = 'remember_email';

// Exponer para HTML
window.toggleFocus = toggleFocus;
window.showPasswordToggle = showPasswordToggle;
window.togglePasswordVisibility = togglePasswordVisibility;

document.addEventListener('DOMContentLoaded', async function() {
    const formulario = document.querySelector('form');
    if (formulario) {
        formulario.addEventListener('submit', hacerLogin);
    }
    
    // Email guardado
    let emailGuardado = '';
    try {
        emailGuardado = localStorage.getItem(STORAGE_KEY) || '';
    } catch (error) {
        emailGuardado = '';
    }
    const campoEmail = $('email');
    const recordar = $('remember-me');

    if (emailGuardado && campoEmail) {
        campoEmail.value = emailGuardado;
        campoEmail.classList.add("has-content");
        if (recordar) recordar.checked = true;
    }
    
    // Verificar sesión activa
    if (await verificarSesion()) return;

    // Estilos campos con contenido
    const campoPassword = $('password');
    
    if (campoEmail && campoEmail.value.trim() !== '') {
        campoEmail.classList.add('has-content');
    }
    if (campoPassword && campoPassword.value.trim() !== '') {
        campoPassword.classList.add('has-content');
    }
    
    // Foco inicial
    setTimeout(() => {
        if (campoEmail && campoEmail.value.trim() !== '') {
            campoPassword.focus();
        } else {
            campoEmail.focus();
        }
    }, 100);
    
    // Actualizar almacenamiento al cambiar email o recordar
    if (campoEmail && recordar) {
        campoEmail.addEventListener('input', () => {
            if (recordar.checked) {
                try {
                    localStorage.setItem(STORAGE_KEY, campoEmail.value.trim());
                } catch (error) {
                    // Ignorar
                }
            }
        });
        
        recordar.addEventListener('change', () => {
            if (recordar.checked) {
                try {
                    localStorage.setItem(STORAGE_KEY, campoEmail.value.trim());
                } catch (error) {
                    // Ignorar
                }
            } else {
                try {
                    localStorage.removeItem(STORAGE_KEY);
                } catch (error) {
                    // Ignorar
                }
            }
        });
    }
});
