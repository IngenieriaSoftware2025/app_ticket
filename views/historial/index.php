<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-primary rounded-4">
                <div class="card-body">
                    <div class="mb-4 text-center">
                        <h5 class="fw-bold text-secondary mb-2">¡Gestión de Tickets de Soporte!</h5>
                        <h3 class="fw-bold text-primary mb-0">HISTORIAL DE TICKETS</h3>
                    </div>

                    <!-- Botones de navegación -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="btn-group" role="group" aria-label="Navegación de historial">
                                <button type="button" class="btn btn-primary btn-lg px-4" id="btnRecibidos">
                                    <i class="bi bi-plus-circle me-2"></i>RECIBIDOS
                                </button>
                                <button type="button" class="btn btn-outline-success btn-lg px-4" id="btnFinalizados">
                                    <i class="bi bi-check-circle me-2"></i>FINALIZADOS
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-lg px-4" id="btnRechazados">
                                    <i class="bi bi-x-circle me-2"></i>RECHAZADOS
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="filtroFechaInicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="filtroFechaInicio">
                        </div>
                        <div class="col-md-6">
                            <label for="filtroFechaFin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="filtroFechaFin">
                        </div>
                    </div>

                    <!-- Indicador de vista actual -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info text-center" id="indicadorVista">
                                <i class="bi bi-info-circle me-2"></i>
                                <span id="textoIndicador">Mostrando tickets recibidos</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle rounded-3 overflow-hidden w-100" id="TableHistorialTickets" style="width: 100% !important;">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Número Ticket</th>
                                    <th>Solicitante</th>
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
<div class="modal fade" id="modalDetalleTicket" tabindex="-1" aria-labelledby="modalDetalleTicketLabel" aria-hidden="true" style="display: none;">
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
                        <p><strong>Número:</strong> <span id="detalleNumero">-</span></p>
                        <p><strong>Estado:</strong> <span id="detalleEstado">-</span></p>
                        <p><strong>Fecha Creación:</strong> <span id="detalleFecha">-</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Información del Solicitante</h6>
                        <p><strong>Nombre:</strong> <span id="detalleSolicitante">-</span></p>
                        <p><strong>Email:</strong> <span id="detalleEmail">-</span></p>
                        <p><strong>Dependencia:</strong> <span id="detalleDependencia">-</span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary">Descripción del Problema</h6>
                        <div class="bg-light p-3 rounded">
                            <p id="detalleDescripcion">Cargando...</p>
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

<script src="<?= asset('build/js/historial/index.js') ?>"></script>