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
let datatable; // Declarar la variable globalmente

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
        console.log('Llamando a URL:', url);
        const respuesta = await fetch(url);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP error! status: ${respuesta.status}`);
        }
        
        const datos = await respuesta.json();
        console.log('Datos recibidos:', datos);
        
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            // Actualizar tabla con los datos
            if (datatable) {
                datatable.clear().draw();
                if (data && data.length > 0) {
                    datatable.rows.add(data).draw();
                }
            }
            actualizarIndicador();
            
            console.log(`Se cargaron ${data ? data.length : 0} tickets`);
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
        console.error('Error completo:', error);
        await Swal.fire({
            position: "center",
            icon: "error",
            title: "Error de conexión",
            text: `No se pudo obtener el historial de tickets: ${error.message}`,
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

// CORREGIR: Mostrar detalles del ticket en modal - SIN BOOTSTRAP JS
const mostrarDetalleTicket = (ticket) => {
    try {
        console.log('Mostrando detalles del ticket:', ticket);
        
        // Rellenar el modal con los datos del ticket
        document.getElementById('detalleNumero').textContent = ticket.form_tick_num || 'N/A';
        document.getElementById('detalleEstado').textContent = ticket.estado_descripcion || 'N/A';
        document.getElementById('detalleFecha').textContent = ticket.form_fecha_creacion || 'N/A';
        document.getElementById('detalleSolicitante').textContent = ticket.solicitante_nombre || 'N/A';
        document.getElementById('detalleEmail').textContent = ticket.tic_correo_electronico || 'N/A';
        document.getElementById('detalleDependencia').textContent = ticket.dependencia_nombre || 'N/A';
        document.getElementById('detalleDescripcion').textContent = ticket.tic_comentario_falla || 'Sin descripción';
        
        // Manejo de imagen
        const imagenContainer = document.getElementById('detalleImagenContainer');
        const imagen = document.getElementById('detalleImagen');
        
        if (ticket.tic_imagen && 
            ticket.tic_imagen.trim() !== '' && 
            ticket.tic_imagen !== 'null' && 
            ticket.tic_imagen !== null) {
            try {
                const rutaImagen = ticket.tic_imagen.startsWith('/') ? ticket.tic_imagen : `/${ticket.tic_imagen}`;
                imagen.src = rutaImagen;
                imagen.onerror = () => {
                    console.log('Error al cargar imagen:', rutaImagen);
                    imagenContainer.style.display = 'none';
                };
                imagen.onload = () => {
                    imagenContainer.style.display = 'block';
                };
            } catch (error) {
                console.log('Error al procesar imagen:', error);
                imagenContainer.style.display = 'none';
            }
        } else {
            imagenContainer.style.display = 'none';
        }
        
        // MOSTRAR MODAL SIN BOOTSTRAP JS - usando atributos HTML
        const modal = document.getElementById('modalDetalleTicket');
        modal.style.display = 'block';
        modal.classList.add('show');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        
        // Agregar backdrop
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        
        // Agregar clase al body
        document.body.classList.add('modal-open');
        
    } catch (error) {
        console.error('Error al mostrar detalles del ticket:', error);
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error",
            text: "No se pudieron cargar los detalles del ticket",
            showConfirmButton: true,
        });
    }
}

// Función para cerrar modal manualmente
const cerrarModal = () => {
    const modal = document.getElementById('modalDetalleTicket');
    modal.style.display = 'none';
    modal.classList.remove('show');
    modal.removeAttribute('aria-modal');
    modal.removeAttribute('role');
    
    // Remover backdrop
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    
    // Remover clase del body
    document.body.classList.remove('modal-open');
}

// Configuración de DataTable
const inicializarDataTable = () => {
    datatable = new DataTable('#TableHistorialTickets', {
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
                    return data && data.length > 35 ? data.substring(0, 35) + '...' : (data || '');
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
                    const rowIndex = meta.row;
                    
                    return `
                        <div class='d-flex justify-content-center'>
                            <button class='btn btn-info btn-sm ver-detalle' 
                                data-row-index='${rowIndex}'
                                title="Ver Detalles">
                                <i class='bi bi-eye'></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    inicializarDataTable();
    
    // Event listeners para filtros
    document.getElementById('filtroFechaInicio').addEventListener('change', BuscarTicketsHistorial);
    document.getElementById('filtroFechaFin').addEventListener('change', BuscarTicketsHistorial);

    // Event listeners para botones
    btnRecibidos.addEventListener('click', () => cambiarVista('recibidos'));
    btnFinalizados.addEventListener('click', () => cambiarVista('finalizados'));
    btnRechazados.addEventListener('click', () => cambiarVista('rechazados'));

    // Event listener para botones de ver detalles
    document.addEventListener('click', function(event) {
        if (event.target.closest('.ver-detalle')) {
            event.preventDefault();
            
            const button = event.target.closest('.ver-detalle');
            const rowIndex = parseInt(button.getAttribute('data-row-index'));
            
            console.log('Índice de fila:', rowIndex);
            
            // Obtener los datos de la fila desde DataTable
            const rowData = datatable.row(rowIndex).data();
            
            console.log('Datos de la fila:', rowData);
            
            if (rowData) {
                mostrarDetalleTicket(rowData);
            } else {
                console.error('No se encontraron datos para la fila:', rowIndex);
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error",
                    text: "No se pudieron cargar los detalles del ticket",
                    showConfirmButton: true,
                });
            }
        }
        
        // Event listener para cerrar modal
        if (event.target.closest('.btn-close') || event.target.closest('[data-bs-dismiss="modal"]')) {
            event.preventDefault();
            cerrarModal();
        }
    });

    // Cerrar modal haciendo clic en el backdrop
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            cerrarModal();
        }
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('modalDetalleTicket');
            if (modal.classList.contains('show')) {
                cerrarModal();
            }
        }
    });

    // Inicializar vista
    vistaActual = 'recibidos';
    actualizarIndicador();
    actualizarBotones();
    BuscarTicketsHistorial();
});