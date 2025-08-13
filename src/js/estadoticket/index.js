import { Dropdown, Modal } from "bootstrap";
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

let estadoActual = 1; 
let oficialesDisponibles = [];
let ticketSeleccionadoAsignacion = null;

const BuscarEstadoTickets = async () => {
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;

    let url = `/app_ticket/estado-tickets/buscarAPI?`;
    const params = new URLSearchParams();

    if (fechaInicio) params.append('fecha_inicio', fechaInicio);
    if (fechaFin) params.append('fecha_fin', fechaFin);
    
    params.append('estado', estadoActual);

    url += params.toString();

    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            console.log('Tickets encontrados:', data);
            
            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(data).draw();
            }
            
            actualizarIndicador(data.length);
        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Error",
                text: mensaje,
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.log(error);
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo obtener los tickets. Verifique su conexión.",
            showConfirmButton: true,
        });
    }
}

const BuscarOficiales = async () => {
    const url = `/app_ticket/estado-tickets/buscarOficialesAPI`;

    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            console.log('Oficiales disponibles encontrados:', data);
            oficialesDisponibles = data;
        } else {
            console.log('Error al obtener oficiales:', mensaje);
        }

    } catch (error) {
        console.log('Error de conexión al obtener oficiales:', error);
    }
}

const AsignarTicket = async (ticketNumero, oficialId) => {
    const url = `/app_ticket/estado-tickets/asignarAPI`;
    
    const formData = new FormData();
    formData.append('ticket_numero', ticketNumero);
    formData.append('oficial_id', oficialId);

    const config = {
        method: 'POST',
        body: formData
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, ticket_numero } = datos;

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "¡Ticket Asignado!",
                text: `El ticket ${ticket_numero} ha sido asignado correctamente y cambiado a EN PROCESO`,
                showConfirmButton: true,
            });
            
            // Cerrar modales
            const modalAsignar = Modal.getInstance(document.getElementById('modalAsignarTicket'));
            const modalConfirmar = Modal.getInstance(document.getElementById('modalConfirmarAsignacion'));
            if (modalAsignar) modalAsignar.hide();
            if (modalConfirmar) modalConfirmar.hide();
            
            // Actualizar vista
            await BuscarEstadoTickets();
        } else {
            await Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al asignar",
                text: mensaje,
                showConfirmButton: true,
            });
        }

    } catch (error) {
        console.log(error);
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: "No se pudo asignar el ticket. Verifique su conexión.",
            showConfirmButton: true,
        });
    }
}

const actualizarIndicador = (cantidad) => {
    const indicador = document.getElementById('textoIndicador');
    const estadosTexto = {
        0: 'rechazados',
        1: 'recibidos',
        2: 'en proceso'
    };
    
    indicador.textContent = `Mostrando tickets ${estadosTexto[estadoActual]} (${cantidad} registros)`;
}

const cambiarEstado = (nuevoEstado) => {
    estadoActual = nuevoEstado;
    
    document.querySelectorAll('.estado-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.estado == nuevoEstado) {
            btn.classList.add('active');
            if (nuevoEstado == 1) {
                btn.className = 'btn btn-primary btn-lg mx-2 estado-btn active';
            } else if (nuevoEstado == 2) {
                btn.className = 'btn btn-primary btn-lg mx-2 estado-btn active';
            } else if (nuevoEstado == 0) {
                btn.className = 'btn btn-danger btn-lg mx-2 estado-btn active';
            }
        } else {
            if (btn.dataset.estado == 1) {
                btn.className = 'btn btn-outline-primary btn-lg mx-2 estado-btn';
            } else if (btn.dataset.estado == 2) {
                btn.className = 'btn btn-outline-primary btn-lg mx-2 estado-btn';
            } else if (btn.dataset.estado == 0) {
                btn.className = 'btn btn-outline-danger btn-lg mx-2 estado-btn';
            }
        }
    });
    
    BuscarEstadoTickets();
}

