import { validarFormulario, Toast } from '../funciones.js';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formularioTicket');
    const btnEnviar = document.getElementById('btnEnviar');
    
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
    const btnLimpiar = document.querySelector('[onclick="limpiarFormulario()"]');
    if (btnLimpiar) {
        btnLimpiar.removeAttribute('onclick');
        btnLimpiar.addEventListener('click', limpiarFormulario);
    }
});

/**
 * Carga las aplicaciones desde el servidor
 */
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
    }
}

/**
 * Maneja el envío del formulario
 */
async function enviarFormulario(evento) {
    evento.preventDefault();
    
    const formulario = evento.target;
    const btnEnviar = document.getElementById('btnEnviar');
    
    // Validar formulario usando la función importada
    if (!validarFormulario(formulario)) {
        Toast.fire({
            icon: 'error',
            title: 'Por favor complete todos los campos requeridos'
        });
        return;
    }
    
    // Validaciones específicas adicionales
    if (!validacionesEspecificas(formulario)) {
        return;
    }
    
    // Cambiar estado del botón
    const textoOriginal = btnEnviar.innerHTML;
    btnEnviar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando Ticket...';
    btnEnviar.disabled = true;
    
    try {
        // Enviar formulario
        const respuesta = await fetch(formulario.action || window.location.href, {
            method: 'POST',
            body: new FormData(formulario)
        });
        
        if (respuesta.ok) {
            // Si la respuesta es exitosa, recargar la página para mostrar mensajes
            window.location.reload();
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
        
    } catch (error) {
        console.error('Error al enviar formulario:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al crear el ticket. Intente nuevamente.'
        });
    } finally {
        // Restaurar botón
        btnEnviar.innerHTML = textoOriginal;
        btnEnviar.disabled = false;
    }
}

/**
 * Validaciones específicas del formulario
 */
function validacionesEspecificas(formulario) {
    const aplicacion = formulario.querySelector('#tic_app');
    const catalogoUsuario = formulario.querySelector('#form_tic_usu');
    const email = formulario.querySelector('#tic_correo_electronico');
    const comentario = formulario.querySelector('#tic_comentario_falla');
    
    // Validar aplicación
    if (!aplicacion || !aplicacion.value.trim()) {
        mostrarErrorCampo('tic_app', 'Debe seleccionar una aplicación');
        return false;
    }
    
    // Validar catálogo de usuario
    if (catalogoUsuario && catalogoUsuario.value && !Number.isInteger(Number(catalogoUsuario.value))) {
        mostrarErrorCampo('form_tic_usu', 'El catálogo debe ser un número entero');
        return false;
    }
    
    // Validar longitud del comentario
    if (comentario && comentario.value && comentario.value.trim().length < 15) {
        mostrarErrorCampo('tic_comentario_falla', 'La descripción debe tener al menos 15 caracteres');
        return false;
    }
    
    // Validar formato de email
    if (email && email.value && !validarEmail(email.value)) {
        mostrarErrorCampo('tic_correo_electronico', 'Ingrese un correo electrónico válido');
        return false;
    }
    
    return true;
}

/**
 * Valida un campo individual
 */
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
            
        case 'form_tic_usu':
            if (campo.value && !Number.isInteger(Number(campo.value))) {
                mostrarErrorCampo(campo.id, 'Debe ser un número entero');
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

/**
 * Limpia errores cuando el usuario empieza a escribir
 */
function limpiarError(evento) {
    const campo = evento.target;
    campo.classList.remove('is-invalid');
    
    // Si tiene contenido, validar
    if (campo.value.trim()) {
        validarCampo(evento);
    }
}

/**
 * Muestra error en un campo específico
 */
function mostrarErrorCampo(idCampo, mensaje) {
    const campo = document.getElementById(idCampo);
    if (!campo) return;
    
    campo.classList.remove('is-valid');
    campo.classList.add('is-invalid');
    
    // Actualizar mensaje de error si existe
    const feedbackError = campo.parentElement.querySelector('.invalid-feedback');
    if (feedbackError) {
        feedbackError.textContent = mensaje;
    }
}

/**
 * Muestra que un campo es válido
 */
function mostrarCampoValido(idCampo) {
    const campo = document.getElementById(idCampo);
    if (!campo) return;
    
    campo.classList.remove('is-invalid');
    campo.classList.add('is-valid');
}

/**
 * Limpia el formulario completamente
 */
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
            const formulario = document.getElementById('formularioTicket');
            formulario.reset();
            
            // Limpiar clases de validación
            const campos = formulario.querySelectorAll('input, select, textarea');
            campos.forEach(campo => {
                campo.classList.remove('is-valid', 'is-invalid');
            });
            
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

/**
 * Valida formato de email
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Contador de caracteres para textarea
 */
function configurarContadorCaracteres() {
    const textarea = document.getElementById('tic_comentario_falla');
    if (!textarea) return;
    
    // Crear elemento contador
    const contador = document.createElement('div');
    contador.className = 'form-text text-end';
    contador.style.fontSize = '0.875rem';
    
    // Insertar después del textarea
    textarea.parentNode.insertBefore(contador, textarea.nextSibling);
    
    // Función para actualizar contador
    const actualizarContador = () => {
        const longitud = textarea.value.length;
        contador.textContent = `${longitud} caracteres`;
        
        if (longitud < 15) {
            contador.className = 'form-text text-end text-danger';
        } else {
            contador.className = 'form-text text-end text-muted';
        }
    };
    
    // Eventos
    textarea.addEventListener('input', actualizarContador);
    textarea.addEventListener('keyup', actualizarContador);
    
    // Inicializar
    actualizarContador();
}

// Configurar contador cuando se carga la página
document.addEventListener('DOMContentLoaded', configurarContadorCaracteres);