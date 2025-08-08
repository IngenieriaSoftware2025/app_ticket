<div class="container py-5">
    <div class="row mb-5 justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body bg-gradient" style="background: linear-gradient(90deg, #f8fafc 60%, #e3f2fd 100%);">
                    <div class="mb-4 text-center">
                        <h5 class="fw-bold text-secondary mb-2">¡Gestión de Tickets de Soporte!</h5>
                        <h3 class="fw-bold text-primary mb-0">ESTADO DE TICKETS</h3>
                    </div>
                    <form id="formEstadoTicket" class="p-4 bg-white rounded-3 shadow-sm border">
                        <input type="hidden" id="tic_id" name="tic_id">
                        
                        <div class="row g-4 mb-3">
                            <div class="col-md-12">
                                <label for="tic_numero_ticket" class="form-label">Número de Ticket</label>
                                <select class="form-select form-select-lg" id="tic_numero_ticket" name="tic_numero_ticket" required>
                                    <option value="">Seleccione un ticket</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-3">
                            <div class="col-md-6">
                                <label for="tic_encargado" class="form-label">Técnico Encargado</label>
                                <select class="form-select form-select-lg" id="tic_encargado" name="tic_encargado" required>
                                    <option value="">Seleccione un técnico</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="estado_ticket" class="form-label">Estado del Ticket</label>
                                <select class="form-select form-select-lg" id="estado_ticket" name="estado_ticket" required>
                                    <option value="">Seleccione un estado</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-3">
                            <button class="btn btn-success btn-lg px-4 shadow" type="submit" id="BtnGuardar">
                                <i class="bi bi-save me-2"></i>Guardar
                            </button>
                            <button class="btn btn-warning btn-lg px-4 shadow d-none" type="button" id="BtnModificar">
                                <i class="bi bi-pencil-square me-2"></i>Modificar
                            </button>
                            <button class="btn btn-secondary btn-lg px-4 shadow" type="reset" id="BtnLimpiar">
                                <i class="bi bi-eraser me-2"></i>Limpiar
                            </button>
                            <button class="btn btn-primary btn-lg px-4 shadow" type="button" id="BtnBuscarTickets">
                                <i class="bi bi-search me-2"></i>Buscar Tickets
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-5" id="seccionTabla" style="display: none;">
        <div class="col-12 d-flex justify-content-center">
            <div class="card shadow-lg border-primary rounded-4" style="width: 95%;">
                <div class="card-body">
                    <h3 class="text-center text-primary mb-4">Tickets registrados en el sistema</h3>

                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="filtroEstado" class="form-label">Filtrar por Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtroFechaInicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="filtroFechaInicio">
                        </div>
                        <div class="col-md-4">
                            <label for="filtroFechaFin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="filtroFechaFin">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle rounded-3 overflow-hidden w-100" id="TableEstadoTickets" style="width: 100% !important;">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Número Ticket</th>
                                    <th>Solicitante</th>
                                    <th>Técnico Encargado</th>
                                    <th>Dependencia</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del ticket -->
<div class="modal fade" id="modalDetalleTicket" tabindex="-1" aria-labelledby="modalDetalleTicketLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleTicketLabel">Detalles del Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Información del Ticket</h6>
                        <p><strong>Número:</strong> <span id="detalleNumero"></span></p>
                        <p><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                        <p><strong>Fecha Creación:</strong> <span id="detalleFecha"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Información del Solicitante</h6>
                        <p><strong>Nombre:</strong> <span id="detalleSolicitante"></span></p>
                        <p><strong>Email:</strong> <span id="detalleEmail"></span></p>
                        <p><strong>Dependencia:</strong> <span id="detalleDependencia"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary">Descripción del Problema</h6>
                        <div class="bg-light p-3 rounded">
                            <p id="detalleDescripcion"></p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3" id="detalleImagenContainer" style="display: none;">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary">Imagen Adjunta</h6>
                        <img id="detalleImagen" class="img-fluid rounded" style="max-height: 300px;" alt="Imagen del problema">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/estadoticket/index.js') ?>"></script>