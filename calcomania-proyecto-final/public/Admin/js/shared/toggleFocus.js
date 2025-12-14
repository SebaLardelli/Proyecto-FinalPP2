// Activa label flotante si el input tiene contenido
export function toggleFocus(input) {
    if (input.value.trim() !== "") {
        input.classList.add("has-content");
    } else {
        input.classList.remove("has-content");
    }
}

