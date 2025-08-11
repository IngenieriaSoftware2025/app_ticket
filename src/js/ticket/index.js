import Swal from "sweetalert2";

const formularioTicket = document.getElementById('formTicket');
const botonGuardar = document.getElementById('BtnEnviar');
const botonLimpiar = document.getElementById('BtnLimpiar');
const selectAplicacion = document.getElementById('tic_app');
const inputImagen = document.getElementById('tic_imagen');

const cargarAplicaciones = async () => {
    try {
        const nombreApp = window.location.pathname.split('/')[1];
        // RUTA CORREGIDA - Cambio de 'obtenerAplicacionesAPI' a 'aplicaciones'
        const url = `/${nombreApp}/ticket/aplicaciones`;
        
        const respuesta = await fetch(url);
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP! estado: ${respuesta.status}`);
        }
        
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        if (codigo == 1) {
            selectAplicacion.innerHTML = '<option value="">Seleccione la aplicación con problemas...</option>';
            
            data.forEach(aplicacion => {
                const opcion = document.createElement('option');
                opcion.value = aplicacion.menu_codigo;
                opcion.textContent = aplicacion.menu_descr;
                selectAplicacion.appendChild(opcion);
            });
            
            console.log(`Se cargaron ${data.length} aplicaciones`);
        } else {
            await Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al cargar aplicaciones",
                text: mensaje || 'Error en la respuesta del servidor',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo cargar las aplicaciones. Verifique su conexión.",
            showConfirmButton: true,
        });
    }
}

const guardarTicket = async (e) => {
    e.preventDefault();
    botonGuardar.disabled = true;

    if (!validacionesEspecificas(formularioTicket)) {
        botonGuardar.disabled = false;
        return;
    }

    const textoOriginal = botonGuardar.innerHTML;
    botonGuardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando Ticket...';

    try {
        const nombreApp = window.location.pathname.split('/')[1];
        // RUTA CORREGIDA - Cambio de 'guardarAPI' a 'guardar'
        const url = `/${nombreApp}/ticket/guardar`;
        
        const respuesta = await fetch(url, {
            method: 'POST',
            body: new FormData(formularioTicket)
        });
        
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "Ticket Creado",
                text: mensaje,
                showConfirmButton: true,
            });

            mostrarModalTicket(data);
            limpiarFormularioCompleto();
        } else {
            await Swal.fire({
                position: "center",
                icon: "error",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.log(error);
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo conectar con el servidor",
            showConfirmButton: true,
        });
    }
    
    botonGuardar.innerHTML = textoOriginal;
    botonGuardar.disabled = false;
}

const mostrarModalTicket = (datosTicket) => {
    const modal = document.getElementById('modalTicket');
    const numeroTicket = document.getElementById('ticketNumero');
    const fechaTicket = document.getElementById('ticketFecha');
    const descripcionTicket = document.getElementById('ticketDescripcion');
    const seccionImagen = document.getElementById('imagenSection');
    const ticketCorreo = document.getElementById('ticketCorreo');
    
    if (!modal) {
        console.error('No se encontró el modal con id "modalTicket"');
        return;
    }
    
    // Llenar datos del ticket
    if (numeroTicket) numeroTicket.textContent = datosTicket.numero_ticket;
    if (fechaTicket) fechaTicket.textContent = new Date().toLocaleDateString('es-ES');
    if (descripcionTicket) descripcionTicket.textContent = formularioTicket.querySelector('#tic_comentario_falla').value;
    if (ticketCorreo) ticketCorreo.textContent = formularioTicket.querySelector('#tic_correo_electronico').value;
    
    // Manejar múltiples imágenes si existen
    const inputImagenFormulario = formularioTicket.querySelector('#tic_imagen');
    if (inputImagenFormulario && inputImagenFormulario.files && inputImagenFormulario.files.length > 0 && seccionImagen) {
        // Limpiar el contenedor de imágenes
        const contenedorImagen = seccionImagen.querySelector('.imagen-container');
        contenedorImagen.innerHTML = '';
        
        // Crear grid para múltiples imágenes
        const gridImagenes = document.createElement('div');
        gridImagenes.className = 'row g-2';
        
        // Mostrar todas las imágenes
        for (let i = 0; i < inputImagenFormulario.files.length; i++) {
            const archivo = inputImagenFormulario.files[i];
            const lector = new FileReader();
            
            lector.onload = function(e) {
                const divColumna = document.createElement('div');
                divColumna.className = inputImagenFormulario.files.length === 1 ? 'col-12' : 'col-md-6 col-sm-12';
                
                const contenedorImg = document.createElement('div');
                contenedorImg.className = 'position-relative text-center';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-fluid rounded ticket-imagen';
                img.style.maxHeight = '200px';
                img.style.maxWidth = '100%';
                img.style.objectFit = 'contain';
                img.alt = `Imagen ${i + 1}`;
                
                const etiqueta = document.createElement('span');
                etiqueta.className = 'position-absolute top-0 start-0 badge bg-primary m-1';
                etiqueta.textContent = `${i + 1}`;
                
                contenedorImg.appendChild(img);
                if (inputImagenFormulario.files.length > 1) {
                    contenedorImg.appendChild(etiqueta);
                }
                divColumna.appendChild(contenedorImg);
                gridImagenes.appendChild(divColumna);
            };
            lector.readAsDataURL(archivo);
        }
        
        contenedorImagen.appendChild(gridImagenes);
        seccionImagen.style.display = 'block';
        
        // Actualizar el título de la sección
        const tituloImagen = seccionImagen.querySelector('.info-section-title');
        if (tituloImagen) {
            tituloImagen.textContent = inputImagenFormulario.files.length === 1 ? 
                'Imagen Adjunta' : 
                `Imágenes Adjuntas (${inputImagenFormulario.files.length})`;
        }
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

const limpiarFormulario = async () => {
    const alertaConfirmarLimpiar = await Swal.fire({
        position: "center",
        icon: "warning",
        title: "¿Limpiar formulario?",
        text: 'Se perderán todos los datos ingresados',
        showConfirmButton: true,
        confirmButtonText: 'Sí, limpiar',
        confirmButtonColor: '#d33',
        cancelButtonText: 'No, cancelar',
        showCancelButton: true
    });

    if (alertaConfirmarLimpiar.isConfirmed) {
        limpiarFormularioCompleto();
        
        await Swal.fire({
            position: "center",
            icon: "success",
            title: "Formulario limpiado",
            showConfirmButton: true,
        });
    }
}

const limpiarFormularioCompleto = () => {
    formularioTicket.reset();
    
    // Limpiar clases de validación
    limpiarClasesValidacion();
    
    // Limpiar vista previa de imágenes
    const contenedorVistaPrevia = document.getElementById('contenedorVistaPrevia');
    const imagenesPreview = document.getElementById('imagenesPreview');
    if (contenedorVistaPrevia) {
        contenedorVistaPrevia.classList.add('d-none');
    }
    if (imagenesPreview) {
        imagenesPreview.innerHTML = '';
    }
    
    // Recargar aplicaciones después de limpiar
    cargarAplicaciones();
    
    // Enfocar primer campo
    const primerCampo = formularioTicket.querySelector('select');
    if (primerCampo) {
        primerCampo.focus();
    }
}

const mostrarVistaPrevia = (evento) => {
    const archivos = evento.target.files;
    const contenedor = document.getElementById('contenedorVistaPrevia');
    const imagenesPreview = document.getElementById('imagenesPreview');
    
    if (!archivos || archivos.length === 0 || !contenedor || !imagenesPreview) return;
    
    // Validar máximo de imágenes
    if (archivos.length > 5) {
        Swal.fire({
            position: "center",
            icon: "error",
            title: "No se pueden subir más de 5 imágenes",
            showConfirmButton: true,
        });
        evento.target.value = '';
        contenedor.classList.add('d-none');
        return;
    }
    
    // Limpiar vista previa anterior
    imagenesPreview.innerHTML = '';
    
    let imagenesValidas = 0;
    
    for (let i = 0; i < archivos.length; i++) {
        const archivo = archivos[i];
        
        // Validar que sea una imagen
        if (!archivo.type.startsWith('image/')) {
            Swal.fire({
                position: "center",
                icon: "error",
                title: `Solo se permiten archivos de imagen (${archivo.name})`,
                showConfirmButton: true,
            });
            evento.target.value = '';
            contenedor.classList.add('d-none');
            return;
        }
        
        // Validar tamaño (8MB por imagen)
        if (archivo.size > 8 * 1024 * 1024) {
            Swal.fire({
                position: "center",
                icon: "error",
                title: `La imagen no puede ser mayor a 8MB (${archivo.name})`,
                showConfirmButton: true,
            });
            evento.target.value = '';
            contenedor.classList.add('d-none');
            return;
        }
        
        // Mostrar vista previa
        const lector = new FileReader();
        lector.onload = function(e) {
            const divColumna = document.createElement('div');
            divColumna.className = 'col-md-4 col-sm-6';
            
            const contenedorImg = document.createElement('div');
            contenedorImg.className = 'position-relative';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-fluid rounded';
            img.style.maxHeight = '150px';
            img.style.width = '100%';
            img.style.objectFit = 'cover';
            img.alt = `Vista previa ${i + 1}`;
            
            const etiqueta = document.createElement('span');
            etiqueta.className = 'position-absolute top-0 start-0 badge bg-primary m-1';
            etiqueta.textContent = `${i + 1}`;
            
            contenedorImg.appendChild(img);
            contenedorImg.appendChild(etiqueta);
            divColumna.appendChild(contenedorImg);
            imagenesPreview.appendChild(divColumna);
            
            imagenesValidas++;
            if (imagenesValidas === archivos.length) {
                contenedor.classList.remove('d-none');
            }
        };
        lector.readAsDataURL(archivo);
    }
}

const validacionesEspecificas = (formulario) => {
    const aplicacion = formulario.querySelector('#tic_app');
    const correoElectronico = formulario.querySelector('#tic_correo_electronico');
    const comentario = formulario.querySelector('#tic_comentario_falla');
    
    // Validar aplicación
    if (!aplicacion || !aplicacion.value.trim()) {
        mostrarErrorCampo('tic_app', 'Debe seleccionar una aplicación');
        if (aplicacion) aplicacion.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Debe seleccionar una aplicación",
            showConfirmButton: true,
        });
        return false;
    }
    
    // Validar correo electrónico
    if (!correoElectronico || !correoElectronico.value.trim()) {
        mostrarErrorCampo('tic_correo_electronico', 'El correo electrónico es obligatorio');
        if (correoElectronico) correoElectronico.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "El correo electrónico es obligatorio",
            showConfirmButton: true,
        });
        return false;
    }
    
    if (!validarCorreoElectronico(correoElectronico.value)) {
        mostrarErrorCampo('tic_correo_electronico', 'Ingrese un correo electrónico válido');
        if (correoElectronico) correoElectronico.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Ingrese un correo electrónico válido",
            showConfirmButton: true,
        });
        return false;
    }
    
    // Validar longitud del comentario
    if (!comentario || !comentario.value.trim()) {
        mostrarErrorCampo('tic_comentario_falla', 'La descripción del problema es obligatoria');
        if (comentario) comentario.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "La descripción del problema es obligatoria",
            showConfirmButton: true,
        });
        return false;
    }
    
    if (comentario.value.trim().length < 15) {
        mostrarErrorCampo('tic_comentario_falla', 'La descripción debe tener al menos 15 caracteres');
        if (comentario) comentario.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "La descripción debe tener al menos 15 caracteres",
            showConfirmButton: true,
        });
        return false;
    }
    
    return true;
}

const validarCorreoElectronico = (correo) => {
    const expresionRegular = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return expresionRegular.test(correo);
}

const mostrarErrorCampo = (idCampo, mensaje) => {
    const campo = document.getElementById(idCampo);
    if (!campo) return;
    
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');
    
    // Buscar o crear mensaje de error
    let mensajeError = campo.parentElement.querySelector('.invalid-feedback');
    if (!mensajeError) {
        mensajeError = document.createElement('div');
        mensajeError.className = 'invalid-feedback';
        campo.parentElement.appendChild(mensajeError);
    }
    mensajeError.textContent = mensaje;
}

const limpiarClasesValidacion = () => {
    const formulario = document.getElementById('formTicket');
    const campos = formulario.querySelectorAll('input, select, textarea');
    campos.forEach(campo => {
        campo.classList.remove('is-valid', 'is-invalid');
    });
    
    // Limpiar mensajes de error
    const mensajesError = formulario.querySelectorAll('.invalid-feedback');
    mensajesError.forEach(mensaje => mensaje.remove());
}

// Hacer la función global para que pueda ser llamada desde el HTML
window.cerrarModalTicket = cerrarModalTicket;

// Cargar datos iniciales
cargarAplicaciones();

// Configurar contador de caracteres al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const areaTexto = document.getElementById('tic_comentario_falla');
    const contador = document.getElementById('contadorCaracteres');
    
    if (areaTexto && contador) {
        const actualizarContador = () => {
            const longitud = areaTexto.value.length;
            contador.textContent = longitud;
            
            if (longitud < 15) {
                contador.parentElement.className = 'text-danger';
            } else {
                contador.parentElement.className = 'text-muted';
            }
        };
        
        areaTexto.addEventListener('input', actualizarContador);
        areaTexto.addEventListener('keyup', actualizarContador);
        actualizarContador();
    }
});

// Eventos del formulario
formularioTicket.addEventListener('submit', guardarTicket);
if (botonLimpiar) {
    botonLimpiar.addEventListener('click', limpiarFormulario);
}

// Event listener para vista previa de imagen
if (inputImagen) {
    inputImagen.addEventListener('change', mostrarVistaPrevia);
}