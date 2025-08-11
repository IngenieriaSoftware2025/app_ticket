
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formTicket');
    const btnEnviar = document.getElementById('BtnEnviar');
    
    if (!formulario) return;
    
    // Cargar aplicaciones al inicializar
    cargarAplicaciones();
    
    // Eventos del formulario
    formulario.addEventListener('submit', enviarFormulario);
    
    // Validación en tiempo real para cada campo
    const campos = formulario.querySelectorAll('input, select, textarea');
    campos.forEach(campo => {
        campo.addEventListener('blur', validarCampo);
        campo.addEventListener('input', limpiarError);
    });
    
    // Event listeners para botones
    const btnLimpiar = document.getElementById('BtnLimpiar');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarFormulario);
    }

    // Event listener para vista previa de imagen
    const inputImagen = document.getElementById('tic_imagen');
    if (inputImagen) {
        inputImagen.addEventListener('change', mostrarVistaPrevia);
    }

    // Configurar contador de caracteres
    configurarContadorCaracteres();
});

// Carga las aplicaciones desde el servidor
async function cargarAplicaciones() {
    try {
        const appName = window.location.pathname.split('/')[1];
        const response = await fetch(`/${appName}/ticket/aplicaciones`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Respuesta del servidor:', data); // Para debug
        
        if (data.codigo === 1 && data.data) {
            const selectApp = document.getElementById('tic_app');
            
            if (!selectApp) {
                console.error('No se encontró el elemento select con id "tic_app"');
                return;
            }
            
            // Limpiar opciones existentes excepto la primera
            selectApp.innerHTML = '<option value="">Seleccione la aplicación con problemas...</option>';
            
            // Agregar las aplicaciones
            data.data.forEach(app => {
                const option = document.createElement('option');
                option.value = app.menu_codigo;
                option.textContent = app.menu_descr;
                selectApp.appendChild(option);
            });
            
            console.log(`Se cargaron ${data.data.length} aplicaciones`);
        } else {
            console.error('Error en la respuesta:', data.mensaje || 'Respuesta inválida');
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al cargar aplicaciones'
        });
    }
}

// Maneja el envío del formulario
async function enviarFormulario(evento) {
    evento.preventDefault();
    
    const formulario = evento.target;
    const btnEnviar = document.getElementById('BtnEnviar');
    
    // Solo usar validaciones específicas (que sí conocen todos los campos)
    if (!validacionesEspecificas(formulario)) {
        return;
    }
    
    // Cambiar estado del botón
    const textoOriginal = btnEnviar.innerHTML;
    btnEnviar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando Ticket...';
    btnEnviar.disabled = true;
    
    try {
        // URL corregida para enviar el formulario
        const appName = window.location.pathname.split('/')[1];
        const url = `/${appName}/ticket/guardar`;
        
        const respuesta = await fetch(url, {
            method: 'POST',
            body: new FormData(formulario)
        });
        
        const datos = await respuesta.json();
        
        if (datos.codigo === 1) {
            // Éxito - mostrar modal personalizado
            mostrarModalTicket(datos.data, formulario);
            
            // Limpiar formulario después del éxito
            formulario.reset();
            limpiarClasesValidacion();
            cargarAplicaciones(); // Recargar aplicaciones
            
        } else {
            // Error del servidor
            await Swal.fire({
                icon: 'error',
                title: 'Error al crear el ticket',
                text: datos.mensaje || 'Ocurrió un error inesperado',
                confirmButtonText: 'Intentar de nuevo'
            });
        }
        
    } catch (error) {
        console.error('Error al enviar formulario:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Verifique su conexión e intente nuevamente.',
            confirmButtonText: 'Entendido'
        });
    } finally {
        // Restaurar botón
        btnEnviar.innerHTML = textoOriginal;
        btnEnviar.disabled = false;
    }
}

