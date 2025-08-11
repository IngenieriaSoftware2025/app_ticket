import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import { validarFormulario } from '../funciones';

const formularioTicket = document.getElementById('formTicket');
const botonGuardar = document.getElementById('BtnEnviar');
const botonLimpiar = document.getElementById('BtnLimpiar');
const selectAplicacion = document.getElementById('tic_app');
const inputImagen = document.getElementById('tic_imagen');

const cargarAplicaciones = async () => {
    const url = `/app_ticket/ticket/obtenerAplicacionesAPI`;
    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            selectAplicacion.innerHTML = '<option value="">Seleccione la aplicación con problemas...</option>';
            
            data.forEach(aplicacion => {
                const option = document.createElement('option');
                option.value = aplicacion.menu_codigo;
                option.textContent = aplicacion.menu_descr;
                selectAplicacion.appendChild(option);
            });
        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.log(error);
    }
}

const guardarTicket = async e => {
    e.preventDefault();
    botonGuardar.disabled = true;

    if (!validarFormulario(formularioTicket, [])) {
        Swal.fire({
            position: "center",
            icon: "info",
            title: "FORMULARIO INCOMPLETO",
            text: "Debe de validar todos los campos",
            showConfirmButton: true,
        });
        botonGuardar.disabled = false;
        return;
    }

    const textoOriginal = botonGuardar.innerHTML;
    botonGuardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando Ticket...';

    const body = new FormData(formularioTicket);
    const url = "/app_ticket/ticket/guardarAPI";
    const config = {
        method: 'POST',
        body
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        console.log(datos);
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "Exito",
                text: mensaje,
                showConfirmButton: true,
            });

            mostrarModalTicket(data);
            limpiarFormulario();
            cargarAplicaciones();
        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.log(error);
    }
    
    botonGuardar.innerHTML = textoOriginal;
    botonGuardar.disabled = false;
}

const mostrarModalTicket = (datosTicket) => {
    const modal = document.getElementById('modalTicket');
    const numeroTicket = document.getElementById('ticketNumero');
    const fechaTicket = document.getElementById('ticketFecha');
    const emailTicket = document.getElementById('ticketEmail');
    const descripcionTicket = document.getElementById('ticketDescripcion');
    const seccionImagen = document.getElementById('imagenSection');
    const imagenTicket = document.getElementById('ticketImagen');
    
    if (!modal) {
        console.error('No se encontró el modal con id "modalTicket"');
        return;
    }
    
    // Llenar datos del ticket
    if (numeroTicket) numeroTicket.textContent = datosTicket.numero_ticket;
    if (fechaTicket) fechaTicket.textContent = new Date().toLocaleDateString('es-ES');
    if (emailTicket) emailTicket.textContent = formularioTicket.querySelector('#tic_correo_electronico').value;
    if (descripcionTicket) descripcionTicket.textContent = formularioTicket.querySelector('#tic_comentario_falla').value;
    
    // Manejar imagen si existe
    const inputImagenFormulario = formularioTicket.querySelector('#tic_imagen');
    if (inputImagenFormulario && inputImagenFormulario.files && inputImagenFormulario.files[0] && seccionImagen && imagenTicket) {
        const archivo = inputImagenFormulario.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            imagenTicket.src = e.target.result;
            seccionImagen.style.display = 'block';
        };
        reader.readAsDataURL(archivo);
    } else if (seccionImagen) {
        seccionImagen.style.display = 'none';
    }
    
    // Mostrar modal
    modal.style.display = 'flex';
    
    // Agregar animación de entrada
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

const cerrarModalTicket = () => {
    const modal = document.getElementById('modalTicket');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

const limpiarFormulario = () => {
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
            formularioTicket.reset();
            
            // Limpiar vista previa de imagen
            const contenedorVistaPrevia = document.getElementById('contenedorVistaPrevia');
            if (contenedorVistaPrevia) {
                contenedorVistaPrevia.classList.add('d-none');
            }
            
            // Recargar aplicaciones después de limpiar
            cargarAplicaciones();
            
            // Enfocar primer campo
            const primerCampo = formularioTicket.querySelector('select');
            if (primerCampo) {
                primerCampo.focus();
            }
            
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Formulario limpiado",
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

const mostrarVistaPrevia = (evento) => {
    const archivo = evento.target.files[0];
    const contenedor = document.getElementById('contenedorVistaPrevia');
    const imagen = document.getElementById('vistaPrevia');
    
    if (!archivo || !contenedor || !imagen) return;
    
    // Validar que sea una imagen
    if (!archivo.type.startsWith('image/')) {
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Solo se permiten archivos de imagen",
            showConfirmButton: true,
        });
        evento.target.value = '';
        contenedor.classList.add('d-none');
        return;
    }
    
    // Validar tamaño (8MB)
    if (archivo.size > 8 * 1024 * 1024) {
        Swal.fire({
            position: "center",
            icon: "error",
            title: "La imagen no puede ser mayor a 8MB",
            showConfirmButton: true,
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

// Hacer la función global para que pueda ser llamada desde el HTML
window.cerrarModalTicket = cerrarModalTicket;

// Cargar datos iniciales
cargarAplicaciones();

// Eventos del formulario
formularioTicket.addEventListener('submit', guardarTicket);
botonLimpiar.addEventListener('click', limpiarFormulario);

// Event listener para vista previa de imagen
if (inputImagen) {
    inputImagen.addEventListener('change', mostrarVistaPrevia);
}