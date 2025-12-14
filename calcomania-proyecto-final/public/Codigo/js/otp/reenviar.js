// Reenvía el código OTP
export function reenviarCodigo() {
    const email = localStorage.getItem('otp_email') || '';
    
    if (!email) {
        alert('No encontramos tu email. Volvé a "Recuperar Contraseña".');
        location.assign('/calcomania-proyecto-final/Recuperacion');
        return;
    }

    fetch('/calcomania-proyecto-final/api/auth/recuperar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    })
    .then(respuesta => respuesta.json())
    .then(datos => {
        if (datos.ok) {
            alert(datos.mensaje + '. Revisa tu email.');
        } else {
            alert(datos.error || 'Error al reenviar código');
        }
    })
    .catch(error => {
        alert('Error de conexión');
    });
}