// Muestra el modal personalizado con los datos del ticket creado
function mostrarModalTicket(datosTicket, formulario) {
    // Obtener elementos del modal
    const modal = document.getElementById('modalTicket');
    const ticketNumero = document.getElementById('ticketNumero');
    const ticketFecha = document.getElementById('ticketFecha');
    const ticketEmail = document.getElementById('ticketEmail');
    const ticketDescripcion = document.getElementById('ticketDescripcion');
    const imagenSection = document.getElementById('imagenSection');
    const ticketImagen = document.getElementById('ticketImagen');
    
    if (!modal) {
        console.error('No se encontró el modal con id "modalTicket"');
        return;
    }
    
    // Llenar datos del ticket
    if (ticketNumero) ticketNumero.textContent = datosTicket.numero_ticket;
    if (ticketFecha) ticketFecha.textContent = new Date().toLocaleDateString('es-ES');
    if (ticketEmail) ticketEmail.textContent = formulario.querySelector('#tic_correo_electronico').value;
    if (ticketDescripcion) ticketDescripcion.textContent = formulario.querySelector('#tic_comentario_falla').value;
    
    // Manejar imagen si existe
    const inputImagen = formulario.querySelector('#tic_imagen');
    if (inputImagen && inputImagen.files && inputImagen.files[0] && imagenSection && ticketImagen) {
        const archivo = inputImagen.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            ticketImagen.src = e.target.result;
            imagenSection.style.display = 'block';
        };
        reader.readAsDataURL(archivo);
    } else if (imagenSection) {
        imagenSection.style.display = 'none';
    }
    
    // Mostrar modal
    modal.style.display = 'flex';
    
    // Agregar animación de entrada
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

