import Swal from "sweetalert2";

// Variables globales
const formulario_ticket = document.getElementById('formTicket');
const boton_guardar = document.getElementById('BtnEnviar');
const boton_limpiar = document.getElementById('BtnLimpiar');
const input_imagen = document.getElementById('tic_imagen');

// Variables para el sistema de búsqueda de aplicaciones
const inputAplicacion = document.getElementById('tic_app_input');
const inputAplicacionId = document.getElementById('tic_app');
const contenedorAplicaciones = document.getElementById('contenedorAplicaciones');
let aplicaciones = []; // Array para almacenar todas las aplicaciones

// Función para cargar aplicaciones desde la API
const cargar_aplicaciones = async () => {
    try {
        const nombre_app = window.location.pathname.split('/')[1];
        const url = `/${nombre_app}/ticket/aplicaciones`;
        
        const respuesta = await fetch(url);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP ${respuesta.status}: ${respuesta.statusText}`);
        }
        
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        if (codigo == 1 && data && data.length > 0) {
            aplicaciones = data;
        } else {
            aplicaciones = [];
            
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Sin aplicaciones",
                text: "No se encontraron aplicaciones disponibles",
                showConfirmButton: true,
            });
        }
    } catch (error) {
        aplicaciones = [];
        
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error al cargar aplicaciones",
            text: "No se pudieron cargar las aplicaciones. Verifique su conexión.",
            showConfirmButton: true,
        });
    }
}

// Función para configurar la búsqueda de aplicaciones
const configurar_busqueda_aplicaciones = () => {
    if (!inputAplicacion || !contenedorAplicaciones) {
        return;
    }

    // Evento input para filtrar aplicaciones mientras se escribe
    inputAplicacion.addEventListener('input', () => {
        const texto = inputAplicacion.value.toLowerCase().trim();
        
        // Limpiar contenido anterior
        contenedorAplicaciones.innerHTML = '';
        inputAplicacionId.value = '';
        
        if (!texto) {
            ocultar_dropdown();
            return;
        }

        if (aplicaciones.length === 0) {
            ocultar_dropdown();
            return;
        }

        const coincidencias = aplicaciones.filter(app =>
            app.gma_desc.toLowerCase().includes(texto)
        ).slice(0, 10); // Solo los primeros 10 resultados

        if (coincidencias.length === 0) {
            // Mostrar mensaje de "no encontrado"
            const mensaje = document.createElement('div');
            mensaje.className = 'dropdown-item text-muted';
            mensaje.textContent = `No se encontraron aplicaciones con "${texto}"`;
            mensaje.style.pointerEvents = 'none';
            contenedorAplicaciones.appendChild(mensaje);
            mostrar_dropdown();
            return;
        }

        // Crear elementos del dropdown
        coincidencias.forEach((aplicacion, index) => {
            const opcion = document.createElement('div');
            opcion.className = 'dropdown-item';
            opcion.textContent = aplicacion.gma_desc;
            opcion.dataset.id = aplicacion.gma_codigo;
            opcion.dataset.descripcion = aplicacion.gma_desc;
            
            // Agregar evento click
            opcion.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                inputAplicacion.value = aplicacion.gma_desc;
                inputAplicacionId.value = aplicacion.gma_codigo;
                ocultar_dropdown();
                
                // Limpiar errores de validación
                inputAplicacion.classList.remove('is-invalid');
                inputAplicacion.classList.add('is-valid');
                const errorMsg = inputAplicacion.parentElement.querySelector('.invalid-feedback');
                if (errorMsg) errorMsg.textContent = '';
            });
            
            // Agregar hover effect adicional
            opcion.addEventListener('mouseenter', () => {
                // Remover highlight de otros elementos
                contenedorAplicaciones.querySelectorAll('.dropdown-item').forEach(item => {
                    item.style.backgroundColor = '';
                });
                // Highlight este elemento
                opcion.style.backgroundColor = '#e9ecef';
            });
            
            opcion.addEventListener('mouseleave', () => {
                opcion.style.backgroundColor = '';
            });
            
            contenedorAplicaciones.appendChild(opcion);
        });

        mostrar_dropdown();
    });

    // Mostrar dropdown al hacer foco (si hay aplicaciones cargadas)
    inputAplicacion.addEventListener('focus', () => {
        if (aplicaciones.length > 0 && inputAplicacion.value.trim()) {
            // Re-ejecutar la búsqueda para mostrar resultados
            inputAplicacion.dispatchEvent(new Event('input'));
        }
    });

    // Ocultar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
        const clickDentroInput = inputAplicacion.contains(e.target);
        const clickDentroDropdown = contenedorAplicaciones.contains(e.target);
        
        if (!clickDentroInput && !clickDentroDropdown) {
            ocultar_dropdown();
        }
    });

    // Manejar teclas de navegación
    inputAplicacion.addEventListener('keydown', (e) => {
        const items = contenedorAplicaciones.querySelectorAll('.dropdown-item:not(.text-muted)');
        
        if (items.length === 0) return;
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                // Implementar navegación con flechas si es necesario
                break;
            case 'ArrowUp':
                e.preventDefault();
                // Implementar navegación con flechas si es necesario
                break;
            case 'Enter':
                e.preventDefault();
                // Auto-seleccionar el primer elemento si solo hay uno
                if (items.length === 1) {
                    items[0].click();
                }
                break;
            case 'Escape':
                ocultar_dropdown();
                break;
        }
    });

    // Limpiar selección si el usuario borra el texto completamente
    inputAplicacion.addEventListener('blur', () => {
        setTimeout(() => {
            if (!inputAplicacion.value.trim()) {
                inputAplicacionId.value = '';
                inputAplicacion.classList.remove('is-valid', 'is-invalid');
            }
        }, 200);
    });
}

// Funciones auxiliares para mostrar/ocultar dropdown
const mostrar_dropdown = () => {
    if (contenedorAplicaciones) {
        contenedorAplicaciones.classList.remove('hide');
        contenedorAplicaciones.classList.add('show');
        contenedorAplicaciones.style.display = 'block';
    }
}

const ocultar_dropdown = () => {
    if (contenedorAplicaciones) {
        contenedorAplicaciones.classList.remove('show');
        contenedorAplicaciones.classList.add('hide');
        contenedorAplicaciones.style.display = 'none';
    }
}

// Función para guardar el ticket
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
                title: "¡Ticket Creado!",
                text: mensaje,
                showConfirmButton: true,
            });

            // Mostrar modal con detalles del ticket
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

// Función para mostrar el modal con detalles del ticket
const mostrar_modal_ticket = (datos_ticket) => {
    const modal = document.getElementById('modalTicket');
    const numero_ticket = document.getElementById('ticketNumero');
    const fecha_ticket = document.getElementById('ticketFecha');
    const descripcion_ticket = document.getElementById('ticketDescripcion');
    const correo_ticket = document.getElementById('ticketCorreo');
    const telefono_ticket = document.getElementById('ticketTelefono');
    const seccion_imagen = document.getElementById('imagenSection');
    
    if (!modal) {
        return;
    }
    
    // Llenar datos del ticket
    if (numero_ticket) numero_ticket.textContent = datos_ticket.numero_ticket;
    if (fecha_ticket) fecha_ticket.textContent = new Date().toLocaleDateString('es-ES');
    if (descripcion_ticket) descripcion_ticket.textContent = formulario_ticket.querySelector('#tic_comentario_falla').value;
    if (correo_ticket) correo_ticket.textContent = formulario_ticket.querySelector('#tic_correo_electronico').value;
    if (telefono_ticket) telefono_ticket.textContent = formulario_ticket.querySelector('#tic_telefono').value;
    
    // Manejar múltiples imágenes si existen
    const input_imagen_formulario = formulario_ticket.querySelector('#tic_imagen');
    if (input_imagen_formulario && input_imagen_formulario.files && input_imagen_formulario.files.length > 0 && seccion_imagen) {
        const contenedor_imagen = document.getElementById('ticketImagenContainer');
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

// Función para cerrar el modal
const cerrar_modal_ticket = () => {
    const modal = document.getElementById('modalTicket');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Función de validaciones específicas
const validaciones_especificas = (formulario) => {
    const aplicacion_id = document.getElementById('tic_app'); // Campo hidden
    const aplicacion_input = document.getElementById('tic_app_input'); // Campo visible
    const correo_electronico = formulario.querySelector('#tic_correo_electronico');
    const comentario = formulario.querySelector('#tic_comentario_falla');
    const telefono = formulario.querySelector('#tic_telefono');
    
    // Validar aplicación (ahora usando el sistema de búsqueda)
    if (!aplicacion_id || !aplicacion_id.value.trim()) {
        mostrar_error_campo('tic_app_input', 'Debe seleccionar una aplicación de la lista');
        if (aplicacion_input) aplicacion_input.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Debe seleccionar una aplicación",
            showConfirmButton: true,
        });
        return false;
    }
    
    // Validar teléfono
    if (telefono && telefono.value.trim()) {
        const valor_telefono = telefono.value.trim();
        
        if (!/^\d+$/.test(valor_telefono)) {
            mostrar_error_campo('tic_telefono', 'Solo se permiten números');
            telefono.focus();
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Teléfono inválido",
                text: "Solo se permiten números",
                showConfirmButton: true,
            });
            return false;
        }
        
        if (valor_telefono.length !== 8) {
            mostrar_error_campo('tic_telefono', 'Debe tener exactamente 8 números');
            telefono.focus();
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Teléfono inválido",
                text: "Debe tener exactamente 8 números",
                showConfirmButton: true,
            });
            return false;
        }
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

// Función para validar correo electrónico
const validar_correo_electronico = (correo) => {
    const expresion_regular = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return expresion_regular.test(correo);
}

// Función para mostrar errores en campos específicos
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

// Función para limpiar clases de validación
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

// Función para limpiar formulario
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

// Función para limpiar formulario completamente
const limpiar_formulario_completo = () => {
    formulario_ticket.reset();
    limpiar_clases_validacion();
    
    // Limpiar campos específicos del sistema de búsqueda
    if (inputAplicacion) inputAplicacion.value = '';
    if (inputAplicacionId) inputAplicacionId.value = '';
    if (contenedorAplicaciones) ocultar_dropdown();
    
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
    if (inputAplicacion) {
        inputAplicacion.focus();
    }
}

// Función para vista previa de imágenes
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

// Función para configurar contador de caracteres
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

// Hacer funciones globales para el modal
window.cerrarModalTicket = cerrar_modal_ticket;

// Configurar eventos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Cargar aplicaciones PRIMERO
    cargar_aplicaciones().then(() => {
        // Configurar búsqueda DESPUÉS de cargar aplicaciones
        configurar_busqueda_aplicaciones();
    });
});