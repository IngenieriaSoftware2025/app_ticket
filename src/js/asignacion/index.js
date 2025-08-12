import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

const indicadorVista = document.getElementById('indicadorVista');
const textoIndicador = document.getElementById('textoIndicador');

let oficialesDisponibles = [];

const BuscarTicketsAsignacion = async () => {
    const url = `/app_ticket/asignacion/buscarAPI`;

    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            console.log('Tickets pendientes de asignación encontrados:', data);
            
            // DEBUG: Ver estructura de cada ticket
            if (data.length > 0) {
                console.log('Primer ticket estructura:', data[0]);
                console.log('Campo tic_comentario_falla:', data[0].tic_comentario_falla);
            }
            
            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(data).draw();
            }

            // Actualizar indicador
            actualizarIndicador(data.length);
        } else {
            await Swal.fire({
                position: "center",
                icon: "info",
                title: "Sin resultados",
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
            text: "No se pudo obtener los tickets pendientes de asignación. Verifique su conexión.",
            showConfirmButton: true,
        });
    }
}

const BuscarOficiales = async () => {
    const url = `/app_ticket/asignacion/buscarOficialesAPI`;

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
    const url = `/app_ticket/asignacion/asignarAPI`;
    
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
                text: `El ticket ${ticket_numero} ha sido asignado correctamente`,
                showConfirmButton: true,
            });
            
            // Recargar la tabla
            await BuscarTicketsAsignacion();
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
    indicadorVista.classList.remove('alert-success', 'alert-danger');
    indicadorVista.classList.add('alert-info');
    textoIndicador.innerHTML = `<i class="bi bi-person-plus me-2"></i>Mostrando tickets pendientes de asignación (${cantidad} registros)`;
}

const generarSelectOficiales = (ticketId) => {
    let options = '<option value="">Seleccionar oficial...</option>';
    
    oficialesDisponibles.forEach(oficial => {
        const nombreCompleto = `${oficial.per_nom1} ${oficial.per_ape1 || ''}`.trim();
        options += `<option value="${oficial.per_catalogo}">${oficial.per_grado || ''} ${nombreCompleto}</option>`;
    });
    
    return `<select class="form-select oficial-select" data-ticket="${ticketId}">${options}</select>`;
}

const datatable = new DataTable('#TableAsignacionTickets', {
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
            width: '5%',
            render: (data, type, row, meta) => {
                return meta.row + 1;
            }
        },
        { 
            title: 'Número Ticket', 
            data: 'tic_numero_ticket',
            width: '15%'
        },
        { 
            title: 'Aplicación', 
            data: 'aplicacion',
            width: '15%',
            render: (data, type, row, meta) => {
                return `<span class="badge bg-primary">${data}</span>`;
            }
        },
        {
            title: 'Oficial Encargado',
            data: null,
            width: '15%',
            render: (data, type, row, meta) => {
                return '<span class="text-muted">Pendiente</span>';
            }
        },
        { 
            title: 'Descripción Problema', 
            data: 'tic_comentario_falla',
            width: '25%',
            render: (data, type, row, meta) => {
                return data ? data.substring(0, 60) + '...' : '';
            }
        },
        {
            title: 'Asignar a',
            data: 'tic_id',
            width: '15%',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
                return generarSelectOficiales(data);
            }
        },
        {
            title: 'Acciones',
            data: 'tic_id',
            width: '10%',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
                return `
                    <div class='d-flex justify-content-center'>
                        <button class='btn btn-success btn-sm asignar' 
                            data-ticket-id='${data}'
                            data-ticket-numero='${row.tic_numero_ticket}'
                            title="Asignar Ticket">
                            <i class='bi bi-person-check me-1'></i>ASIGNAR
                        </button>
                    </div>
                `;
            }
        }
    ]
});

const mostrarDetalleTicket = (event) => {
    const ticket = JSON.parse(event.currentTarget.dataset.ticket);
    
    document.getElementById('detalleNumero').textContent = ticket.tic_numero_ticket;
    document.getElementById('detalleFecha').textContent = ticket.form_fecha_creacion;
    document.getElementById('detalleSolicitante').textContent = ticket.solicitante_nombre;
    document.getElementById('detalleDependencia').textContent = ticket.dependencia_nombre;
    document.getElementById('detalleAplicacion').textContent = ticket.aplicacion;
    document.getElementById('detalleDescripcion').textContent = ticket.tic_comentario_falla;
    
    const imagenContainer = document.getElementById('detalleImagenContainer');
    const imagen = document.getElementById('detalleImagen');
    
    // Manejar la imagen de forma segura
    if (ticket.tic_imagen && ticket.tic_imagen.trim() !== '' && ticket.tic_imagen !== 'null') {
        try {
            imagen.src = `/${ticket.tic_imagen}`;
            imagenContainer.style.display = 'block';
        } catch (error) {
            console.log('Error al cargar imagen:', error);
            imagenContainer.style.display = 'none';
        }
    } else {
        imagenContainer.style.display = 'none';
    }
}

const manejarAsignacion = (event) => {
    const ticketId = event.currentTarget.dataset.ticketId;
    const ticketNumero = event.currentTarget.dataset.ticketNumero;
    const selectOficial = document.querySelector(`.oficial-select[data-ticket="${ticketId}"]`);
    
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
    
    // Mostrar modal de confirmación
    document.getElementById('confirmTicketNumero').textContent = ticketNumero;
    document.getElementById('confirmOficialNombre').textContent = 
        `${oficialSeleccionado.per_grado || ''} ${oficialSeleccionado.per_nom1} ${oficialSeleccionado.per_ape1 || ''}`;
    
    // Configurar el botón de confirmación
    const btnConfirmar = document.getElementById('btnConfirmarAsignacion');
    btnConfirmar.onclick = async () => {
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarAsignacion'));
        modal.hide();
        
        // Procesar asignación
        await AsignarTicket(ticketNumero, oficialId);
    };
    
    // Mostrar modal
    const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarAsignacion'));
    modalConfirmar.show();
}

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', async function() {
    await BuscarOficiales();
    await BuscarTicketsAsignacion();
});

// Eventos del datatable
datatable.on('click', '.asignar', manejarAsignacion);