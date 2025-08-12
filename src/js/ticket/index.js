import Swal from "sweetalert2";

const formulario_ticket = document.getElementById('formTicket');
const boton_guardar = document.getElementById('BtnEnviar');
const boton_limpiar = document.getElementById('BtnLimpiar');
const select_aplicacion = document.getElementById('tic_app');
const input_imagen = document.getElementById('tic_imagen');

const cargar_aplicaciones = async () => {
    try {
        const nombre_app = window.location.pathname.split('/')[1];
        // RUTA CORREGIDA - Usando la ruta del controlador
        const url = `/${nombre_app}/ticket/aplicaciones`;
        
        const respuesta = await fetch(url);
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP! estado: ${respuesta.status}`);
        }
        
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        if (codigo == 1) {
            select_aplicacion.innerHTML = '<option value="">Seleccione la aplicación con problemas...</option>';
            
            // CAMBIO PRINCIPAL: Usar gma_codigo y gma_desc en lugar de menu_codigo y menu_descr
            data.forEach(aplicacion => {
                const opcion = document.createElement('option');
                opcion.value = aplicacion.gma_codigo;  // Cambio aquí
                opcion.textContent = aplicacion.gma_desc; // Cambio aquí
                select_aplicacion.appendChild(opcion);
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

const guardar_ticket = async (e) => {
    e.preventDefault();
    boton_guardar.disabled = true;

    if (!validaciones_especificas(formulario_ticket)) {
        boton_guardar.disabled = false;
        return;
    }

    const texto_original = boton_guardar.innerHTML;
    boton_guardar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando Ticket...';

    try {
        const nombre_app = window.location.pathname.split('/')[1];
        // RUTA CORREGIDA - Usando la ruta del controlador
        const url = `/${nombre_app}/ticket/guardar`;
        
        const respuesta = await fetch(url, {
            method: 'POST',
            body: new FormData(formulario_ticket)
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

            mostrar_modal_ticket(data);
            limpiar_formulario_completo();
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
    
    boton_guardar.innerHTML = texto_original;
    boton_guardar.disabled = false;
}

const mostrar_modal_ticket = (datos_ticket) => {
    const modal = document.getElementById('modalTicket');
    const numero_ticket = document.getElementById('ticketNumero');
    const fecha_ticket = document.getElementById('ticketFecha');
    const descripcion_ticket = document.getElementById('ticketDescripcion');
    const seccion_imagen = document.getElementById('imagenSection');
    const ticket_correo = document.getElementById('ticketCorreo');
    
    if (!modal) {
        console.error('No se encontró el modal con id "modalTicket"');
        return;
    }
    
    // Llenar datos del ticket
    if (numero_ticket) numero_ticket.textContent = datos_ticket.numero_ticket;
    if (fecha_ticket) fecha_ticket.textContent = new Date().toLocaleDateString('es-ES');
    if (descripcion_ticket) descripcion_ticket.textContent = formulario_ticket.querySelector('#tic_comentario_falla').value;
    if (ticket_correo) ticket_correo.textContent = formulario_ticket.querySelector('#tic_correo_electronico').value;
    
    // Manejar múltiples imágenes si existen
    const input_imagen_formulario = formulario_ticket.querySelector('#tic_imagen');
    if (input_imagen_formulario && input_imagen_formulario.files && input_imagen_formulario.files.length > 0 && seccion_imagen) {
        // Limpiar el contenedor de imágenes
        const contenedor_imagen = seccion_imagen.querySelector('.imagen-container');
        contenedor_imagen.innerHTML = '';
        
        // Crear grid para múltiples imágenes
        const grid_imagenes = document.createElement('div');
        grid_imagenes.className = 'row g-2';
        
        // Mostrar todas las imágenes
        for (let i = 0; i < input_imagen_formulario.files.length; i++) {
            const archivo = input_imagen_formulario.files[i];
            const lector = new FileReader();
            
            lector.onload = function(e) {
                const div_columna = document.createElement('div');
                div_columna.className = input_imagen_formulario.files.length === 1 ? 'col-12' : 'col-md-6 col-sm-12';
                
                const contenedor_img = document.createElement('div');
                contenedor_img.className = 'position-relative text-center';
                
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
                
                contenedor_img.appendChild(img);
                if (input_imagen_formulario.files.length > 1) {
                    contenedor_img.appendChild(etiqueta);
                }
                div_columna.appendChild(contenedor_img);
                grid_imagenes.appendChild(div_columna);
            };
            lector.readAsDataURL(archivo);
        }
        
        contenedor_imagen.appendChild(grid_imagenes);
        seccion_imagen.style.display = 'block';
        
        // Actualizar el título de la sección
        const titulo_imagen = seccion_imagen.querySelector('.info-section-title');
        if (titulo_imagen) {
            titulo_imagen.textContent = input_imagen_formulario.files.length === 1 ? 
                'Imagen Adjunta' : 
                `Imágenes Adjuntas (${input_imagen_formulario.files.length})`;
        }
    } else if (seccion_imagen) {
        seccion_imagen.style.display = 'none';
    }
    
    // Mostrar modal
    modal.style.display = 'flex';
    
    // Agregar animación de entrada
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

const cerrar_modal_ticket = () => {
    const modal = document.getElementById('modalTicket');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

const limpiar_formulario = async () => {
    const alerta_confirmar_limpiar = await Swal.fire({
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

    if (alerta_confirmar_limpiar.isConfirmed) {
        limpiar_formulario_completo();
        
        await Swal.fire({
            position: "center",
            icon: "success",
            title: "Formulario limpiado",
            showConfirmButton: true,
        });
    }
}

const limpiar_formulario_completo = () => {
    formulario_ticket.reset();
    
    // Limpiar clases de validación
    limpiar_clases_validacion();
    
    // Limpiar vista previa de imágenes
    const contenedor_vista_previa = document.getElementById('contenedorVistaPrevia');
    const imagenes_preview = document.getElementById('imagenesPreview');
    if (contenedor_vista_previa) {
        contenedor_vista_previa.classList.add('d-none');
    }
    if (imagenes_preview) {
        imagenes_preview.innerHTML = '';
    }
    
    // Recargar aplicaciones después de limpiar
    cargar_aplicaciones();
    
    // Enfocar primer campo
    const primer_campo = formulario_ticket.querySelector('select');
    if (primer_campo) {
        primer_campo.focus();
    }
}

const mostrar_vista_previa = (evento) => {
    const archivos = evento.target.files;
    const contenedor = document.getElementById('contenedorVistaPrevia');
    const imagenes_preview = document.getElementById('imagenesPreview');
    
    if (!archivos || archivos.length === 0 || !contenedor || !imagenes_preview) return;
    
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
    imagenes_preview.innerHTML = '';
    
    let imagenes_validas = 0;
    
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
            const div_columna = document.createElement('div');
            div_columna.className = 'col-md-4 col-sm-6';
            
            const contenedor_img = document.createElement('div');
            contenedor_img.className = 'position-relative';
            
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
            
            contenedor_img.appendChild(img);
            contenedor_img.appendChild(etiqueta);
            div_columna.appendChild(contenedor_img);
            imagenes_preview.appendChild(div_columna);
            
            imagenes_validas++;
            if (imagenes_validas === archivos.length) {
                contenedor.classList.remove('d-none');
            }
        };
        lector.readAsDataURL(archivo);
    }
}

const validaciones_especificas = (formulario) => {
    const aplicacion = formulario.querySelector('#tic_app');
    const correo_electronico = formulario.querySelector('#tic_correo_electronico');
    const comentario = formulario.querySelector('#tic_comentario_falla');
    
    // Validar aplicación
    if (!aplicacion || !aplicacion.value.trim()) {
        mostrar_error_campo('tic_app', 'Debe seleccionar una aplicación');
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
    if (!correo_electronico || !correo_electronico.value.trim()) {
        mostrar_error_campo('tic_correo_electronico', 'El correo electrónico es obligatorio');
        if (correo_electronico) correo_electronico.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "El correo electrónico es obligatorio",
            showConfirmButton: true,
        });
        return false;
    }
    
    if (!validar_correo_electronico(correo_electronico.value)) {
        mostrar_error_campo('tic_correo_electronico', 'Ingrese un correo electrónico válido');
        if (correo_electronico) correo_electronico.focus();
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
        mostrar_error_campo('tic_comentario_falla', 'La descripción del problema es obligatoria');
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
        mostrar_error_campo('tic_comentario_falla', 'La descripción debe tener al menos 15 caracteres');
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

const validar_correo_electronico = (correo) => {
    const expresion_regular = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return expresion_regular.test(correo);
}

const mostrar_error_campo = (id_campo, mensaje) => {
    const campo = document.getElementById(id_campo);
    if (!campo) return;
    
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');
    
    // Buscar o crear mensaje de error
    let mensaje_error = campo.parentElement.querySelector('.invalid-feedback');
    if (!mensaje_error) {
        mensaje_error = document.createElement('div');
        mensaje_error.className = 'invalid-feedback';
        campo.parentElement.appendChild(mensaje_error);
    }
    mensaje_error.textContent = mensaje;
}

const limpiar_clases_validacion = () => {
    const formulario = document.getElementById('formTicket');
    const campos = formulario.querySelectorAll('input, select, textarea');
    campos.forEach(campo => {
        campo.classList.remove('is-valid', 'is-invalid');
    });
    
    // Limpiar mensajes de error
    const mensajes_error = formulario.querySelectorAll('.invalid-feedback');
    mensajes_error.forEach(mensaje => mensaje.remove());
}

// Hacer la función global para que pueda ser llamada desde el HTML
window.cerrarModalTicket = cerrar_modal_ticket;

// Cargar datos iniciales
cargar_aplicaciones();

// Configurar contador de caracteres al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const area_texto = document.getElementById('tic_comentario_falla');
    const contador = document.getElementById('contadorCaracteres');
    
    if (area_texto && contador) {
        const actualizar_contador = () => {
            const longitud = area_texto.value.length;
            contador.textContent = longitud;
            
            if (longitud < 15) {
                contador.parentElement.className = 'text-danger';
            } else {
                contador.parentElement.className = 'text-muted';
            }
        };
        
        area_texto.addEventListener('input', actualizar_contador);
        area_texto.addEventListener('keyup', actualizar_contador);
        actualizar_contador();
    }
});

// Eventos del formulario
formulario_ticket.addEventListener('submit', guardar_ticket);
if (boton_limpiar) {
    boton_limpiar.addEventListener('click', limpiar_formulario);
}

// Event listener para vista previa de imagen
if (input_imagen) {
    input_imagen.addEventListener('change', mostrar_vista_previa);
}