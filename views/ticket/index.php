<?php
// Obtener datos de la sesión
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'EMPLEADO';
$catalogoUsuario = $_SESSION['per_catalogo'] ?? null;
$dependenciaUsuario = $_SESSION['dep_llave'] ?? null;
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
                                <strong><i class="bi bi-building me-2"></i>Dependencia:</strong> <?= $dependenciaUsuario ?? 'No definida' ?>
                            </div>
                        </div>
                    </div>

                    <form id="formTicket" class="p-4 bg-white rounded-3 shadow-sm border" enctype="multipart/form-data">
                        <!-- Campos ocultos con datos de sesión -->
                        <input type="hidden" name="form_tic_usu" value="<?= $catalogoUsuario ?>">
                        <input type="hidden" name="tic_dependencia" value="<?= $dependenciaUsuario ?>">
                        
                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_correo_electronico" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" class="form-control form-control-lg" id="tic_correo_electronico" name="tic_correo_electronico" placeholder="ejemplo@correo.com" maxlength="250" required>
                                <div class="form-text">Correo donde recibirá las actualizaciones del ticket</div>
                            </div>
                        </div>

                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_comentario_falla" class="form-label">
                                    <i class="bi bi-chat-text me-2"></i>Descripción del Problema
                                </label>
                                <textarea class="form-control" id="tic_comentario_falla" name="tic_comentario_falla" rows="6" maxlength="2000" placeholder="Describa detalladamente el problema que está experimentando..." required></textarea>
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
                                    <i class="bi bi-image me-2"></i>Imagen del Problema (Opcional)
                                </label>
                                <input type="file" class="form-control form-control-lg" id="tic_imagen" name="tic_imagen" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <div class="form-text">
                                    <strong>Formatos permitidos:</strong> JPG, PNG, GIF, WEBP | 
                                    <strong>Tamaño máximo:</strong> 8MB
                                </div>
                                
                                <!-- Vista previa de imagen -->
                                <div id="contenedorVistaPrevia" class="mt-3 d-none">
                                    <div class="card border-success">
                                        <div class="card-header bg-success bg-opacity-10 py-2">
                                            <h6 class="mb-0 text-success">
                                                <i class="bi bi-check-circle me-2"></i>Vista Previa de la Imagen
                                            </h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <img id="vistaPrevia" class="img-fluid rounded" style="max-height: 200px;" alt="Vista previa">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-3">
                            <button class="btn btn-success btn-lg px-4 shadow" type="submit" id="BtnEnviar">
                                <i class="bi bi-send me-2"></i>Enviar Ticket
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="<?= asset('build/js/ticket/index.js') ?>"></script>