const revertirTicket = async (e) => {
    const ticketNumero = e.currentTarget.dataset.ticket;

    const AlertaConfirmarRevertir = await Swal.fire({
        position: "center",
        icon: "warning",
        title: "¿Revertir ticket rechazado?",
        html: `
            <div class="mb-3">
                <strong>Ticket:</strong> ${ticketNumero}<br>
                <span class="text-warning">El ticket volverá a estado RECIBIDO y estará disponible para asignación</span>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Sí, Revertir',
        confirmButtonColor: '#ffc107',
        cancelButtonText: 'No, Cancelar',
        showCancelButton: true
    });

    if (AlertaConfirmarRevertir.isConfirmed) {
        const body = new FormData();
        body.append('ticket_numero', ticketNumero);
        
        const url = `/app_ticket/estado-tickets/revertir`;
        const config = {
            method: 'POST',
            body
        }

        try {
            const consulta = await fetch(url, config);
            const respuesta = await consulta.json();
            const { codigo, mensaje } = respuesta;

            if (codigo == 1) {
                await Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Ticket Revertido",
                    text: mensaje,
                    showConfirmButton: true,
                });
                
                BuscarEstadoTickets();
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
            console.log(error);
            await Swal.fire({
                position: "center",
                icon: "error",
                title: "Error",
                text: "Error de conexión. Intente nuevamente.",
                showConfirmButton: true,
            });
        }
    }
}

const rechazarTicket = async (e) => {
    const ticketNumero = e.currentTarget.dataset.ticket;

    const AlertaConfirmarRechazar = await Swal.fire({
        position: "center",
        icon: "warning",
        title: "¿Rechazar ticket?",
        html: `
            <div class="mb-3">
                <strong>Ticket:</strong> ${ticketNumero}<br>
                <span class="text-danger">El ticket será marcado como rechazado y no aparecerá en la lista</span>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Sí, Rechazar',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'No, Cancelar',
        showCancelButton: true
    });

    if (AlertaConfirmarRechazar.isConfirmed) {
        const body = new FormData();
        body.append('ticket_numero', ticketNumero);
        
        const url = `/app_ticket/estado-tickets/rechazar`;
        const config = {
            method: 'POST',
            body
        }

        try {
            const consulta = await fetch(url, config);
            const respuesta = await consulta.json();
            const { codigo, mensaje } = respuesta;

            if (codigo == 1) {
                await Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Ticket Rechazado",
                    text: mensaje,
                    showConfirmButton: true,
                });
                
                BuscarEstadoTickets();
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
            console.log(error);
            await Swal.fire({
                position: "center",
                icon: "error",
                title: "Error",
                text: "Error de conexión. Intente nuevamente.",
                showConfirmButton: true,
            });
        }
    }
}

