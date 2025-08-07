<?php
// Vista: views/tickets/index.php
// Sistema de Tickets - Formulario con datos de sesión
session_start();
$userName = $_SESSION['user'] ?? 'Usuario';
$userRole = $_SESSION['usuario_rol'] ?? 'EMPLEADO';
$userCatalogo = $_SESSION['per_catalogo'] ?? null;
$userDependencia = $_SESSION['dep_llave'] ?? null;
?>

<style>
    body {
        background: #f8fbff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 80%, rgba(44, 90, 160, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(74, 144, 226, 0.2) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(135, 206, 235, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 60% 70%, rgba(227, 242, 253, 0.1) 0%, transparent 50%);
        z-index: -1;
        animation: backgroundMove 20s ease-in-out infinite;
    }

    @keyframes backgroundMove {
        0%, 100% { transform: translateX(0) translateY(0); }
        25% { transform: translateX(-20px) translateY(-10px); }
        50% { transform: translateX(20px) translateY(10px); }
        75% { transform: translateX(-10px) translateY(20px); }
    }

    .header {
        padding: 3rem 2rem;
        text-align: center;
        border-radius: 20px;
        margin-top: 2rem;
        margin-bottom: 3rem;
        max-width: 1140px;
        margin-left: auto;
        margin-right: auto;
        background: rgba(44, 90, 160, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(44, 90, 160, 0.2);
        box-shadow: 0 8px 25px rgba(44, 90, 160, 0.15);
    }
    
    .logo {
        font-size: 3.5rem;
        font-weight: 800;
        color: #2c5aa0;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(44, 90, 160, 0.3);
    }

    .welcome-section {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        border: 1px solid rgba(44, 90, 160, 0.2);
        box-shadow: 0 5px 15px rgba(44, 90, 160, 0.1);
    }

    .user-welcome {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 1rem;
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        background: #2c5aa0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .user-details h3 {
        margin: 0;
        color: #2c5aa0;
        font-weight: 700;
    }

    .user-role-badge {
        background: #28a745;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .container {
        max-width: 1140px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        z-index: 1;
    }

    .card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(74, 144, 226, 0.3);
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(44, 90, 160, 0.15);
        position: relative;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(44, 90, 160, 0.2);
    }

    .card-header {
        background: linear-gradient(135deg, #2c5aa0, #1e3f73);
        color: white;
        border-radius: 20px 20px 0 0 !important;
        border-bottom: none;
        padding: 2rem;
    }

    .btn {
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        font-weight: 600;
        border-radius: 12px;
        padding: 12px 24px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2c5aa0, #1e3f73);
        box-shadow: 0 4px 15px rgba(44, 90, 160, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1e3f73, #1a365d);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(44, 90, 160, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d, #545b62);
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #545b62, #383d41);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    }

    .form-control, .form-select {
        border: 2px solid rgba(44, 90, 160, 0.2);
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
    }

    .form-control:focus, .form-select:focus {
        border-color: #2c5aa0;
        box-shadow: 0 0 0 0.25rem rgba(44, 90, 160, 0.25);
        background: rgba(255, 255, 255, 1);
    }

    .form-label {
        color: #2c5aa0;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .form-text {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .vista-previa-imagen {
        max-width: 100%;
        max-height: 200px;
        border-radius: 10px;
        border: 2px dashed #dee2e6;
        display: none;
        object-fit: cover;
    }

    .border-success {
        border-color: #28a745 !important;
    }

    .border-warning {
        border-color: #ffc107 !important;
    }

    .border-danger {
        border-color: #dc3545 !important;
    }

    .tabs-container {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(44, 90, 160, 0.2);
        box-shadow: 0 5px 15px rgba(44, 90, 160, 0.1);
    }

    .nav-pills .nav-link {
        border-radius: 10px;
        margin: 0 0.25rem;
        padding: 12px 20px;
        transition: all 0.3s ease;
        color: #2c5aa0;
        font-weight: 600;
    }

    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #2c5aa0, #1e3f73);
        color: white;
    }

    .table {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 15px;
        overflow: hidden;
    }

    .table th {
        background: linear-gradient(135deg, #2c5aa0, #1e3f73);
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(44, 90, 160, 0.1);
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
</style>

<body>
    <div class="header">
        <div class="logo">
            <i class="bi bi-headset"></i> Sistema de Tickets de Soporte
        </div>
        <p class="text-muted mb-0">Gestión integral de solicitudes de atención al usuario</p>
    </div>
    
    <div class="container">
        <!-- INFORMACIÓN DEL USUARIO -->
        <div class="welcome-section">
            <div class="user-welcome">
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="user-details text-center">
                    <h3><?= $userName ?></h3>
                    <span class="user-role-badge"><?= $userRole ?></span>
                </div>
            </div>
            <p class="text-center text-muted mb-0">
                Reporte problemas técnicos y solicite soporte especializado
            </p>
        </div>

        <!-- PESTAÑAS PRINCIPALES -->
        <div class="tabs-container">
            <ul class="nav nav-pills nav-fill mb-0" id="pestanasTickets" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pestana-crear" data-bs-toggle="pill" data-bs-target="#contenido-crear" type="button" role="tab">
                        <i class="bi bi-plus-circle me-2"></i>Crear Ticket
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pestana-mis-tickets" data-bs-toggle="pill" data-bs-target="#contenido-mis-tickets" type="button" role="tab">
                        <i class="bi bi-list-task me-2"></i>Mis Tickets
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pestana-buscar" data-bs-toggle="pill" data-bs-target="#contenido-buscar" type="button" role="tab">
                        <i class="bi bi-search me-2"></i>Buscar Ticket
                    </button>
                </li>
            </ul>
        </div>

        <!-- CONTENIDO DE PESTAÑAS -->
        <div class="tab-content" id="contenidoPestanasTickets">
            
            <!-- PESTAÑA: CREAR NUEVO TICKET -->
            <div class="tab-pane fade show active" id="contenido-crear" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card shadow-lg border-0">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-plus-circle-fill fs-3 me-3"></i>
                                    <div>
                                        <h4 class="card-title mb-1">Crear Nuevo Ticket de Soporte</h4>
                                        <p class="card-text mb-0 opacity-75">Complete la información de su solicitud</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body p-4">
                                <form id="formularioNuevoTicket" enctype="multipart/form-data">
                                    <!-- DATOS DEL USUARIO (AUTOMÁTICOS) -->
                                    <input type="hidden" name="form_tic_usu" value="<?= $userCatalogo ?>">
                                    <input type="hidden" name="tic_dependencia" value="<?= $userDependencia ?>">
                                    
                                    <!-- INFORMACIÓN AUTOMÁTICA -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <h6 class="alert-heading">
                                                    <i class="bi bi-info-circle me-2"></i>Información Automática
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Usuario:</strong> <?= $userName ?><br>
                                                        <strong>Rol:</strong> <?= $userRole ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Catálogo:</strong> <?= $userCatalogo ?? 'No definido' ?><br>
                                                        <strong>Dependencia:</strong> <span id="nombreDependencia">Cargando...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- CORREO ELECTRÓNICO -->
                                        <div class="col-12 mb-4">
                                            <h5 class="border-bottom pb-2 mb-3">
                                                <i class="bi bi-envelope-at text-primary me-2"></i>Información de Contacto
                                            </h5>
                                            <div class="mb-3">
                                                <label for="correoElectronico" class="form-label">
                                                    Correo Electrónico <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="bi bi-envelope"></i>
                                                    </span>
                                                    <input type="email" class="form-control" id="correoElectronico" name="tic_correo_electronico" 
                                                           placeholder="usuario@institucion.gob.gt" maxlength="250" required>
                                                </div>
                                                <div class="form-text">
                                                    <i class="bi bi-bell me-1"></i>Correo donde recibirá las actualizaciones del ticket
                                                </div>
                                            </div>
                                        </div>

                                        <!-- DESCRIPCIÓN DEL PROBLEMA -->
                                        <div class="col-12 mb-4">
                                            <h5 class="border-bottom pb-2 mb-3">
                                                <i class="bi bi-exclamation-triangle text-warning me-2"></i>Descripción del Problema
                                            </h5>
                                            <div class="mb-3">
                                                <label for="descripcionProblema" class="form-label">
                                                    Descripción Detallada del Problema <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control" id="descripcionProblema" name="tic_comentario_falla" 
                                                          rows="6" maxlength="2000" required
                                                          placeholder="Describa detalladamente el problema:&#10;&#10;• ¿Qué aplicación o sistema está involucrado?&#10;• ¿Qué estaba haciendo cuando ocurrió el problema?&#10;• ¿Qué mensaje de error aparece (si aplica)?&#10;• ¿Cuándo comenzó a presentarse el inconveniente?&#10;• ¿Es la primera vez que ocurre o es recurrente?&#10;• ¿Otros detalles relevantes que puedan ayudar?"></textarea>
                                                <div class="d-flex justify-content-between">
                                                    <div class="form-text">
                                                        <i class="bi bi-chat-square-text me-1"></i>Sea específico para una mejor atención
                                                    </div>
                                                    <small class="text-muted">
                                                        <span id="contadorCaracteres">0</span>/2000 caracteres
                                                        <span class="text-danger">(mínimo 15)</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ARCHIVO ADJUNTO -->
                                        <div class="col-12 mb-4">
                                            <h5 class="border-bottom pb-2 mb-3">
                                                <i class="bi bi-paperclip text-success me-2"></i>Archivo Adjunto (Opcional)
                                            </h5>
                                            <div class="mb-3">
                                                <label for="imagenProblema" class="form-label">
                                                    <i class="bi bi-image me-2"></i>Captura de Pantalla o Imagen
                                                </label>
                                                <input type="file" class="form-control" id="imagenProblema" name="tic_imagen" 
                                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                                <div class="form-text">
                                                    <strong>Formatos:</strong> JPG, PNG, GIF, WEBP | 
                                                    <strong>Tamaño máximo:</strong> 8MB
                                                </div>
                                            </div>
                                            
                                            <!-- Vista previa -->
                                            <div id="contenedorVistaPrevia" class="d-none">
                                                <div class="card border-success">
                                                    <div class="card-header bg-success bg-opacity-10 py-2">
                                                        <h6 class="mb-0 text-success">
                                                            <i class="bi bi-check-circle me-2"></i>Vista Previa
                                                        </h6>
                                                    </div>
                                                    <div class="card-body text-center">
                                                        <img id="vistaPrevia" class="vista-previa-imagen" alt="Vista previa">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- BOTONES DE ACCIÓN -->
                                    <div class="border-top pt-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" class="btn btn-secondary" id="botonLimpiarFormulario">
                                                <i class="bi bi-arrow-clockwise me-2"></i>Limpiar Formulario
                                            </button>
                                            <button type="submit" class="btn btn-primary px-5" id="botonCrearTicket">
                                                <i class="bi bi-send me-2"></i>Crear Ticket de Soporte
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA: MIS TICKETS -->
            <div class="tab-pane fade" id="contenido-mis-tickets" role="tabpanel">
                <div class="card shadow border-0">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1">
                                    <i class="bi bi-person-lines-fill me-2"></i>Mis Tickets
                                </h4>
                                <p class="mb-0 opacity-75">Tickets creados por <?= $userName ?></p>
                            </div>
                            <button class="btn btn-light" id="botonActualizarMisTickets">
                                <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Encargado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaMisTickets">
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="spinner-border text-primary mb-3"></div>
                                            <p class="text-muted mb-0">Cargando sus tickets...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA: BUSCAR TICKET -->
            <div class="tab-pane fade" id="contenido-buscar" role="tabpanel">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow border-0">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-search me-2"></i>Buscar Ticket
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="formularioBuscarTicket">
                                    <div class="mb-3">
                                        <label class="form-label">Número de Ticket</label>
                                        <input type="text" class="form-control" id="buscarNumeroTicket" 
                                               placeholder="TK202501001" maxlength="15">
                                        <div class="form-text">Ingrese el número completo del ticket</div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search me-2"></i>Buscar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8">
                        <div class="card shadow border-0">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>Resultado de Búsqueda
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="resultadoBusqueda">
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-search display-4 mb-3"></i>
                                        <p class="mb-0">Ingrese un número de ticket para consultar su estado</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER DETALLES -->
    <div class="modal fade" id="modalDetalleTicket" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>Detalles del Ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contenidoDetalleTicket">
                    <!-- Contenido cargado dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= asset('build/js/tickets/index.js') ?>"></script>

    <script>
    // Variables globales con datos de sesión
    const usuarioActual = {
        catalogo: <?= json_encode($userCatalogo) ?>,
        nombre: <?= json_encode($userName) ?>,
        rol: <?= json_encode($userRole) ?>,
        dependencia: <?= json_encode($userDependencia) ?>
    };

    // Inicialización al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar nombre de dependencia
        cargarNombreDependencia();
        
        // Configurar eventos
        configurarEventosFormulario();
        
        // Cargar mis tickets automáticamente
        cargarMisTickets();
    });

    // Cargar nombre de la dependencia del usuario
    async function cargarNombreDependencia() {
        if (!usuarioActual.dependencia) {
            document.getElementById('nombreDependencia').textContent = 'No asignada';
            return;
        }

        try {
            const response = await fetch('/proyecto_jjjc/tickets/dependencias', { method: 'POST' });
            const data = await response.json();
            
            if (data.codigo == 1) {
                const dependencia = data.data.find(dep => dep.dep_llave == usuarioActual.dependencia);
                document.getElementById('nombreDependencia').textContent = 
                    dependencia ? dependencia.descripcion_larga : 'Dependencia no encontrada';
            }
        } catch (error) {
            console.error('Error al cargar dependencia:', error);
            document.getElementById('nombreDependencia').textContent = 'Error al cargar';
        }
    }

    // Configurar eventos del formulario
    function configurarEventosFormulario() {
        // Contador de caracteres
        const textarea = document.getElementById('descripcionProblema');
        const contador = document.getElementById('contadorCaracteres');
        
        textarea.addEventListener('input', function() {
            const longitud = this.value.length;
            contador.textContent = longitud;
            
            // Cambiar estilos según longitud
            this.classList.remove('border-success', 'border-warning', 'border-danger');
            if (longitud < 15) {
                this.classList.add('border-danger');
                contador.className = 'text-danger fw-bold';
            } else if (longitud > 1800) {
                this.classList.add('border-warning');
                contador.className = 'text-warning fw-bold';
            } else {
                this.classList.add('border-success');
                contador.className = 'text-success';
            }
        });

        // Vista previa de imagen
        const inputImagen = document.getElementById('imagenProblema');
        inputImagen.addEventListener('change', mostrarVistaPrevia);

        // Formulario de crear ticket
        document.getElementById('formularioNuevoTicket').addEventListener('submit', crearTicket);

        // Formulario de buscar ticket
        document.getElementById('formularioBuscarTicket').addEventListener('submit', buscarTicketPorNumero);

        // Botones
        document.getElementById('botonLimpiarFormulario').addEventListener('click', limpiarFormulario);
        document.getElementById('botonActualizarMisTickets').addEventListener('click', cargarMisTickets);

        // Eventos de pestañas
        document.querySelectorAll('[data-bs-toggle="pill"]').forEach(pestaña => {
            pestaña.addEventListener('shown.bs.tab', function(evento) {
                const target = evento.target.getAttribute('data-bs-target');
                if (target === '#contenido-mis-tickets') {
                    cargarMisTickets();
                }
            });
        });
    }

    // Mostrar vista previa de imagen
    function mostrarVistaPrevia(evento) {
        const archivo = evento.target.files[0];
        const vistaPrevia = document.getElementById('vistaPrevia');
        const contenedor = document.getElementById('contenedorVistaPrevia');

        if (archivo) {
            // Validaciones
            if (!archivo.type.startsWith('image/')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo No Válido',
                    text: 'Solo se permiten archivos de imagen',
                    confirmButtonText: 'Entendido'
                });
                evento.target.value = '';
                contenedor.classList.add('d-none');
                return;
            }

            if (archivo.size > 8 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivo Muy Grande',
                    text: 'La imagen no puede ser mayor a 8MB',
                    confirmButtonText: 'Entendido'
                });
                evento.target.value = '';
                contenedor.classList.add('d-none');
                return;
            }

            // Mostrar vista previa
            const lector = new FileReader();
            lector.onload = function(e) {
                vistaPrevia.src = e.target.result;
                vistaPrevia.style.display = 'block';
                contenedor.classList.remove('d-none');
            };
            lector.readAsDataURL(archivo);
        } else {
            contenedor.classList.add('d-none');
        }
    }

    // Crear nuevo ticket
    async function crearTicket(evento) {
        evento.preventDefault();

        const boton = document.getElementById('botonCrearTicket');
        boton.disabled = true;
        boton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creando Ticket...';

        const formData = new FormData(evento.target);

        try {
            const response = await fetch('/proyecto_jjjc/tickets/guardar', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.codigo == 1) {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Ticket Creado Exitosamente!',
                    html: `
                        <div class="text-center">
                            <h4 class="text-primary mb-3">Número de Ticket</h4>
                            <div class="bg-light p-3 rounded mb-3">
                                <h2 class="text-primary mb-0">${data.data.numero_ticket}</h2>
                            </div>
                            <p class="mb-0">Guarde este número para dar seguimiento a su solicitud</p>
                        </div>
                    `,
                    confirmButtonText: 'Entendido',
                    timer: 8000
                });

                limpiarFormulario();
                cargarMisTickets();
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error al Crear Ticket',
                    text: data.mensaje,
                    confirmButtonText: 'Intentar de Nuevo'
                });
            }
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'No se pudo conectar con el servidor',
                confirmButtonText: 'Reintentar'
            });
        }

        // Restaurar botón
        boton.disabled = false;
        boton.innerHTML = '<i class="bi bi-send me-2"></i>Crear Ticket de Soporte';
    }

    // Cargar mis tickets (del usuario logueado)
    async function cargarMisTickets() {
        const tabla = document.getElementById('tablaMisTickets');
        tabla.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border text-primary mb-3"></div>
                    <p class="text-muted mb-0">Cargando sus tickets...</p>
                </td>
            </tr>
        `;

        try {
            const response = await fetch('/proyecto_jjjc/tickets/buscar', { method: 'POST' });
            const data = await response.json();

            if (data.codigo == 1) {
                // Filtrar solo los tickets del usuario actual
                const misTickets = data.data.filter(ticket => 
                    ticket.form_tic_usu == usuarioActual.catalogo || 
                    ticket.per_catalogo == usuarioActual.catalogo
                );

                if (misTickets.length > 0) {
                    tabla.innerHTML = '';
                    misTickets.forEach(ticket => {
                        const fila = crearFilaTicket(ticket);
                        tabla.appendChild(fila);
                    });
                } else {
                    tabla.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox display-4 mb-3 text-muted"></i>
                                <p class="text-muted mb-0">No tiene tickets registrados</p>
                            </td>
                        </tr>
                    `;
                }
            } else {
                tabla.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle mb-3"></i>
                            <p class="mb-0">Error al cargar tickets</p>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            tabla.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        <i class="bi bi-wifi-off display-4 mb-3"></i>
                        <p class="mb-0">Error de conexión</p>
                    </td>
                </tr>
            `;
        }
    }

    // Buscar ticket por número
    async function buscarTicketPorNumero(evento) {
        evento.preventDefault();
        
        const numeroTicket = document.getElementById('buscarNumeroTicket').value.trim().toUpperCase();
        const resultado = document.getElementById('resultadoBusqueda');

        if (!numeroTicket) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo Requerido',
                text: 'Ingrese el número del ticket a buscar',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        resultado.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3"></div>
                <p class="mb-0">Buscando ticket ${numeroTicket}...</p>
            </div>
        `;

        try {
            const response = await fetch(`/proyecto_jjjc/tickets/detalle?numero_ticket=${numeroTicket}`);
            const data = await response.json();

            if (data.codigo == 1) {
                const ticket = data.data;
                resultado.innerHTML = `
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle me-2"></i>Ticket Encontrado: ${ticket.form_tick_num}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Solicitante:</strong> ${ticket.solicitante_completo || 'N/A'}</p>
                                    <p><strong>Dependencia:</strong> ${ticket.dependencia_completa || 'N/A'}</p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge bg-primary">${ticket.estado_descripcion || 'Creado'}</span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Fecha:</strong> ${new Date(ticket.form_fecha_creacion).toLocaleDateString('es-GT')}</p>
                                    <p><strong>Encargado:</strong> ${ticket.encargado_nombre || 'Sin asignar'}</p>
                                    <p><strong>Correo:</strong> ${ticket.tic_correo_electronico}</p>
                                </div>
                                <div class="col-12">
                                    <p><strong>Descripción:</strong></p>
                                    <div class="bg-light p-3 rounded">
                                        ${ticket.tic_comentario_falla}
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-primary btn-ver-detalle" data-ticket="${ticket.form_tick_num}">
                                    <i class="bi bi-eye me-2"></i>Ver Detalles Completos
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                // Configurar evento del botón
                document.querySelector('.btn-ver-detalle').addEventListener('click', function() {
                    mostrarDetalleCompleto(this.dataset.ticket);
                });

            } else {
                resultado.innerHTML = `
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="bi bi-search display-4 text-warning mb-3"></i>
                            <h5 class="text-warning">Ticket No Encontrado</h5>
                            <p class="text-muted mb-0">
                                No se encontró ningún ticket con el número <strong>${numeroTicket}</strong>
                            </p>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            resultado.innerHTML = `
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle display-4 text-danger mb-3"></i>
                        <h5 class="text-danger">Error de Búsqueda</h5>
                        <p class="text-muted mb-0">No se pudo realizar la búsqueda</p>
                    </div>
                </div>
            `;
        }
    }

    // Crear fila de ticket para tabla
    function crearFilaTicket(ticket) {
        const fila = document.createElement('tr');
        const fecha = new Date(ticket.form_fecha_creacion).toLocaleDateString('es-GT');
        const descripcionCorta = ticket.tic_comentario_falla.length > 50 
            ? ticket.tic_comentario_falla.substring(0, 50) + '...'
            : ticket.tic_comentario_falla;

        // Determinar color del estado
        let colorEstado = 'secondary';
        switch(ticket.estado_ticket) {
            case 2: colorEstado = 'warning'; break;
            case 3: colorEstado = 'info'; break;
            case 4: colorEstado = 'success'; break;
            case 5: colorEstado = 'primary'; break;
        }

        fila.innerHTML = `
            <td class="fw-bold text-primary">${ticket.form_tick_num}</td>
            <td>
                <span title="${ticket.tic_comentario_falla}">${descripcionCorta}</span>
            </td>
            <td>
                <span class="badge bg-${colorEstado}">
                    ${ticket.descripcion_estado || 'Creado'}
                </span>
            </td>
            <td><small>${fecha}</small></td>
            <td>${ticket.nombre_encargado || '<em class="text-muted">Sin asignar</em>'}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary btn-ver-detalle" 
                            data-ticket="${ticket.form_tick_num}" title="Ver Detalles">
                        <i class="bi bi-eye"></i>
                    </button>
                    ${ticket.tic_imagen ? 
                        `<button class="btn btn-outline-success btn-ver-imagen" 
                                 data-imagen="${ticket.tic_imagen}" title="Ver Imagen">
                            <i class="bi bi-image"></i>
                        </button>` : ''
                    }
                </div>
            </td>
        `;

        // Configurar eventos de los botones
        fila.querySelector('.btn-ver-detalle').addEventListener('click', function() {
            mostrarDetalleCompleto(this.dataset.ticket);
        });

        const btnImagen = fila.querySelector('.btn-ver-imagen');
        if (btnImagen) {
            btnImagen.addEventListener('click', function() {
                mostrarImagenTicket(this.dataset.imagen);
            });
        }

        return fila;
    }

    // Mostrar detalles completos del ticket
    async function mostrarDetalleCompleto(numeroTicket) {
        try {
            const response = await fetch(`/proyecto_jjjc/tickets/detalle?numero_ticket=${numeroTicket}`);
            const data = await response.json();

            if (data.codigo == 1) {
                const ticket = data.data;
                const fecha = new Date(ticket.form_fecha_creacion).toLocaleString('es-GT');

                document.getElementById('contenidoDetalleTicket').innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">Información General</h6>
                            <p><strong>Número:</strong> ${ticket.form_tick_num}</p>
                            <p><strong>Fecha:</strong> ${fecha}</p>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-info">${ticket.estado_descripcion || 'Creado'}</span>
                            </p>
                            <p><strong>Correo:</strong> ${ticket.tic_correo_electronico}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-success">Personal</h6>
                            <p><strong>Solicitante:</strong> ${ticket.solicitante_completo || 'N/A'}</p>
                            <p><strong>Dependencia:</strong> ${ticket.dependencia_completa || 'N/A'}</p>
                            <p><strong>Encargado:</strong> ${ticket.encargado_nombre || 'Sin asignar'}</p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-warning">Descripción del Problema</h6>
                            <div class="bg-light p-3 rounded">
                                ${ticket.tic_comentario_falla}
                            </div>
                        </div>
                        ${ticket.tic_imagen ? `
                        <div class="col-12 mt-3">
                            <h6 class="text-info">Imagen Adjunta</h6>
                            <div class="text-center">
                                <img src="/proyecto_jjjc/public/uploads/tickets/${ticket.tic_imagen}" 
                                     class="img-fluid rounded shadow" style="max-height: 300px;">
                            </div>
                        </div>` : ''}
                    </div>
                `;

                const modal = new bootstrap.Modal(document.getElementById('modalDetalleTicket'));
                modal.show();
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los detalles del ticket'
            });
        }
    }

    // Mostrar imagen del ticket
    function mostrarImagenTicket(nombreImagen) {
        const urlImagen = `/proyecto_jjjc/public/uploads/tickets/${nombreImagen}`;
        
        Swal.fire({
            title: 'Imagen del Ticket',
            imageUrl: urlImagen,
            imageAlt: 'Imagen adjunta al ticket',
            showConfirmButton: true,
            confirmButtonText: 'Cerrar',
            imageWidth: 400,
            imageHeight: 300
        });
    }

    // Limpiar formulario
    function limpiarFormulario() {
        document.getElementById('formularioNuevoTicket').reset();
        document.getElementById('contenedorVistaPrevia').classList.add('d-none');
        document.getElementById('contadorCaracteres').textContent = '0';
        document.getElementById('descripcionProblema').classList.remove('border-success', 'border-warning', 'border-danger');
        
        Swal.fire({
            icon: 'info',
            title: 'Formulario Limpiado',
            text: 'Todos los campos han sido reiniciados',
            timer: 1500,
            showConfirmButton: false
        });
    }
    </script>
</body>