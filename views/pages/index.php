<?php
// Vista: views/tickets/index.php
?>

<style>
    .contenedor-tickets {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: calc(100vh - 120px);
        padding: 2rem 0;
        border-radius: 15px;
        margin: 1rem;
    }
    
    .tarjeta-ticket {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .encabezado-ticket {
        background: linear-gradient(45deg, #4e54c8, #8f94fb);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .icono-ticket {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .formulario-ticket {
        padding: 2rem;
    }
    
    .campo-obligatorio::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }
    
    .vista-previa-imagen {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
        padding: 1rem;
        margin-top: 1rem;
        display: none;
    }
    
    .boton-principal {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .boton-principal:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .tabla-tickets {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .encabezado-tabla {
        background: linear-gradient(45deg, #4e54c8, #8f94fb);
        color: white;
    }
    
    .badge-estado {
        font-size: 0.75rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
    
    .estado-creado { background-color: #17a2b8; }
    .estado-pendiente { background-color: #ffc107; color: #000; }
    .estado-asignado { background-color: #007bff; }
    .estado-resuelto { background-color: #28a745; }
    .estado-espera { background-color: #fd7e14; }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>

<div class="contenedor-tickets">
    <div class="container-fluid">
        
        <!-- ENCABEZADO PRINCIPAL -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="display-4 text-white fw-bold mb-2">
                    <i class="bi bi-headset"></i> Sistema de Tickets
                </h1>
                <p class="lead text-white-50">Gestión de Soporte Técnico y Atención al Usuario</p>
            </div>
        </div>

        <!-- FORMULARIO DE NUEVO TICKET -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="tarjeta-ticket">
                    <div class="encabezado-ticket">
                        <i class="bi bi-plus-circle icono-ticket"></i>
                        <h2 class="mb-0">Crear Nuevo Ticket de Soporte</h2>
                        <p class="mb-0">Complete la información de su solicitud</p>
                    </div>
                    
                    <form id="formularioNuevoTicket" class="formulario-ticket" enctype="multipart/form-data">
                        <div class="row">
                            <!-- USUARIO SOLICITANTE -->
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="usuarioSolicitante" class="form-label campo-obligatorio">Usuario Solicitante</label>
                                <select class="form-select" id="usuarioSolicitante" name="form_tic_usu" required>
                                    <option value="">Seleccione un usuario</option>
                                    <!-- Opciones se cargan dinámicamente -->
                                </select>
                                <div class="form-text">Seleccione el usuario que reporta el problema</div>
                            </div>

                            <!-- DEPENDENCIA -->
                            <div class="col-lg-6 col-md-6 mb-3">
                                <label for="dependenciaSolicitante" class="form-label campo-obligatorio">Dependencia</label>
                                <select class="form-select" id="dependenciaSolicitante" name="tic_dependencia" required>
                                    <option value="">Seleccione una dependencia</option>
                                    <!-- Opciones se cargan dinámicamente -->
                                </select>
                                <div class="form-text">Dependencia que reporta el problema</div>
                            </div>

                            <!-- CORREO ELECTRÓNICO -->
                            <div class="col-12 mb-3">
                                <label for="correoElectronico" class="form-label campo-obligatorio">Correo Electrónico de Contacto</label>
                                <input type="email" class="form-control" id="correoElectronico" name="tic_correo_electronico" 
                                       placeholder="usuario@institucion.gob.gt" maxlength="250" required>
                                <div class="form-text">Correo para recibir actualizaciones del ticket</div>
                            </div>

                            <!-- DESCRIPCIÓN DEL PROBLEMA -->
                            <div class="col-12 mb-3">
                                <label for="descripcionProblema" class="form-label campo-obligatorio">Descripción Detallada del Problema</label>
                                <textarea class="form-control" id="descripcionProblema" name="tic_comentario_falla" 
                                          rows="6" placeholder="Describa detalladamente el problema o solicitud:&#10;&#10;- ¿Qué estaba haciendo cuando ocurrió el problema?&#10;- ¿Qué aplicación o sistema está involucrado?&#10;- ¿Qué mensaje de error aparece (si aplica)?&#10;- ¿Cuándo comenzó el problema?&#10;- ¿Otros detalles relevantes?" 
                                          maxlength="2000" required></textarea>
                                <div class="form-text">
                                    <span id="contadorCaracteres">0</span>/2000 caracteres (mínimo 15 caracteres)
                                </div>
                            </div>

                            <!-- IMAGEN ADJUNTA -->
                            <div class="col-12 mb-4">
                                <label for="imagenProblema" class="form-label">
                                    <i class="bi bi-image me-2"></i>Imagen del Problema (Opcional)
                                </label>
                                <input type="file" class="form-control" id="imagenProblema" name="tic_imagen" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <div class="form-text">
                                    Adjunte una captura de pantalla o foto que ayude a explicar el problema.
                                    <br><strong>Formatos:</strong> JPG, PNG, GIF, WEBP. <strong>Tamaño máximo:</strong> 8MB
                                </div>
                                <img id="vistaPrevia" class="vista-previa-imagen" alt="Vista previa de la imagen">
                            </div>

                            <!-- BOTONES -->
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-outline-secondary btn-lg me-3" id="botonLimpiarFormulario">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Limpiar Formulario
                                </button>
                                <button type="submit" class="boton-principal btn-lg" id="botonCrearTicket">
                                    <i class="bi bi-send me-2"></i>Crear Ticket de Soporte
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TABLA DE TICKETS EXISTENTES -->
        <div class="row">
            <div class="col-12">
                <div class="tabla-tickets">
                    <div class="encabezado-tabla p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">
                                    <i class="bi bi-list-task me-2"></i>Tickets Registrados en el Sistema
                                </h3>
                                <small class="opacity-75">Gestión y seguimiento de solicitudes de soporte</small>
                            </div>
                            <button class="btn btn-light btn-lg" id="botonActualizarTickets">
                                <i class="bi bi-arrow-clockwise me-2"></i>Actualizar Lista
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-bold">
                                        <i class="bi bi-hash me-1"></i>Número
                                    </th>
                                    <th class="fw-bold">
                                        <i class="bi bi-person me-1"></i>Solicitante
                                    </th>
                                    <th class="fw-bold">
                                        <i class="bi bi-building me-1"></i>Dependencia
                                    </th>
                                    <th class="fw-bold">
                                        <i class="bi bi-exclamation-circle me-1"></i>Problema
                                    </th>
                                    <th class="fw-bold">
                                        <i class="bi bi-flag me-1"></i>Estado
                                    </th>
                                    <th class="fw-bold">
                                        <i class="bi bi-person-check me-1"></i>Encargado
                                    </th>
                                    <th class="fw-bold">
                                        <i class="bi bi-calendar me-1"></i>Fecha
                                    </th>
                                    <th class="fw-bold text-center">
                                        <i class="bi bi-gear me-1"></i>Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaTickets">
                                <!-- Los tickets se cargarán aquí dinámicamente -->
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-3 mb-0 text-muted">Cargando tickets del sistema...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