const mostrarModalAsignacion = async (ticketData) => {
    ticketSeleccionadoAsignacion = ticketData;
    
    // Actualizar información del ticket seleccionado
    document.getElementById('ticketSeleccionado').textContent = ticketData.form_tick_num;
    
    // Crear tabla con el ticket seleccionado
    const datatableModal = new DataTable('#TableAsignacionModal', {
        destroy: true,
        dom: 't',
        language: lenguaje,
        data: [ticketData],
        ordering: false,
        columns: [
            {
                title: 'No.',
                data: null,
                width: '5%',
                render: () => 1
            },
            { 
                title: 'Número Ticket', 
                data: 'form_tick_num',
                width: '15%'
            },
            { 
                title: 'Aplicación', 
                data: 'aplicacion',
                width: '15%',
                render: (data) => {
                    return `<span class="badge bg-primary">${data}</span>`;
                }
            },
            {
                title: 'Oficial Encargado',
                data: null,
                width: '15%',
                render: () => '<span class="text-muted">Pendiente</span>'
            },
            { 
                title: 'Descripción Problema', 
                data: 'tic_comentario_falla',
                width: '25%',
                render: (data) => {
                    return data ? data.substring(0, 60) + '...' : '';
                }
            },
            {
                title: 'Asignar a',
                data: 'form_tick_num',
                width: '15%',
                searchable: false,
                orderable: false,
                render: (data) => {
                    return generarSelectOficiales(data);
                }
            },
            {
                title: 'Acciones',
                data: 'form_tick_num',
                width: '10%',
                searchable: false,
                orderable: false,
                render: (data) => {
                    return `
                        <div class='d-flex justify-content-center'>
                            <button class='btn btn-success btn-sm asignar-modal' 
                                data-ticket-numero='${data}'
                                title="Asignar Ticket">
                                <i class='bi bi-person-check me-1'></i>ASIGNAR
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // Manejar evento de asignación en el modal
    datatableModal.on('click', '.asignar-modal', manejarAsignacionModal);
    
    // Mostrar modal
    const modal = new Modal(document.getElementById('modalAsignarTicket'));
    modal.show();
}

const generarSelectOficiales = (ticketId) => {
    let options = '<option value="">Seleccionar oficial...</option>';
    
    oficialesDisponibles.forEach(oficial => {
        const nombreCompleto = `${oficial.per_nom1} ${oficial.per_ape1 || ''}`.trim();
        options += `<option value="${oficial.per_catalogo}">${oficial.per_grado || ''} ${nombreCompleto}</option>`;
    });
    
    return `<select class="form-select oficial-select" data-ticket="${ticketId}">${options}</select>`;
}

const manejarAsignacionModal = (event) => {
    const ticketNumero = event.currentTarget.dataset.ticketNumero;
    const selectOficial = document.querySelector(`.oficial-select[data-ticket="${ticketNumero}"]`);
    
    if (!selectOficial || !selectOficial.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Seleccione un oficial',
            text: 'Debe seleccionar un oficial antes de asignar el ticket',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const oficialId = selectOficial.value;
    const oficialSeleccionado = oficialesDisponibles.find(o => o.per_catalogo == oficialId);
    
    if (!oficialSeleccionado) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró información del oficial seleccionado',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    document.getElementById('confirmTicketNumero').textContent = ticketNumero;
    document.getElementById('confirmOficialNombre').textContent = 
        `${oficialSeleccionado.per_grado || ''} ${oficialSeleccionado.per_nom1} ${oficialSeleccionado.per_ape1 || ''}`;
    
    const btnConfirmar = document.getElementById('btnConfirmarAsignacion');
    btnConfirmar.onclick = async () => {
        await AsignarTicket(ticketNumero, oficialId);
    };
    
    const modalConfirmar = new Modal(document.getElementById('modalConfirmarAsignacion'));
    modalConfirmar.show();
}

const datatable = new DataTable('#TableEstadoTickets', {
    dom: `
        <"row mt-3 justify-content-between" 
            <"col" l> 
            <"col" B> 
            <"col-3" f>
        >
        t
        <"row mt-3 justify-content-between" 
            <"col-md-3 d-flex align-items-center" i> 
            <"col-md-8 d-flex justify-content-end" p>
        >
    `,
    language: lenguaje,
    data: [],
    ordering: false,
    columns: [
        {
            title: 'No.',
            data: null,
            width: '3%',
            render: (data, type, row, meta) => {
                return meta.row + 1; 
            }
        },
        { 
            title: 'Número Ticket', 
            data: 'form_tick_num',
            width: '12%'
        },
        { 
            title: 'Solicitante', 
            data: 'solicitante_nombre',
            width: '15%'
        },
        { 
            title: 'Dependencia', 
            data: 'dependencia_nombre',
            width: '18%',
            render: (data, type, row, meta) => {
                return data ? data.substring(0, 30) + '...' : '';
            }
        },
        { 
            title: 'Aplicación', 
            data: 'aplicacion',
            width: '12%',
            render: (data, type, row, meta) => {
                return `<span class="badge bg-info">${data}</span>`;
            }
        },
        {
            title: 'Estado',
            data: 'estado_descripcion',
            width: '10%',
            render: (data, type, row) => {
                let badgeClass = 'bg-secondary';
                switch(data) {
                    case 'RECIBIDO': badgeClass = 'bg-info'; break;
                    case 'EN PROCESO': badgeClass = 'bg-primary'; break;
                    case 'RECHAZADO': badgeClass = 'bg-danger'; break;
                }
                return `<span class="badge ${badgeClass}">${data}</span>`;
            }
        },
        { 
            title: 'Fecha Creación', 
            data: 'form_fecha_creacion',
            width: '10%'
        },
        {
            title: 'Acciones',
            data: 'tic_id',
            width: '20%',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
                const estadoId = row.estado_ticket;
                const ticketNumero = row.form_tick_num;
                
                let botones = '';

                // Botón Ver Detalles (siempre disponible)
                botones += `
                    <button class='btn btn-info btn-sm ver mx-1' 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalDetalleTicket"
                        data-ticket='${JSON.stringify(row)}'
                        title="Ver Detalles">
                        <i class='bi bi-eye'></i>
                    </button>
                `;

                // Solo para tickets RECIBIDOS (estado 1)
                if (estadoId == 1) {
                    // Botón Asignar a
                    botones += `
                        <button class='btn btn-success btn-sm asignar mx-1' 
                            data-ticket='${JSON.stringify(row)}'
                            title="Asignar a Oficial">
                            <i class="bi bi-person-plus"></i> Asignar a
                        </button>
                    `;

                    // Botón Rechazar
                    botones += `
                        <button class='btn btn-danger btn-sm rechazar mx-1' 
                            data-ticket="${ticketNumero}"
                            title="Rechazar">
                            <i class="bi bi-x-circle"></i> Rechazar
                        </button>
                    `;
                }

                // Solo para tickets RECHAZADOS (estado 0)
                if (estadoId == 0) {
                    // Botón Revertir
                    botones += `
                        <button class='btn btn-warning btn-sm revertir mx-1' 
                            data-ticket="${ticketNumero}"
                            title="Revertir a Recibido">
                            <i class="bi bi-arrow-counterclockwise"></i> Revertir
                        </button>
                    `;
                }

                return `<div class='d-flex justify-content-center flex-wrap'>${botones}</div>`;
            }
        }
    ]
});

const mostrarDetalleTicket = (event) => {
    const ticket = JSON.parse(event.currentTarget.dataset.ticket);
    
    document.getElementById('detalleNumero').textContent = ticket.form_tick_num;
    document.getElementById('detalleEstado').textContent = ticket.estado_descripcion;
    document.getElementById('detalleFecha').textContent = ticket.form_fecha_creacion;
    document.getElementById('detalleSolicitante').textContent = ticket.solicitante_nombre;
    document.getElementById('detalleEmail').textContent = ticket.tic_correo_electronico;
    document.getElementById('detalleDependencia').textContent = ticket.dependencia_nombre;
    document.getElementById('detalleAplicacion').textContent = ticket.aplicacion;
    document.getElementById('detalleEncargado').textContent = ticket.encargado_nombre || 'Sin asignar';
    document.getElementById('detalleDescripcion').textContent = ticket.tic_comentario_falla;
    
    // Mostrar/ocultar sección de rechazado
    const seccionRechazado = document.getElementById('detalleEstadoRechazado');
    if (ticket.estado_ticket == 0) {
        seccionRechazado.style.display = 'block';
    } else {
        seccionRechazado.style.display = 'none';
    }
    
    const imagenContainer = document.getElementById('detalleImagenContainer');
    const imagen = document.getElementById('detalleImagen');
    
    if (ticket.tic_imagen && ticket.tic_imagen.trim() !== '') {
        imagen.src = `/${ticket.tic_imagen}`;
        imagenContainer.style.display = 'block';
    } else {
        imagenContainer.style.display = 'none';
    }
}

const manejarAsignacion = (event) => {
    const ticketData = JSON.parse(event.currentTarget.dataset.ticket);
    mostrarModalAsignacion(ticketData);
}

// Event Listeners
document.getElementById('filtroFechaInicio').addEventListener('change', BuscarEstadoTickets);
document.getElementById('filtroFechaFin').addEventListener('change', BuscarEstadoTickets);

document.querySelectorAll('.estado-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const nuevoEstado = parseInt(e.currentTarget.dataset.estado);
        cambiarEstado(nuevoEstado);
    });
});

document.addEventListener('DOMContentLoaded', async function() {
    await BuscarOficiales();
    await BuscarEstadoTickets();
});

// Event Handlers de DataTable
datatable.on('click', '.ver', mostrarDetalleTicket);
datatable.on('click', '.asignar', manejarAsignacion);
datatable.on('click', '.rechazar', rechazarTicket);
datatable.on('click', '.revertir', revertirTicket);