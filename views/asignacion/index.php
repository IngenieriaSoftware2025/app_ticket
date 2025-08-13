<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-primary rounded-4">
                <div class="card-body">
                    <div class="mb-4 text-center">
                        <h5 class="fw-bold text-secondary mb-2">¡Gestión de Tickets de Soporte!</h5>
                        <h3 class="fw-bold text-primary mb-0">ASIGNACIÓN DE TICKETS</h3>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info text-center" id="indicadorVista">
                                <i class="bi bi-person-plus me-2"></i>
                                <span id="textoIndicador">Mostrando tickets pendientes de asignación</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered align-middle rounded-3 overflow-hidden w-100" id="TableAsignacionTickets" style="width: 100% !important;">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Número Ticket</th>
                                    <th>Aplicación</th>
                                    <th>Oficial Encargado</th>
                                    <th>Descripción Problema</th>
                                    <th>Asignar a</th>
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
                        <p><strong>Estado:</strong> <span id="detalleEstado">RECIBIDO</span></p>
                        <p><strong>Fecha Creación:</strong> <span id="detalleFecha"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Información del Solicitante</h6>
                        <p><strong>Nombre:</strong> <span id="detalleSolicitante"></span></p>
                        <p><strong>Dependencia:</strong> <span id="detalleDependencia"></span></p>
                        <p><strong>Aplicación:</strong> <span id="detalleAplicacion"></span></p>
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

<div class="modal fade" id="modalConfirmarAsignacion" tabindex="-1" aria-labelledby="modalConfirmarAsignacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalConfirmarAsignacionLabel">Confirmar Asignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="bi bi-person-check-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">¿Confirmar asignación?</h5>
                    <p class="mb-3">¿Está seguro de asignar el ticket <strong id="confirmTicketNumero"></strong> al oficial seleccionado?</p>
                    <div class="alert alert-info">
                        <strong>Oficial:</strong> <span id="confirmOficialNombre"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarAsignacion">
                    <i class="bi bi-check-circle me-2"></i>Sí, Asignar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/asignacion/index.js') ?>"></script>