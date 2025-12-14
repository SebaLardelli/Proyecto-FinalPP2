import { API_BASE, estado, obtenerHeaders } from '../shared/index.js';

// Procesa el pago con validaciones
export async function realizarPago() {
    if (!estado.puntoRetiroSeleccionado) {
        alert('Selecciona un punto de retiro');
        return;
    }
    
    if (estado.metodosSeleccionados.length === 0) {
        alert('Selecciona al menos un método de pago');
        return;
    }
    
    if (estado.metodosSeleccionados.length === 2) {
        const monto1 = parseFloat(document.getElementById(`monto-${estado.metodosSeleccionados[0]}`).value) || 0;
        const monto2 = parseFloat(document.getElementById(`monto-${estado.metodosSeleccionados[1]}`).value) || 0;
        
        if (monto1 < 0 || monto2 < 0) {
            alert('Los montos no pueden ser negativos');
            return;
        }
        
        if (monto1 === 0 || monto2 === 0) {
            alert('En pago combinado, ambos métodos deben tener un monto mayor a $0');
            return;
        }
        
        // Verifica que la suma de los pagos sea igual al total (con margen de 0.01 por precisión de punto flotante)
        // No se usa igualdad exacta (===) porque los decimales pueden tener pequeñas diferencias (ej: 100.00 vs 99.9999999)
        if (Math.abs((monto1 + monto2) - estado.total) > 0.01) {
            alert('La suma de los pagos debe ser igual al total');
            return;
        }
    }
    
    const boton = document.getElementById('realizar-pago');
    boton.disabled = true;
    boton.textContent = 'Procesando...';
    
    try {
        let pagos = [];
        
        if (estado.metodosSeleccionados.length === 1) {
            pagos = [{
                id_metodo_pago: parseInt(estado.metodosSeleccionados[0]),
                monto: estado.total
            }];
        } else {
            const monto1 = parseFloat(document.getElementById(`monto-${estado.metodosSeleccionados[0]}`).value) || 0;
            const monto2 = parseFloat(document.getElementById(`monto-${estado.metodosSeleccionados[1]}`).value) || 0;
            pagos = [
                { id_metodo_pago: parseInt(estado.metodosSeleccionados[0]), monto: monto1 },
                { id_metodo_pago: parseInt(estado.metodosSeleccionados[1]), monto: monto2 }
            ];
        }
        
        const response = await fetch(`${API_BASE}/ventas?action=crear`, {
            method: 'POST',
            credentials: 'include',
            headers: obtenerHeaders(),
            body: JSON.stringify({
                punto_retiro_id: parseInt(estado.puntoRetiroSeleccionado),
                pagos: pagos
            })
        });
        
        const data = await response.json();
        
        if (response.ok && data.ok) {
            alert('Pago realizado exitosamente');
            setTimeout(() => {
                window.location.href = '/calcomania-proyecto-final/Carrito';
            }, 500);
        } else {
            alert(data.error || 'Error al procesar el pago');
        }
    } catch (error) {
        alert('Error de conexión');
    } finally {
        boton.disabled = false;
        boton.textContent = 'Realizar Pago';
    }
}

