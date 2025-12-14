// Mueve el foco al siguiente input cuando se completa un dígito
export function moveToNext(current, nextId) {
    if (current.value.length === 1) {
        document.getElementById(nextId).focus();
    }
}