// Cierra el modal personalizado
function cerrarModalTicket() {
    const modal = document.getElementById('modalTicket');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Hacer la función global para que pueda ser llamada desde el HTML
window.cerrarModalTicket = cerrarModalTicket;


// Validaciones específicas del formulario
function validacionesEspecificas(formulario) {
    const aplicacion = formulario.querySelector('#tic_app');
    const email = formulario.querySelector('#tic_correo_electronico');
    const comentario = formulario.querySelector('#tic_comentario_falla');
    
    // Validar aplicación
    if (!aplicacion || !aplicacion.value.trim()) {
        mostrarErrorCampo('tic_app', 'Debe seleccionar una aplicación');
        aplicacion.focus();
        Toast.fire({
            icon: 'error',
            title: 'Debe seleccionar una aplicación'
        });
        return false;
    }
    
    // Validar email
    if (!email || !email.value.trim()) {
        mostrarErrorCampo('tic_correo_electronico', 'El correo electrónico es obligatorio');
        email.focus();
        Toast.fire({
            icon: 'error',
            title: 'El correo electrónico es obligatorio'
        });
        return false;
    }
    
    if (!validarEmail(email.value)) {
        mostrarErrorCampo('tic_correo_electronico', 'Ingrese un correo electrónico válido');
        email.focus();
        Toast.fire({
            icon: 'error',
            title: 'Ingrese un correo electrónico válido'
        });
        return false;
    }
    
    // Validar longitud del comentario
    if (!comentario || !comentario.value.trim()) {
        mostrarErrorCampo('tic_comentario_falla', 'La descripción del problema es obligatoria');
        comentario.focus();
        Toast.fire({
            icon: 'error',
            title: 'La descripción del problema es obligatoria'
        });
        return false;
    }
    
    if (comentario.value.trim().length < 15) {
        mostrarErrorCampo('tic_comentario_falla', 'La descripción debe tener al menos 15 caracteres');
        comentario.focus();
        Toast.fire({
            icon: 'error',
            title: 'La descripción debe tener al menos 15 caracteres'
        });
        return false;
    }
    
    return true;
}

// Valida un campo individual
function validarCampo(evento) {
    const campo = evento.target;
    
    if (campo.hasAttribute('required') && !campo.value.trim()) {
        mostrarErrorCampo(campo.id, 'Este campo es obligatorio');
        return false;
    }
    
    // Validaciones específicas por tipo de campo
    switch (campo.id) {
        case 'tic_app':
            if (!campo.value.trim()) {
                mostrarErrorCampo(campo.id, 'Debe seleccionar una aplicación');
                return false;
            }
            break;
            
        case 'tic_correo_electronico':
            if (campo.value && !validarEmail(campo.value)) {
                mostrarErrorCampo(campo.id, 'Formato de correo inválido');
                return false;
            }
            break;
            
        case 'tic_comentario_falla':
            if (campo.value && campo.value.trim().length < 15) {
                mostrarErrorCampo(campo.id, 'Mínimo 15 caracteres');
                return false;
            }
            break;
    }
    
    // Si llega aquí, el campo es válido
    mostrarCampoValido(campo.id);
    return true;
}

// Limpia errores cuando el usuario empieza a escribir
function limpiarError(evento) {
    const campo = evento.target;
    campo.classList.remove('is-invalid');
    
    // Si tiene contenido, validar
    if (campo.value.trim()) {
        validarCampo(evento);
    }
}

// Muestra error en un campo específico
function mostrarErrorCampo(idCampo, mensaje) {
    const campo = document.getElementById(idCampo);
    if (!campo) return;
    
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');
    
    // Buscar o crear mensaje de error
    let feedbackError = campo.parentElement.querySelector('.invalid-feedback');
    if (!feedbackError) {
        feedbackError = document.createElement('div');
        feedbackError.className = 'invalid-feedback';
        campo.parentElement.appendChild(feedbackError);
    }
    feedbackError.textContent = mensaje;
}

// Muestra que un campo es válido
function mostrarCampoValido(idCampo) {
    const campo = document.getElementById(idCampo);
    if (!campo) return;
    
    campo.classList.remove('is-invalid');
    campo.classList.add('is-valid');
}

// Limpia todas las clases de validación
function limpiarClasesValidacion() {
    const formulario = document.getElementById('formTicket');
    const campos = formulario.querySelectorAll('input, select, textarea');
    campos.forEach(campo => {
        campo.classList.remove('is-valid', 'is-invalid');
    });
    
    // Limpiar mensajes de error
    const mensajesError = formulario.querySelectorAll('.invalid-feedback');
    mensajesError.forEach(mensaje => mensaje.remove());
}

// Limpia el formulario completamente
function limpiarFormulario() {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Se perderán todos los datos ingresados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((resultado) => {
        if (resultado.isConfirmed) {
            const formulario = document.getElementById('formTicket');
            formulario.reset();
            
            limpiarClasesValidacion();
            
            // Limpiar vista previa de imagen
            const contenedorVistaPrevia = document.getElementById('contenedorVistaPrevia');
            if (contenedorVistaPrevia) {
                contenedorVistaPrevia.classList.add('d-none');
            }
            
            // Recargar aplicaciones después de limpiar
            cargarAplicaciones();
            
            // Enfocar primer campo
            const primerCampo = formulario.querySelector('select');
            if (primerCampo) {
                primerCampo.focus();
            }
            
            Toast.fire({
                icon: 'success',
                title: 'Formulario limpiado'
            });
        }
    });
}

// Valida formato de email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Configurar contador de caracteres para textarea
function configurarContadorCaracteres() {
    const textarea = document.getElementById('tic_comentario_falla');
    const contador = document.getElementById('contadorCaracteres');
    
    if (!textarea || !contador) return;
    
    // Función para actualizar contador
    const actualizarContador = () => {
        const longitud = textarea.value.length;
        contador.textContent = longitud;
        
        if (longitud < 15) {
            contador.parentElement.className = 'text-danger';
        } else {
            contador.parentElement.className = 'text-muted';
        }
    };
    
    // Eventos
    textarea.addEventListener('input', actualizarContador);
    textarea.addEventListener('keyup', actualizarContador);
    
    // Inicializar
    actualizarContador();
}

// Mostrar vista previa de imagen seleccionada
function mostrarVistaPrevia(evento) {
    const archivo = evento.target.files[0];
    const contenedor = document.getElementById('contenedorVistaPrevia');
    const imagen = document.getElementById('vistaPrevia');
    
    if (!archivo || !contenedor || !imagen) return;
    
    // Validar que sea una imagen
    if (!archivo.type.startsWith('image/')) {
        Toast.fire({
            icon: 'error',
            title: 'Solo se permiten archivos de imagen'
        });
        evento.target.value = '';
        contenedor.classList.add('d-none');
        return;
    }
    
    // Validar tamaño (8MB)
    if (archivo.size > 8 * 1024 * 1024) {
        Toast.fire({
            icon: 'error',
            title: 'La imagen no puede ser mayor a 8MB'
        });
        evento.target.value = '';
        contenedor.classList.add('d-none');
        return;
    }
    
    // Mostrar vista previa
    const reader = new FileReader();
    reader.onload = function(e) {
        imagen.src = e.target.result;
        contenedor.classList.remove('d-none');
    };
    reader.readAsDataURL(archivo);
}