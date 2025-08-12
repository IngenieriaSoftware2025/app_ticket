import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

// Elementos del DOM
const btnRecibidos = document.getElementById('btnRecibidos');
const btnFinalizados = document.getElementById('btnFinalizados');
const btnRechazados = document.getElementById('btnRechazados');
const indicadorVista = document.getElementById('indicadorVista');
const textoIndicador = document.getElementById('textoIndicador');

// Estado actual de la vista
let vistaActual = 'recibidos';

// Función principal para buscar tickets
const BuscarTicketsHistorial = async () => {
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;

    let url = `/app_ticket/historial/buscarAPI?tipo=${vistaActual}`;
    
    // Agregar filtros de fecha si existen
    const params = new URLSearchParams();
    if (fechaInicio) params.append('fecha_inicio', fechaInicio);
    if (fechaFin) params.append('fecha_fin', fechaFin);
    if (params.toString()) url += '&' + params.toString();

    try {
        const respuesta = await fetch(url);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            // Actualizar tabla con los datos
            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(data).draw();
            }
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

// Cambiar vista y actualizar botones
const cambiarVista = async (nuevaVista) => {
    vistaActual = nuevaVista;
    actualizarBotones();
    await BuscarTicketsHistorial();
}

// Actualizar estilos de botones según la vista actual
const actualizarBotones = () => {
    // Limpiar todos los botones
    btnRecibidos.className = 'btn btn-outline-primary btn-lg px-4';
    btnFinalizados.className = 'btn btn-outline-success btn-lg px-4';
    btnRechazados.className = 'btn btn-outline-danger btn-lg px-4';

    // Activar el botón correspondiente
    switch(vistaActual) {
        case 'recibidos':
            btnRecibidos.className = 'btn btn-primary btn-lg px-4';
            break;
        case 'finalizados':
            btnFinalizados.className = 'btn btn-success btn-lg px-4';
            break;
        case 'rechazados':
            btnRechazados.className = 'btn btn-danger btn-lg px-4';
            break;
    }
}

// Actualizar texto del indicador
const actualizarIndicador = () => {
    const iconos = {
        'recibidos': 'bi-plus-circle',
        'finalizados': 'bi-check-circle', 
        'rechazados': 'bi-x-circle'
    };
    
    const clases = {
        'recibidos': 'alert-info',
        'finalizados': 'alert-success',
        'rechazados': 'alert-danger'
    };
    
    const textos = {
        'recibidos': 'Mostrando tickets recibidos (en proceso)',
        'finalizados': 'Mostrando tickets finalizados (completados)',
        'rechazados': 'Mostrando tickets rechazados'
    };

    // Limpiar clases anteriores
    indicadorVista.className = `alert text-center ${clases[vistaActual]}`;
    
    // Actualizar contenido
    textoIndicador.innerHTML = `<i class="${iconos[vistaActual]} me-2"></i>${textos[vistaActual]}`;
}

// Mostrar detalles del ticket en modal
const mostrarDetalleTicket = (event) => {
    const ticket = JSON.parse(event.currentTarget.dataset.ticket);
    
    document.getElementById('detalleNumero').textContent = ticket.form_tick_num;
    document.getElementById('detalleEstado').textContent = ticket.estado_descripcion;
    document.getElementById('detalleFecha').textContent = ticket.form_fecha_creacion;
    document.getElementById('detalleSolicitante').textContent = ticket.solicitante_nombre;
    document.getElementById('detalleEmail').textContent = ticket.tic_correo_electronico;
    document.getElementById('detalleDependencia').textContent = ticket.dependencia_nombre;
    document.getElementById('detalleDescripcion').textContent = ticket.tic_comentario_falla;
    
    // Manejo de imagen
    const imagenContainer = document.getElementById('detalleImagenContainer');
    const imagen = document.getElementById('detalleImagen');
    
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

// Configuración de DataTable
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
            render: (data, type, row, meta) => meta.row + 1
        },
        { 
            title: 'Número Ticket', 
            data: 'form_tick_num',
            width: '18%'
        },
        { 
            title: 'Solicitante', 
            data: 'solicitante_nombre',
            width: '22%'
        },
        { 
            title: 'Dependencia', 
            data: 'dependencia_nombre',
            width: '22%',
            render: (data, type, row, meta) => {
                return data ? data.substring(0, 35) + '...' : '';
            }
        },
        {
            title: 'Estado',
            data: 'estado_descripcion',
            width: '12%',
            render: (data, type, row) => {
                const badgeClasses = {
                    'RECIBIDO': 'bg-info',
                    'EN PROCESO': 'bg-primary', 
                    'FINALIZADO': 'bg-success',
                    'RECHAZADO': 'bg-danger'
                };
                
                const badgeClass = badgeClasses[data] || 'bg-secondary';
                return `<span class="badge ${badgeClass}">${data}</span>`;
            }
        },
        { 
            title: 'Fecha Creación', 
            data: 'form_fecha_creacion',
            width: '15%'
        },
        {
            title: 'Acciones',
            data: 'tic_id',
            width: '8%',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
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
    ]
});

// Event Listeners
document.getElementById('filtroFechaInicio').addEventListener('change', BuscarTicketsHistorial);
document.getElementById('filtroFechaFin').addEventListener('change', BuscarTicketsHistorial);

btnRecibidos.addEventListener('click', () => cambiarVista('recibidos'));
btnFinalizados.addEventListener('click', () => cambiarVista('finalizados'));
btnRechazados.addEventListener('click', () => cambiarVista('rechazados'));

datatable.on('click', '.ver', mostrarDetalleTicket);

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    vistaActual = 'recibidos';
    actualizarIndicador();
    BuscarTicketsHistorial();
});