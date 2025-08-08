import Swal from 'sweetalert2';

// Inicialización al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    configurarEventos();
});

function configurarEventos() {
    // Contador de caracteres para el textarea
    const textarea = document.getElementById('tic_comentario_falla');
    const contador = document.getElementById('contadorCaracteres');
    
    textarea.addEventListener('input', function() {
        const longitud = this.value.length;
        contador.textContent = longitud;
        
        // Cambiar estilos según longitud
        this.classList.remove('border-success', 'border-warning', 'border-danger');
        if (longitud < 15) {
            this.classList.add('border-danger');
            contador.className = 'text-danger fw-bold';
        } else if (longitud > 1800) {
            this.classList.add('border-warning');
            contador.className = 'text-warning fw-bold';
        } else {
            this.classList.add('border-success');
            contador.className = 'text-success';
        }
    });

    // Vista previa de imagen
    const inputImagen = document.getElementById('tic_imagen');
    inputImagen.addEventListener('change', mostrarVistaPrevia);

    // Enviar formulario
    document.getElementById('formTicket').addEventListener('submit', enviarTicket);

    // Limpiar formulario
    document.getElementById('BtnLimpiar').addEventListener('click', limpiarFormulario);

    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalTicket();
        }
    });

    // Cerrar modal al hacer clic fuera del contenido
    document.getElementById('modalTicket')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalTicket();
        }
    });
}

function mostrarVistaPrevia(evento) {
    const archivo = evento.target.files[0];
    const vistaPrevia = document.getElementById('vistaPrevia');
    const contenedor = document.getElementById('contenedorVistaPrevia');

    if (archivo) {
        // Validaciones
        if (!archivo.type.startsWith('image/')) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo No Válido',
                text: 'Solo se permiten archivos de imagen',
                confirmButtonText: 'Entendido'
            });
            evento.target.value = '';
            contenedor.classList.add('d-none');
            return;
        }

        if (archivo.size > 8 * 1024 * 1024) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo Muy Grande',
                text: 'La imagen no puede ser mayor a 8MB',
                confirmButtonText: 'Entendido'
            });
            evento.target.value = '';
            contenedor.classList.add('d-none');
            return;
        }

        // Mostrar vista previa
        const lector = new FileReader();
        lector.onload = function(e) {
            vistaPrevia.src = e.target.result;
            contenedor.classList.remove('d-none');
        };
        lector.readAsDataURL(archivo);
    } else {
        contenedor.classList.add('d-none');
    }
}

