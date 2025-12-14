// Activa label flotante si el input tiene contenido
export function toggleFocus(input) {
    if (input.value.trim() === "") {
        input.classList.remove("has-content");
    } else {
        input.classList.add("has-content");
    }
}

