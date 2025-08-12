import Swal from "sweetalert2";

const formulario_ticket = document.getElementById('formTicket');
const boton_guardar = document.getElementById('BtnEnviar');
const boton_limpiar = document.getElementById('BtnLimpiar');
const select_aplicacion = document.getElementById('tic_app');
const input_imagen = document.getElementById('tic_imagen');

const cargar_aplicaciones = async () => {
    try {
        const nombre_app = window.location.pathname.split('/')[1];
        const url = `/${nombre_app}/ticket/obtenerAplicacionesAPI`;
        
        const respuesta = await fetch(url);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        if (codigo == 1) {
            // Limpiar opciones existentes
            select_aplicacion.innerHTML = '<option value="">Seleccione la aplicación con problemas...</option>';
            
            // Agregar aplicaciones al select
            data.forEach(aplicacion => {
                const opcion = document.createElement('option');
                opcion.value = aplicacion.gma_codigo;
                opcion.textContent = aplicacion.gma_desc;
                select_aplicacion.appendChild(opcion);
            });
            
            console.log(`Se cargaron ${data.length} aplicaciones`);
        } else {
            console.error('Error al cargar aplicaciones:', mensaje);
        }
    } catch (error) {
        console.error('Error al cargar aplicaciones:', error);
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
        const url = `/${nombre_app}/ticket/guardarAPI`;
        
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
                title: "¡Ticket Creado!",
                text: mensaje,
                showConfirmButton: true,
            });

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

const validaciones_especificas = (formulario) => {
    const aplicacion = formulario.querySelector('#tic_app');
    const correo_electronico = formulario.querySelector('#tic_correo_electronico');
    const comentario = formulario.querySelector('#tic_comentario_falla');
    
    // Validar aplicación
    if (!aplicacion || !aplicacion.value.trim()) {
        mostrar_error_campo('tic_app', 'Debe seleccionar una aplicación');
        if (aplicacion) aplicacion.focus();
        return false;
    }
    
    // Validar correo electrónico
    if (!correo_electronico || !correo_electronico.value.trim()) {
        mostrar_error_campo('tic_correo_electronico', 'El correo electrónico es obligatorio');
        if (correo_electronico) correo_electronico.focus();
        return false;
    }
    
    if (!validar_correo_electronico(correo_electronico.value)) {
        mostrar_error_campo('tic_correo_electronico', 'Ingrese un correo electrónico válido');
        if (correo_electronico) correo_electronico.focus();
        return false;
    }
    
    // Validar longitud del comentario
    if (!comentario || !comentario.value.trim()) {
        mostrar_error_campo('tic_comentario_falla', 'La descripción del problema es obligatoria');
        if (comentario) comentario.focus();
        return false;
    }
    
    if (comentario.value.trim().length < 15) {
        mostrar_error_campo('tic_comentario_falla', 'La descripción debe tener al menos 15 caracteres');
        if (comentario) comentario.focus();
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
    if (!formulario_ticket) return;
    
    const campos = formulario_ticket.querySelectorAll('input, select, textarea');
    campos.forEach(campo => {
        campo.classList.remove('is-valid', 'is-invalid');
    });
    
    // Limpiar mensajes de error
    const mensajes_error = formulario_ticket.querySelectorAll('.invalid-feedback');
    mensajes_error.forEach(mensaje => mensaje.remove());
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
    
    // Enfocar primer campo
    if (select_aplicacion) {
        select_aplicacion.focus();
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

// Configurar contador de caracteres
const configurar_contador_caracteres = () => {
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
}

// Configurar eventos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, iniciando configuración...');
    
    // Configurar contador de caracteres
    configurar_contador_caracteres();
    
    // Configurar eventos del formulario
    if (formulario_ticket) {
        formulario_ticket.addEventListener('submit', guardar_ticket);
    }
    
    if (boton_limpiar) {
        boton_limpiar.addEventListener('click', limpiar_formulario);
    }

    // Event listener para vista previa de imagen
    if (input_imagen) {
        input_imagen.addEventListener('change', mostrar_vista_previa);
    }
    
    // Cargar aplicaciones
    cargar_aplicaciones();
});