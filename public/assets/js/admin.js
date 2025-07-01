// Captura todos los select de estado
const estadoSelects = document.querySelectorAll('.estado-select');

estadoSelects.forEach((estadoSelect) => {
    estadoSelect.addEventListener('change', function () {
        const row = this.closest('tr');
        const rolSelect = row.querySelector('.rol-select');

        if (this.value === 'aprobado') {
            rolSelect.removeAttribute('disabled');
        } else {
            rolSelect.setAttribute('disabled', 'disabled');
            rolSelect.value = ""; // Limpia el valor del rol si no está aprobado
        }
    });
});

// Detecta cambios en estado o rol
const filas = document.querySelectorAll('tr');

filas.forEach((fila) => {
    const estado = fila.querySelector('.estado-select');
    const rol = fila.querySelector('.rol-select');

    if (estado && rol) {
        estado.addEventListener('change', () => {
            fila.classList.add('cambiado');
        });

        rol.addEventListener('change', () => {
            fila.classList.add('cambiado');
        });
    }

    // Cuando se envía el formulario, quitar la clase 'cambiado'
    const form = fila.querySelector('form');
    if (form) {
        form.addEventListener('submit', () => {
            fila.classList.remove('cambiado');

        });
    }
});

// Mostrar mensaje "Guardado"
const noti = document.getElementById('notificacion-guardado');
if (noti) {
    setTimeout(() => {
        noti.style.opacity = '0';
    }, 1000); // desaparece después de 3 segundos
}