async function enviarTicket(evento) {
    evento.preventDefault();

    // Validaciones del formulario
    const correo = document.getElementById('tic_correo_electronico').value.trim();
    const descripcion = document.getElementById('tic_comentario_falla').value.trim();

    if (!correo) {
        Swal.fire({
            icon: 'error',
            title: 'Campo Requerido',
            text: 'El correo electrónico es obligatorio',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    if (!validarCorreoElectronico(correo)) {
        Swal.fire({
            icon: 'error',
            title: 'Correo Inválido',
            text: 'Ingrese un correo electrónico válido',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    if (descripcion.length < 15) {
        Swal.fire({
            icon: 'error',
            title: 'Descripción Insuficiente',
            text: 'La descripción debe tener al menos 15 caracteres',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    if (descripcion.length > 2000) {
        Swal.fire({
            icon: 'error',
            title: 'Descripción Muy Larga',
            text: 'La descripción no puede exceder 2000 caracteres',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const boton = document.getElementById('BtnEnviar');
    const textoOriginal = boton.innerHTML;
    boton.disabled = true;
    boton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Enviando...';

    const formData = new FormData(evento.target);

    try {
        const appName = window.location.pathname.split('/')[1];
        const response = await fetch(`/${appName}/ticket/guardar`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.codigo == 1) {
            // MOSTRAR MODAL EN LUGAR DEL SWEETALERT
            mostrarModalTicket({
                numeroTicket: data.data.numero_ticket,
                correo: correo,
                descripcion: descripcion,
                imagen: obtenerImagenSeleccionada()
            });

            // Limpiar formulario después de mostrar el modal
            setTimeout(() => {
                limpiarFormulario();
            }, 500);

        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Error al Enviar Ticket',
                text: data.mensaje,
                confirmButtonText: 'Intentar de Nuevo'
            });
        }
    } catch (error) {
        console.error('Error al enviar ticket:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error de Conexión',
            text: 'No se pudo conectar con el servidor',
            confirmButtonText: 'Reintentar'
        });
    } finally {
        // Restaurar botón
        boton.disabled = false;
        boton.innerHTML = textoOriginal;
    }
}

function mostrarModalTicket(datosTicket) {
    // Poblar datos del modal
    document.getElementById('ticketModalTitle').textContent = `Detalles de Ticket ${datosTicket.numeroTicket}`;
    document.getElementById('ticketNumero').textContent = datosTicket.numeroTicket;
    document.getElementById('ticketFecha').textContent = obtenerFechaActual();
    document.getElementById('ticketEmail').textContent = datosTicket.correo;
    document.getElementById('ticketDescripcion').textContent = datosTicket.descripcion;

    // Mostrar imagen si existe
    const imagenSection = document.getElementById('imagenSection');
    const ticketImagen = document.getElementById('ticketImagen');
    
    if (datosTicket.imagen) {
        ticketImagen.src = datosTicket.imagen;
        imagenSection.style.display = 'block';
    } else {
        imagenSection.style.display = 'none';
    }

    // Mostrar el modal
    const modal = document.getElementById('modalTicket');
    modal.style.display = 'flex';
    
    // Añadir animación de entrada
    setTimeout(() => {
        modal.classList.add('show');
    }, 50);

    // Enfocar el modal para accesibilidad
    modal.focus();
}

function cerrarModalTicket() {
    const modal = document.getElementById('modalTicket');
    
    if (modal && modal.style.display !== 'none') {
        // Animación de salida
        modal.style.animation = 'fadeOut 0.3s ease-out';
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.style.animation = '';
            modal.classList.remove('show');
        }, 300);
    }
}

function obtenerImagenSeleccionada() {
    const inputImagen = document.getElementById('tic_imagen');
    const vistaPrevia = document.getElementById('vistaPrevia');
    
    if (inputImagen.files && inputImagen.files[0] && vistaPrevia.src) {
        return vistaPrevia.src;
    }
    
    return null;
}

function obtenerFechaActual() {
    const ahora = new Date();
    const dia = ahora.getDate().toString().padStart(2, '0');
    const mes = (ahora.getMonth() + 1).toString().padStart(2, '0');
    const año = ahora.getFullYear();
    const horas = ahora.getHours().toString().padStart(2, '0');
    const minutos = ahora.getMinutes().toString().padStart(2, '0');
    
    return `${dia}/${mes}/${año} ${horas}:${minutos}`;
}

function limpiarFormulario() {
    // Limpiar el formulario
    document.getElementById('formTicket').reset();
    
    // Ocultar vista previa
    document.getElementById('contenedorVistaPrevia').classList.add('d-none');
    
    // Resetear contador
    document.getElementById('contadorCaracteres').textContent = '0';
    
    // Limpiar estilos del textarea
    const textarea = document.getElementById('tic_comentario_falla');
    textarea.classList.remove('border-success', 'border-warning', 'border-danger');
    
    // Mensaje de confirmación (solo si no hay modal visible)
    const modal = document.getElementById('modalTicket');
    if (!modal || modal.style.display === 'none') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });

        Toast.fire({
            icon: 'info',
            title: 'Formulario limpiado correctamente'
        });
    }
}

function validarCorreoElectronico(correo) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(correo);
}

// Hacer la función cerrarModalTicket global para que pueda ser llamada desde el HTML
window.cerrarModalTicket = cerrarModalTicket;