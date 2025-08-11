import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

const btnCreados = document.getElementById('btnCreados');
const btnFinalizados = document.getElementById('btnFinalizados');
const indicadorVista = document.getElementById('indicadorVista');
const textoIndicador = document.getElementById('textoIndicador');

let vistaActual = 'creados'; // Por defecto mostrar creados

const BuscarTicketsHistorial = async () => {
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;

    let url = `/app_ticket/historial/buscarAPI?tipo=${vistaActual}`;
    const params = new URLSearchParams();

    if (fechaInicio) params.append('fecha_inicio', fechaInicio);
    if (fechaFin) params.append('fecha_fin', fechaFin);

    if (params.toString()) {
        url += '&' + params.toString();
    }

    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            console.log('Tickets del historial encontrados:', data);
            
            // NO organizar por estados, mostrar lista simple
            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(data).draw();
            }

            // Actualizar indicador
            actualizarIndicador();
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
            text: "No se pudo obtener el historial de tickets. Verifique su conexión.",
            showConfirmButton: true,
        });
    }
}

const BuscarTicketsCreados = async () => {
    vistaActual = 'creados';
    
    // Cambiar estado de botones
    btnCreados.classList.remove('btn-outline-primary');
    btnCreados.classList.add('btn-primary');
    btnFinalizados.classList.remove('btn-success');
    btnFinalizados.classList.add('btn-outline-success');

    await BuscarTicketsHistorial();
}

const BuscarTicketsFinalizados = async () => {
    vistaActual = 'finalizados';
    
    // Cambiar estado de botones
    btnFinalizados.classList.remove('btn-outline-success');
    btnFinalizados.classList.add('btn-success');
    btnCreados.classList.remove('btn-primary');
    btnCreados.classList.add('btn-outline-primary');

    await BuscarTicketsHistorial();
}

const actualizarIndicador = () => {
    if (vistaActual === 'creados') {
        indicadorVista.classList.remove('alert-success');
        indicadorVista.classList.add('alert-info');
        textoIndicador.innerHTML = '<i class="bi bi-plus-circle me-2"></i>Mostrando tickets creados (en proceso)';
    } else {
        indicadorVista.classList.remove('alert-info');
        indicadorVista.classList.add('alert-success');
        textoIndicador.innerHTML = '<i class="bi bi-check-circle me-2"></i>Mostrando tickets finalizados (completados)';
    }
}

const datatable = new DataTable('#TableHistorialTickets', {
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
            width: '15%'
        },
        { 
            title: 'Solicitante', 
            data: 'solicitante_nombre',
            width: '18%'
        },
        { 
            title: 'Técnico Encargado', 
            data: 'encargado_nombre',
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
            title: 'Estado',
            data: 'estado_descripcion',
            width: '12%',
            render: (data, type, row) => {
                let badgeClass = 'bg-secondary';
                switch(data) {
                    case 'CREADO': badgeClass = 'bg-primary'; break;
                    case 'RECIBIDO': badgeClass = 'bg-info'; break;
                    case 'PENDIENTE ASIGNACION': badgeClass = 'bg-warning'; break;
                    case 'ASIGNADO': badgeClass = 'bg-success'; break;
                    case 'EN PROCESO': badgeClass = 'bg-primary'; break;
                    case 'EN ESPERA REQUERIMIENTOS': badgeClass = 'bg-warning'; break;
                    case 'RESUELTO': badgeClass = 'bg-success'; break;
                    case 'CERRADO': badgeClass = 'bg-dark'; break;
                }
                return `<span class="badge ${badgeClass}">${data}</span>`;
            }
        },
        { 
            title: 'Fecha Creación', 
            data: 'form_fecha_creacion',
            width: '12%'
        },
        {
            title: 'Acciones',
            data: 'tic_id',
            width: '7%',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
                // Solo botón de ver detalles para el historial
                return `
                    <div class='d-flex justify-content-center'>
                        <button class='btn btn-info btn-sm ver' 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalDetalleTicket"
                            data-ticket='${JSON.stringify(row)}'
                            title="Ver Detalles">
                            <i class='bi bi-eye'></i>
                        </button>
                    </div>
                `;
            }
        }
    ],
    rowCallback: function(row, data) {
        // Sin separadores, tabla normal
    }
});

const mostrarDetalleTicket = (event) => {
    const ticket = JSON.parse(event.currentTarget.dataset.ticket);
    
    document.getElementById('detalleNumero').textContent = ticket.form_tick_num;
    document.getElementById('detalleEstado').textContent = ticket.estado_descripcion;
    document.getElementById('detalleFecha').textContent = ticket.form_fecha_creacion;
    document.getElementById('detalleSolicitante').textContent = ticket.solicitante_nombre;
    document.getElementById('detalleEmail').textContent = ticket.tic_correo_electronico;
    document.getElementById('detalleDependencia').textContent = ticket.dependencia_nombre;
    document.getElementById('detalleDescripcion').textContent = ticket.tic_comentario_falla;
    
    const imagenContainer = document.getElementById('detalleImagenContainer');
    const imagen = document.getElementById('detalleImagen');
    
    if (ticket.tic_imagen && ticket.tic_imagen.trim() !== '') {
        imagen.src = `/${ticket.tic_imagen}`;
        imagenContainer.style.display = 'block';
    } else {
        imagenContainer.style.display = 'none';
    }
}

// Eventos para filtros
document.getElementById('filtroFechaInicio').addEventListener('change', BuscarTicketsHistorial);
document.getElementById('filtroFechaFin').addEventListener('change', BuscarTicketsHistorial);

// Eventos para botones de navegación
btnCreados.addEventListener('click', BuscarTicketsCreados);
btnFinalizados.addEventListener('click', BuscarTicketsFinalizados);

// Cargar tickets creados automáticamente al inicio
document.addEventListener('DOMContentLoaded', function() {
    vistaActual = 'creados';
    actualizarIndicador();
    BuscarTicketsHistorial();
});

// Eventos del datatable
datatable.on('click', '.ver', mostrarDetalleTicket);