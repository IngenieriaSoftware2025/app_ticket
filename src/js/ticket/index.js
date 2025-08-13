import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import { validarFormulario } from '../funciones';
import { lenguaje } from "../lenguaje";

const FormularioTicket = document.getElementById('formTicket');
const BtnEnviar = document.getElementById('BtnEnviar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const InputImagen = document.getElementById('tic_imagen');
const InputAplicacion = document.getElementById('tic_app_input');
const InputAplicacionId = document.getElementById('tic_app');
const ContenedorAplicaciones = document.getElementById('contenedorAplicaciones');
const ContadorCaracteres = document.getElementById('contadorCaracteres');
const AreaTextoComentario = document.getElementById('tic_comentario_falla');

// Variables globales
let aplicaciones = [];

// Función para cargar aplicaciones
const CargarAplicaciones = async () => {
    try {
        const nombreApp = window.location.pathname.split('/')[1];
        const url = `/${nombreApp}/ticket/aplicaciones`;
        const config = {
            method: 'GET'
        };
        
        const respuesta = await fetch(url, config);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP ${respuesta.status}: ${respuesta.statusText}`);
        }
        
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        if (codigo == 1 && data && data.length > 0) {
            aplicaciones = data;
        } else {
            aplicaciones = [];
            
            await Swal.fire({
                position: "center",
                icon: "warning",
                title: "Sin aplicaciones",
                text: "No se encontraron aplicaciones disponibles",
                showConfirmButton: true,
            });
        }
    } catch (error) {
        aplicaciones = [];
        
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error al cargar aplicaciones",
            text: "No se pudieron cargar las aplicaciones. Verifique su conexión.",
            showConfirmButton: true,
        });
    }
};

// Función para guardar el ticket
const GuardarTicket = async (event) => {
    event.preventDefault();
    BtnEnviar.disabled = true;

    if (!ValidacionesEspecificas(FormularioTicket)) {
        BtnEnviar.disabled = false;
        return;
    }

    const textoOriginal = BtnEnviar.innerHTML;
    BtnEnviar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando Ticket...';

    try {
        const nombreApp = window.location.pathname.split('/')[1];
        const url = `/${nombreApp}/ticket/guardar`;
        const body = new FormData(FormularioTicket);
        const config = {
            method: 'POST',
            body
        };
        
        const respuesta = await fetch(url, config);
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

            MostrarModalTicket(data);
            LimpiarFormularioCompleto();
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
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo conectar con el servidor",
            showConfirmButton: true,
        });
    }
    
    BtnEnviar.innerHTML = textoOriginal;
    BtnEnviar.disabled = false;
};

// Función para mostrar el modal de los detalles del ticket
const MostrarModalTicket = (datosTicket) => {
    const modal = document.getElementById('modalTicket');
    const numeroTicket = document.getElementById('ticketNumero');
    const fechaTicket = document.getElementById('ticketFecha');
    const descripcionTicket = document.getElementById('ticketDescripcion');
    const correoTicket = document.getElementById('ticketCorreo');
    const telefonoTicket = document.getElementById('ticketTelefono');
    const seccionImagen = document.getElementById('imagenSection');
    
    if (!modal) return;
    
    // Llenar datos del ticket
    if (numeroTicket) numeroTicket.textContent = datosTicket.numero_ticket;
    if (fechaTicket) fechaTicket.textContent = new Date().toLocaleDateString('es-ES');
    if (descripcionTicket) descripcionTicket.textContent = FormularioTicket.querySelector('#tic_comentario_falla').value;
    if (correoTicket) correoTicket.textContent = FormularioTicket.querySelector('#tic_correo_electronico').value;
    if (telefonoTicket) telefonoTicket.textContent = FormularioTicket.querySelector('#tic_telefono').value;
    
    // Manejar múltiples imágenes si existen
    const inputImagenFormulario = FormularioTicket.querySelector('#tic_imagen');
    if (inputImagenFormulario && inputImagenFormulario.files && inputImagenFormulario.files.length > 0 && seccionImagen) {
        const contenedorImagen = document.getElementById('ticketImagenContainer');
        contenedorImagen.innerHTML = '';
        
        const gridImagenes = document.createElement('div');
        gridImagenes.className = 'row g-2';
        
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
    } else if (seccionImagen) {
        seccionImagen.style.display = 'none';
    }
    
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
};

// Función para cerrar el modal
const CerrarModalTicket = () => {
    const modal = document.getElementById('modalTicket');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
};

// Función de validaciones específicas
const ValidacionesEspecificas = (formulario) => {
    const aplicacionId = document.getElementById('tic_app');
    const aplicacionInput = document.getElementById('tic_app_input');
    const correoElectronico = formulario.querySelector('#tic_correo_electronico');
    const comentario = formulario.querySelector('#tic_comentario_falla');
    const telefono = formulario.querySelector('#tic_telefono');
    
    // Validar aplicación
    if (!aplicacionId || !aplicacionId.value.trim()) {
        MostrarErrorCampo('tic_app_input', 'Debe seleccionar una aplicación de la lista');
        if (aplicacionInput) aplicacionInput.focus();
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
        const valorTelefono = telefono.value.trim();
        
        if (!/^\d+$/.test(valorTelefono)) {
            MostrarErrorCampo('tic_telefono', 'Solo se permiten números');
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
        
        if (valorTelefono.length !== 8) {
            MostrarErrorCampo('tic_telefono', 'Debe tener exactamente 8 números');
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
    if (!correoElectronico || !correoElectronico.value.trim()) {
        MostrarErrorCampo('tic_correo_electronico', 'El correo electrónico es obligatorio');
        if (correoElectronico) correoElectronico.focus();
        Swal.fire({
            position: "center",
            icon: "error",
            title: "El correo electrónico es obligatorio",
            showConfirmButton: true,
        });
        return false;
    }
    
    if (!ValidarCorreoElectronico(correoElectronico.value)) {
        MostrarErrorCampo('tic_correo_electronico', 'Ingrese un correo electrónico válido');
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
        MostrarErrorCampo('tic_comentario_falla', 'La descripción del problema es obligatoria');
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
        MostrarErrorCampo('tic_comentario_falla', 'La descripción debe tener al menos 15 caracteres');
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
};

// Función para validar correo electrónico
const ValidarCorreoElectronico = (correo) => {
    const expresionRegular = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return expresionRegular.test(correo);
};

// Función para mostrar errores en campos específicos
const MostrarErrorCampo = (idCampo, mensaje) => {
    const campo = document.getElementById(idCampo);
    if (!campo) return;
    
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');
    
    let mensajeError = campo.parentElement.querySelector('.invalid-feedback');
    if (!mensajeError) {
        mensajeError = document.createElement('div');
        mensajeError.className = 'invalid-feedback';
        campo.parentElement.appendChild(mensajeError);
    }
    mensajeError.textContent = mensaje;
};

// Función para limpiar formulario
const LimpiarFormulario = async () => {
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
        LimpiarFormularioCompleto();
        
        await Swal.fire({
            position: "center",
            icon: "success",
            title: "Formulario limpiado",
            showConfirmButton: true,
        });
    }
};

// Función para limpiar formulario completamente
const LimpiarFormularioCompleto = () => {
    FormularioTicket.reset();
    LimpiarClasesValidacion();
    
    if (InputAplicacion) InputAplicacion.value = '';
    if (InputAplicacionId) InputAplicacionId.value = '';
    if (ContenedorAplicaciones) OcultarDropdown();
    
    const contenedorVistaPrevia = document.getElementById('contenedorVistaPrevia');
    const imagenesPreview = document.getElementById('imagenesPreview');
    if (contenedorVistaPrevia) {
        contenedorVistaPrevia.classList.add('d-none');
    }
    if (imagenesPreview) {
        imagenesPreview.innerHTML = '';
    }
    
    if (InputAplicacion) {
        InputAplicacion.focus();
    }
};

// Función para limpiar clases de validación
const LimpiarClasesValidacion = () => {
    if (!FormularioTicket) return;
    
    const campos = FormularioTicket.querySelectorAll('input, select, textarea');
    campos.forEach(campo => {
        campo.classList.remove('is-valid', 'is-invalid');
    });
    
    const mensajesError = FormularioTicket.querySelectorAll('.invalid-feedback');
    mensajesError.forEach(mensaje => mensaje.remove());
};

// Función para configurar la búsqueda de aplicaciones
const ConfigurarBusquedaAplicaciones = () => {
    if (!InputAplicacion || !ContenedorAplicaciones) return;

    InputAplicacion.addEventListener('input', () => {
        const texto = InputAplicacion.value.toLowerCase().trim();
        
        ContenedorAplicaciones.innerHTML = '';
        InputAplicacionId.value = '';
        
        if (!texto) {
            OcultarDropdown();
            return;
        }

        if (aplicaciones.length === 0) {
            OcultarDropdown();
            return;
        }

        const coincidencias = aplicaciones.filter(app =>
            app.gma_desc.toLowerCase().includes(texto)
        ).slice(0, 10);

        if (coincidencias.length === 0) {
            const mensaje = document.createElement('div');
            mensaje.className = 'dropdown-item text-muted';
            mensaje.textContent = `No se encontraron aplicaciones con "${texto}"`;
            mensaje.style.pointerEvents = 'none';
            ContenedorAplicaciones.appendChild(mensaje);
            MostrarDropdown();
            return;
        }

        coincidencias.forEach((aplicacion) => {
            const opcion = document.createElement('div');
            opcion.className = 'dropdown-item';
            opcion.textContent = aplicacion.gma_desc;
            opcion.dataset.id = aplicacion.gma_codigo;
            opcion.dataset.descripcion = aplicacion.gma_desc;
            
            opcion.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                InputAplicacion.value = aplicacion.gma_desc;
                InputAplicacionId.value = aplicacion.gma_codigo;
                OcultarDropdown();
                
                InputAplicacion.classList.remove('is-invalid');
                InputAplicacion.classList.add('is-valid');
                const errorMsg = InputAplicacion.parentElement.querySelector('.invalid-feedback');
                if (errorMsg) errorMsg.textContent = '';
            });
            
            opcion.addEventListener('mouseenter', () => {
                ContenedorAplicaciones.querySelectorAll('.dropdown-item').forEach(item => {
                    item.style.backgroundColor = '';
                });
                opcion.style.backgroundColor = '#e9ecef';
            });
            
            opcion.addEventListener('mouseleave', () => {
                opcion.style.backgroundColor = '';
            });
            
            ContenedorAplicaciones.appendChild(opcion);
        });

        MostrarDropdown();
    });

    InputAplicacion.addEventListener('focus', () => {
        if (aplicaciones.length > 0 && InputAplicacion.value.trim()) {
            InputAplicacion.dispatchEvent(new Event('input'));
        }
    });

    document.addEventListener('click', (e) => {
        const clickDentroInput = InputAplicacion.contains(e.target);
        const clickDentroDropdown = ContenedorAplicaciones.contains(e.target);
        
        if (!clickDentroInput && !clickDentroDropdown) {
            OcultarDropdown();
        }
    });

    InputAplicacion.addEventListener('keydown', (e) => {
        const items = ContenedorAplicaciones.querySelectorAll('.dropdown-item:not(.text-muted)');
        
        if (items.length === 0) return;
        
        switch(e.key) {
            case 'Enter':
                e.preventDefault();
                if (items.length === 1) {
                    items[0].click();
                }
                break;
            case 'Escape':
                OcultarDropdown();
                break;
        }
    });

    InputAplicacion.addEventListener('blur', () => {
        setTimeout(() => {
            if (!InputAplicacion.value.trim()) {
                InputAplicacionId.value = '';
                InputAplicacion.classList.remove('is-valid', 'is-invalid');
            }
        }, 200);
    });
};

// Funciones auxiliares para mostrar y ocultar dropdown
const MostrarDropdown = () => {
    if (ContenedorAplicaciones) {
        ContenedorAplicaciones.classList.remove('hide');
        ContenedorAplicaciones.classList.add('show');
        ContenedorAplicaciones.style.display = 'block';
    }
};

const OcultarDropdown = () => {
    if (ContenedorAplicaciones) {
        ContenedorAplicaciones.classList.remove('show');
        ContenedorAplicaciones.classList.add('hide');
        ContenedorAplicaciones.style.display = 'none';
    }
};

// Función para vista previa de imágenes
const MostrarVistaPrevia = (evento) => {
    const archivos = evento.target.files;
    const contenedor = document.getElementById('contenedorVistaPrevia');
    const imagenesPreview = document.getElementById('imagenesPreview');
    
    if (!archivos || archivos.length === 0 || !contenedor || !imagenesPreview) return;
    
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
    
    imagenesPreview.innerHTML = '';
    let imagenesValidas = 0;
    
    for (let i = 0; i < archivos.length; i++) {
        const archivo = archivos[i];
        
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
};

// Función para configurar contador de caracteres
const ConfigurarContadorCaracteres = () => {
    if (AreaTextoComentario && ContadorCaracteres) {
        const actualizarContador = () => {
            const longitud = AreaTextoComentario.value.length;
            ContadorCaracteres.textContent = longitud;
            
            if (longitud < 15) {
                ContadorCaracteres.parentElement.className = 'text-danger';
            } else {
                ContadorCaracteres.parentElement.className = 'text-muted';
            }
        };
        
        AreaTextoComentario.addEventListener('input', actualizarContador);
        AreaTextoComentario.addEventListener('keyup', actualizarContador);
        actualizarContador();
    }
};

// Hacer funciones globales para el modal
window.cerrarModalTicket = CerrarModalTicket;

// Event Listeners
FormularioTicket.addEventListener('submit', GuardarTicket);
BtnLimpiar.addEventListener('click', LimpiarFormulario);
InputImagen.addEventListener('change', MostrarVistaPrevia);

// Inicialización
ConfigurarContadorCaracteres();
CargarAplicaciones().then(() => {
    ConfigurarBusquedaAplicaciones();
});