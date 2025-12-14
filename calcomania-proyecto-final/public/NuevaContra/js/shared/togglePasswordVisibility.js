// Configura todos los toggles de visibilidad de contraseña
export function configurarTogglesContrasena() {
    document.querySelectorAll('.password-toggle').forEach(botonToggle => {
        if (!botonToggle) return;
        
        const idCampo = botonToggle.getAttribute('data-target');
        const campo = document.getElementById(idCampo);
        if (!campo) return;

        const iconoVer = botonToggle.querySelector('.eye-on');
        const iconoOcultar = botonToggle.querySelector('.eye-off');

        function actualizarIconos() {
            const esPassword = campo.type === 'password';
            if (iconoVer) iconoVer.style.display = esPassword ? 'inline' : 'none';
            if (iconoOcultar) iconoOcultar.style.display = esPassword ? 'none' : 'inline';
        }
        
        actualizarIconos();

        botonToggle.addEventListener('click', () => {
            campo.type = campo.type === 'password' ? 'text' : 'password';
            actualizarIconos();
            campo.focus();
        });

        campo.addEventListener('input', () => {
            campo.classList.toggle('has-content', campo.value.trim() !== '');
        });
    });
}

