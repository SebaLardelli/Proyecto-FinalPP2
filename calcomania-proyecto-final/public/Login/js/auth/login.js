import { API_URL, $, mostrarMensaje, validarEmail } from '../shared/index.js';

const STORAGE_KEY = 'remember_email';

export async function hacerLogin(evento) {
    evento.preventDefault();
    
    const email = $('email').value.trim();
    const password = $('password').value;
    
    if (!email) {
        mostrarMensaje('Ingresa tu email');
        $('email').focus();
        return;
    }
    
    if (!validarEmail(email)) {
        mostrarMensaje('El formato del email no es válido');
        $('email').focus();
        return;
    }
    
    if (!password) {
        mostrarMensaje('Ingresa tu contraseña');
        $('password').focus();
        return;
    }
    
    if ($('remember-me')?.checked) {
        try {
            localStorage.setItem(STORAGE_KEY, email);
        } catch (error) {
            // Ignorar problemas con localStorage
        }
    } else {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (error) {
            // Ignorar
        }
    }

    const boton = $('login-button');
    boton.disabled = true;
    boton.textContent = 'Ingresando...';
    
    try {
        const respuesta = await fetch(API_URL, {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        // Verificar si la respuesta es JSON
        let datos;
        try {
            datos = await respuesta.json();
        } catch (e) {
            mostrarMensaje('Error al iniciar sesión. Respuesta inválida del servidor.');
            return;
        }
        
        if (!respuesta.ok || !datos.ok) {
            mostrarMensaje(datos.error || 'Error al iniciar sesión');
            return;
        }
        
        // Guardar token JWT
        if (datos.token) {
            try {
                localStorage.setItem('jwt_token', datos.token);
                
                // Verificar que el token se guardó correctamente
                const tokenGuardado = localStorage.getItem('jwt_token');
                if (tokenGuardado !== datos.token) {
                    mostrarMensaje('Error guardando token de sesión');
                    return;
                }
            } catch (error) {
                mostrarMensaje('Error guardando token de sesión');
                return;
            }
        } else {
            mostrarMensaje('Error: No se recibió token de sesión');
            return;
        }
        
        // Verificar que el token se puede decodificar antes de continuar
        try {
            const parts = datos.token.split('.');
            if (parts.length !== 3) {
                throw new Error('Token inválido: formato incorrecto');
            }
            
            const payloadBase64 = parts[1];
            const padding = payloadBase64.length % 4;
            const paddedBase64 = padding ? payloadBase64 + '='.repeat(4 - padding) : payloadBase64;
            const payload = JSON.parse(atob(paddedBase64));
            
            const ahora = Math.floor(Date.now() / 1000);
            if (payload.exp && ahora >= payload.exp) {
                throw new Error('Token ya expirado al momento de generarse');
            }
        } catch (error) {
            mostrarMensaje('Error: El token generado no es válido. Por favor, intenta nuevamente.');
            localStorage.removeItem('jwt_token');
            return;
        }
        
        mostrarMensaje('¡Bienvenido!', true);
        
        // Redirigir después de un breve delay
        setTimeout(() => {
            // Asegurar que el token correcto esté guardado
            const tokenActual = localStorage.getItem('jwt_token');
            if (tokenActual !== datos.token) {
                localStorage.setItem('jwt_token', datos.token);
            }
            
            // Verificar una vez más antes de redirigir
            const tokenVerificado = localStorage.getItem('jwt_token');
            if (!tokenVerificado || tokenVerificado !== datos.token) {
                mostrarMensaje('Error guardando sesión. Por favor, intenta nuevamente.');
                return;
            }
            
            // Obtener redirect del backend o calcularlo desde el rol
            let redirect = datos.redirect;
            const rol = datos.usuario?.rol ?? datos.usuario?.id_rol ?? datos.rol;
            
            // Si no hay redirect del backend, calcularlo desde el rol
            if (!redirect) {
                if (rol === 1 || rol === '1') {
                    redirect = '/calcomania-proyecto-final/Admin';
                } else {
                    redirect = '/calcomania-proyecto-final/Inicio';
                }
            }
            
            window.location.href = redirect;
        }, 800);
        
    } catch (error) {
        mostrarMensaje('Error de conexión. Verifica tu internet.');
    } finally {
        boton.disabled = false;
        boton.textContent = 'Acceder';
    }
}

