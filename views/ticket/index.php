<style>
.modal-ticket-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    animation: fadeIn 0.3s ease-out;
}

.modal-ticket-container {
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideDown 0.4s ease-out;
}

.modal-ticket-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.modal-ticket-header {
    background: linear-gradient(135deg, #2c5aa0, #1e3f73);
    color: white;
    padding: 20px;
    text-align: center;
}

.modal-ticket-title {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 600;
}

.modal-ticket-content {
    padding: 30px;
}

.dropdown-aplicaciones {
    position: relative !important;
    width: 100% !important;
}

.dropdown-aplicaciones .dropdown-menu {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 1050 !important;
    background: white !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    max-height: 200px !important;
    overflow-y: auto !important;
    width: 100% !important;
    margin-top: 2px !important;
    padding: 0 !important;
    transform: none !important;
    inset: auto !important;
    float: none !important;
    display: none !important;
}

.dropdown-aplicaciones .dropdown-menu.show {
    display: block !important;
}

.dropdown-aplicaciones .dropdown-menu.hide {
    display: none !important;
}

.dropdown-aplicaciones .dropdown-item {
    padding: 10px 15px !important;
    cursor: pointer !important;
    border-bottom: 1px solid #f8f9fa !important;
    color: #495057 !important;
    background: white !important;
    transition: background-color 0.15s ease-in-out !important;
    display: block !important;
    width: 100% !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    border: none !important;
    font-size: 14px !important;
    line-height: 1.4 !important;
    margin: 0 !important;
    text-align: left !important;
}

.dropdown-aplicaciones .dropdown-item:hover {
    background-color: #e9ecef !important;
    color: #16181b !important;
}

.dropdown-aplicaciones .dropdown-item:active,
.dropdown-aplicaciones .dropdown-item:focus {
    background-color: #007bff !important;
    color: white !important;
    outline: none !important;
}

.dropdown-aplicaciones .dropdown-item:last-child {
    border-bottom: none !important;
}

.dropdown-aplicaciones .dropdown-item.text-muted {
    color: #6c757d !important;
    font-style: italic !important;
    pointer-events: none !important;
}

.dropdown-aplicaciones input:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
}

.dropdown-aplicaciones .dropdown-menu::-webkit-scrollbar {
    width: 6px;
}

.dropdown-aplicaciones .dropdown-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.dropdown-aplicaciones .dropdown-menu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dropdown-aplicaciones .dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.dropdown-aplicaciones .dropdown-menu * {
    box-sizing: border-box;
}

@media (max-width: 768px) {
    .dropdown-aplicaciones .dropdown-menu {
        font-size: 16px !important; 
    }
    
    .dropdown-aplicaciones .dropdown-item {
        padding: 12px 15px !important;
    }
}

.info-section {
    background: #f8fafc;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e3f2fd;
}

.info-section-title {
    color: #2c5aa0;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 1rem;
    border-bottom: 2px solid #e3f2fd;
    padding-bottom: 8px;
}

.info-item {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-item strong {
    color: #1e3f73;
    min-width: 80px;
}

.badge-estado {
    background: #28a745;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.descripcion-section {
    background: #ffffff;
    border: 2px solid #e3f2fd;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.descripcion-content {
    background: #f8fafc;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #2c5aa0;
    font-size: 0.95rem;
    line-height: 1.6;
    color: #1e3f73;
}

.imagen-section {
    background: #ffffff;
    border: 2px solid #e3f2fd;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
}

.imagen-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
}

.ticket-imagen {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(44, 90, 160, 0.2);
}

.modal-ticket-footer {
    background: #f8fafc;
    padding: 20px;
    text-align: center;
    border-top: 1px solid #e3f2fd;
}

