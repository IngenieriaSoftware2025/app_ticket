<?php
// Obtener datos de la sesión
$catalogoUsuario = $_SESSION['auth_user'] ?? null;
$dependenciaUsuario = $_SESSION['dep_llave'] ?? 999; // Dependencia de prueba por defecto

// Obtener datos del usuario desde mper
$nombreUsuario = 'No disponible';
$rolUsuario = 'No disponible';
$telefonoUsuario = 'No disponible';
$nombreDependencia = 'DEPENDENCIA DE PRUEBA'; // Valor por defecto

if ($catalogoUsuario) {
    try {
        $sqlUsuario = "SELECT per_telefono, per_nom1, per_nom2, per_ape1, per_desc_empleo 
                      FROM mper 
                      WHERE per_catalogo = $catalogoUsuario";
        $datosUsuario = \Model\ActiveRecord::fetchFirst($sqlUsuario);
        
        if ($datosUsuario) {
            $nombreUsuario = trim($datosUsuario['per_nom1'] . ' ' . $datosUsuario['per_nom2'] . ' ' . $datosUsuario['per_ape1']);
            $rolUsuario = $datosUsuario['per_desc_empleo'] ?? 'No definido';
            $telefonoUsuario = $datosUsuario['per_telefono'] ?? 'No registrado';
        }
        
        // Intentar obtener el nombre real de la dependencia si existe
        if ($dependenciaUsuario != 999) {
            $sqlDependencia = "SELECT dep_desc_lg FROM mdep WHERE dep_llave = $dependenciaUsuario";
            $datosDependencia = \Model\ActiveRecord::fetchFirst($sqlDependencia);
            if ($datosDependencia) {
                $nombreDependencia = $datosDependencia['dep_desc_lg'];
            }
        }
    } catch (Exception $e) {
        // Los valores por defecto ya están asignados
    }
}
?>

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
                                <strong><i class="bi bi-person-fill me-2"></i>Usuario:</strong> <?= $nombreUsuario ?><br>
                                <strong><i class="bi bi-shield-check me-2"></i>Rol:</strong> <?= $rolUsuario ?>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="bi bi-hash me-2"></i>Catálogo:</strong> <?= $catalogoUsuario ?? 'No definido' ?><br>
                                <strong><i class="bi bi-telephone-fill me-2"></i>Teléfono:</strong> <?= $telefonoUsuario ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong><i class="bi bi-building me-2"></i>Dependencia:</strong> <?= $nombreDependencia ?>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="bi bi-envelope-fill me-2"></i>Correo:</strong> <span class="text-muted">Se ingresa en el formulario</span>
                            </div>
                        </div>
                    </div>

                    <form id="formTicket" method="POST" action="/<?= $_ENV['APP_NAME'] ?>/ticket/guardarAPI" class="p-4 bg-white rounded-3 shadow-sm border" enctype="multipart/form-data">
                        <!-- Campos ocultos con datos de sesión -->
                        <input type="hidden" name="form_tic_usu" value="<?= $catalogoUsuario ?>">
                        <input type="hidden" name="tic_dependencia" value="<?= $dependenciaUsuario ?>">
                        <input type="hidden" name="form_estado" value="1">
                        
                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_app" class="form-label">
                                    <i class="bi bi-app-indicator me-2"></i>Aplicación con Problema
                                </label>
                                <select class="form-control form-control-lg" id="tic_app" name="tic_app" required>
                                    <option value="">Seleccione la aplicación con problemas...</option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Seleccione la aplicación que presenta el problema</div>
                            </div>
                        </div>

                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_correo_electronico" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" class="form-control form-control-lg" id="tic_correo_electronico" name="tic_correo_electronico" placeholder="ejemplo@correo.com" maxlength="100" required>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Correo donde recibirá las actualizaciones del ticket</div>
                            </div>
                        </div>

                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_comentario_falla" class="form-label">
                                    <i class="bi bi-chat-text me-2"></i>Descripción del Problema
                                </label>
                                <textarea class="form-control" id="tic_comentario_falla" name="tic_comentario_falla" rows="6" maxlength="2000" placeholder="Describa detalladamente el problema que está experimentando..." required></textarea>
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

                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_imagen" class="form-label">
                                    <i class="bi bi-image me-2"></i>Imágenes del Problema (Opcional)
                                </label>
                                <input type="file" class="form-control form-control-lg" id="tic_imagen" name="tic_imagen[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple>
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
                        
                        <div class="d-flex justify-content-center gap-3">
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

<!-- MODAL PARA MOSTRAR DETALLES DEL TICKET -->
<div id="modalTicket" class="modal-ticket-overlay" style="display: none;">
    <div class="modal-ticket-container">
        <div class="modal-ticket-card">
            <!-- Header del Modal -->
            <div class="modal-ticket-header">
                <h4 class="modal-ticket-title" id="ticketModalTitle">Ticket Creado Exitosamente</h4>
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
                                <span class="badge-estado" id="ticketEstado">CREADO</span>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Datos del Solicitante -->
                    <div class="col-md-6">
                        <div class="info-section">
                            <h6 class="info-section-title">Solicitante</h6>
                            <div class="info-item">
                                <strong>Nombre:</strong> <span id="ticketUsuario"><?= $nombreUsuario ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Teléfono:</strong> <span id="ticketTelefono"><?= $telefonoUsuario ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Correo:</strong> <span id="ticketCorreo"></span>
                            </div>
                            <div class="info-item">
                                <strong>Dependencia:</strong> <span id="ticketDependencia"><?= $nombreDependencia ?></span>
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

                <!-- Imágenes Adjuntas (si existen) -->
                <div id="imagenSection" class="imagen-section" style="display: none;">
                    <h6 class="info-section-title">Imágenes Adjuntas</h6>
                    <div class="imagen-container">
                        <img id="ticketImagen" class="ticket-imagen" alt="Imagen del problema">
                    </div>
                </div>
            </div>

            <!-- Footer del Modal -->
            <div class="modal-ticket-footer">
                <button type="button" class="btn-cerrar-modal" onclick="cerrarModalTicket()">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Estilos CSS para el Modal -->
<style>
/* Overlay del Modal */
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

/* Container del Modal */
.modal-ticket-container {
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideDown 0.4s ease-out;
}

/* Card Principal del Modal */
.modal-ticket-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

/* Header del Modal */
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

/* Contenido del Modal */
.modal-ticket-content {
    padding: 30px;
}

/* Secciones de Información */
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

/* Badge de Estado */
.badge-estado {
    background: #28a745;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

/* Sección de Descripción */
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

/* Sección de Imagen */
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

/* Footer del Modal */
.modal-ticket-footer {
    background: #f8fafc;
    padding: 20px;
    text-align: center;
    border-top: 1px solid #e3f2fd;
}

/* Botón Cerrar */
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

/* Animaciones */
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

/* Responsive */
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

<script>
// Configurar contador de caracteres para textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('tic_comentario_falla');
    const contador = document.getElementById('contadorCaracteres');
    
    if (textarea && contador) {
        const actualizarContador = () => {
            const longitud = textarea.value.length;
            contador.textContent = longitud;
            
            if (longitud < 15) {
                contador.parentElement.className = 'text-danger';
            } else {
                contador.parentElement.className = 'text-muted';
            }
        };
        
        textarea.addEventListener('input', actualizarContador);
        textarea.addEventListener('keyup', actualizarContador);
        actualizarContador();
    }
});
</script>

<script src="<?= asset('build/js/ticket/index.js') ?>"></script>