.btn-cerrar-modal {
    background: linear-gradient(135deg, #6f42c1, #5a32a3);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-cerrar-modal:hover {
    background: linear-gradient(135deg, #5a32a3, #4c2a91);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(111, 66, 193, 0.4);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from { 
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to { 
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@media (max-width: 768px) {
    .modal-ticket-container {
        width: 95%;
        margin: 10px;
    }
    
    .modal-ticket-content {
        padding: 20px;
    }
    
    .info-section {
        padding: 15px;
    }
}
</style>

<div class="container py-5">
    <div class="row mb-5 justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body bg-gradient" style="background: linear-gradient(90deg, #f8fafc 60%, #e3f2fd 100%);">
                    <div class="mb-4 text-center">
                        <h5 class="fw-bold text-secondary mb-2">¡Sistema de Soporte Técnico!</h5>
                        <h3 class="fw-bold text-primary mb-0">FORMULARIO DE TICKETS</h3>
                    </div>

                    <!-- Información del usuario -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="bi bi-person-fill me-2"></i>Usuario:</strong> <?= $datosUsuario[0]['nombre'] ?><br>
                                <strong><i class="bi bi-briefcase me-2"></i>Empleo:</strong> <?= $datosUsuario[0]['per_desc_empleo'] ?>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="bi bi-hash me-2"></i>Catálogo:</strong> <?= $datosUsuario[0]['per_catalogo'] ?><br>
                                <strong><i class="bi bi-building me-2"></i>Dependencia:</strong> <?= $datosUsuario[0]['dep_desc_md'] ?>
                            </div>
                        </div>
                    </div>

                        <form id="formTicket" class="p-4 bg-white rounded-3 shadow-sm border">
                        <!-- Campos ocultos con datos de sesión -->
                        <input type="hidden" name="form_tic_usu" value="<?= $datosUsuario[0]['per_catalogo'] ?>">
                        <input type="hidden" name="tic_dependencia" value="<?= $dependenciaUsuario ?>">
                        <input type="hidden" name="form_estado" value="1">
                        
                        <!-- Campo de aplicación con búsqueda mejorada -->
                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_app_input" class="form-label">
                                    <i class="bi bi-app-indicator me-2"></i>Aplicación con Problema <span class="text-danger">*</span>
                                </label>
                                <div class="dropdown-aplicaciones position-relative">
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="tic_app_input" 
                                           placeholder="Escriba el nombre de la aplicación..." 
                                           autocomplete="off" 
                                           required>
                                    <input type="hidden" id="tic_app" name="tic_app" value="">
                                    <div id="contenedorAplicaciones" class="dropdown-menu">
                                        <!-- Las opciones aparecerán aquí dinámicamente -->
                                    </div>
                                </div>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>Escriba para buscar la aplicación que presenta el problema
                                </div>
                            </div>
                        </div>

                        <!-- Campos de contacto -->
                        <div class="row g-4 mb-3">
                            <div class="col-md-6">
                                <label for="tic_correo_electronico" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control form-control-lg" id="tic_correo_electronico" 
                                       name="tic_correo_electronico" placeholder="ejemplo@correo.com" maxlength="100" 
                                       value="<?= $datosUsuario[0]['oper_correo_personal'] ?>" required>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Correo donde recibirá las actualizaciones del ticket</div>
                            </div>
                            <div class="col-md-6">
                                <label for="tic_telefono" class="form-label">
                                    <i class="bi bi-telephone me-2"></i>Teléfono de Contacto <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control form-control-lg" id="tic_telefono" 
                                        name="tic_telefono" placeholder="12345678" maxlength="8" 
                                        value="<?= $datosUsuario[0]['oper_celular_personal'] ?>" required>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Teléfono de 8 dígitos para contacto</div>
                            </div>
                        </div>

                        <!-- Descripción del problema -->
                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_comentario_falla" class="form-label">
                                    <i class="bi bi-chat-text me-2"></i>Descripción del Problema <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="tic_comentario_falla" name="tic_comentario_falla" 
                                          rows="6" maxlength="2000" 
                                          placeholder="Describa detalladamente el problema que está experimentando..." 
                                          required></textarea>
                                <div class="invalid-feedback"></div>
                                <div class="d-flex justify-content-between">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>Sea específico para una mejor atención
                                    </div>
                                    <small class="text-muted">
                                        <span id="contadorCaracteres">0</span>/2000 caracteres
                                        <span class="text-danger">(mínimo 15)</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Campo de imágenes -->
                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_imagen" class="form-label">
                                    <i class="bi bi-image me-2"></i>Imágenes del Problema (Opcional)
                                </label>
                                <input type="file" class="form-control form-control-lg" id="tic_imagen" 
                                       name="tic_imagen[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">
                                    <strong>Formatos permitidos:</strong> JPG, PNG, GIF, WEBP | 
                                    <strong>Tamaño máximo:</strong> 8MB por imagen |
                                    <strong>Máximo:</strong> 5 imágenes
                                </div>
                                
                                <!-- Vista previa de imágenes -->
                                <div id="contenedorVistaPrevia" class="mt-3 d-none">
                                    <div class="card border-success">
                                        <div class="card-header bg-success bg-opacity-10 py-2">
                                            <h6 class="mb-0 text-success">
                                                <i class="bi bi-check-circle me-2"></i>Vista Previa de las Imágenes
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="imagenesPreview" class="row g-2">
                                                <!-- Las imágenes se mostrarán aquí -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button class="btn btn-success btn-lg px-4 shadow" type="submit" id="BtnEnviar">
                                <i class="bi bi-send me-2"></i>Crear Ticket
                            </button>
                            <button class="btn btn-secondary btn-lg px-4 shadow" type="button" id="BtnLimpiar">
                                <i class="bi bi-eraser me-2"></i>Limpiar Formulario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar detalles del ticket creado -->
<div id="modalTicket" class="modal-ticket-overlay" style="display: none;">
    <div class="modal-ticket-container">
        <div class="modal-ticket-card">
            <!-- Header del Modal -->
            <div class="modal-ticket-header">
                <h4 class="modal-ticket-title">¡Ticket Creado Exitosamente!</h4>
            </div>

            <!-- Contenido del Modal -->
            <div class="modal-ticket-content">
                <!-- Información del Ticket y Usuario -->
                <div class="row">
                    <!-- Columna Izquierda: Información del Ticket -->
                    <div class="col-md-6">
                        <div class="info-section">
                            <h6 class="info-section-title">Información del Ticket</h6>
                            <div class="info-item">
                                <strong>Número:</strong> <span id="ticketNumero"></span>
                            </div>
                            <div class="info-item">
                                <strong>Fecha:</strong> <span id="ticketFecha"></span>
                            </div>
                            <div class="info-item">
                                <strong>Estado:</strong> 
                                <span class="badge-estado">CREADO</span>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Datos del Solicitante -->
                    <div class="col-md-6">
                        <div class="info-section">
                            <h6 class="info-section-title">Solicitante</h6>
                            <div class="info-item">
                                <strong>Nombre:</strong> <span id="ticketUsuario"><?= $datosUsuario[0]['nombre'] ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Teléfono:</strong> <span id="ticketTelefono"></span>
                            </div>
                            <div class="info-item">
                                <strong>Correo:</strong> <span id="ticketCorreo"></span>
                            </div>
                            <div class="info-item">
                                <strong>Dependencia:</strong> <span id="ticketDependencia"><?= $datosUsuario[0]['dep_desc_md'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción del Problema -->
                <div class="descripcion-section">
                    <h6 class="info-section-title">Descripción del Problema</h6>
                    <div class="descripcion-content" id="ticketDescripcion">
                        <!-- Aquí se mostrará la descripción -->
                    </div>
                </div>

                <!-- Imágenes Adjuntas -->
                <div id="imagenSection" class="imagen-section" style="display: none;">
                    <h6 class="info-section-title">Imágenes Adjuntas</h6>
                    <div class="imagen-container" id="ticketImagenContainer">
                        <!-- Las imágenes se mostrarán aquí -->
                    </div>
                </div>
            </div>

            <!-- Footer del Modal -->
            <div class="modal-ticket-footer">
                <button type="button" class="btn-cerrar-modal" onclick="cerrarModalTicket()">
                    <i class="bi bi-check-circle me-2"></i>Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/ticket/index.js') ?>"></script>