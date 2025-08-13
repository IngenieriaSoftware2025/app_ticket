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
let datatable;

// Función principal para buscar tickets
const BuscarTicketsHistorial = async () => {
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;

    let url = `/app_ticket/historial/buscarAPI?tipo=${vistaActual}`;
    
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

const cambiarVista = async (nuevaVista) => {
    vistaActual = nuevaVista;
    actualizarBotones();
    await BuscarTicketsHistorial();
}

const actualizarBotones = () => {
    btnRecibidos.className = 'btn btn-outline-primary btn-lg px-4';
    btnFinalizados.className = 'btn btn-outline-success btn-lg px-4';
    btnRechazados.className = 'btn btn-outline-danger btn-lg px-4';

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

    indicadorVista.className = `alert text-center ${clases[vistaActual]}`;
    textoIndicador.innerHTML = `<i class="${iconos[vistaActual]} me-2"></i>${textos[vistaActual]}`;
}

const mostrarDetalleTicket = (ticket) => {
    try {
        console.log('Mostrando detalles del ticket:', ticket);
        
        document.getElementById('detalleNumero').textContent = ticket.form_tick_num || 'N/A';
        document.getElementById('detalleEstado').textContent = ticket.estado_descripcion || 'N/A';
        document.getElementById('detalleFecha').textContent = ticket.form_fecha_creacion || 'N/A';
        document.getElementById('detalleSolicitante').textContent = ticket.solicitante_nombre || 'N/A';
        document.getElementById('detalleEmail').textContent = ticket.tic_correo_electronico || 'N/A';
        document.getElementById('detalleDependencia').textContent = ticket.dependencia_nombre || 'N/A';
        
        console.log('Descripción del ticket:', ticket.tic_comentario_falla);
        console.log('Objeto ticket completo:', ticket);
        
        let descripcion = 'No se ha proporcionado una descripción para este ticket';
        
        if (ticket.tic_comentario_falla) {
            const desc = ticket.tic_comentario_falla.toString().trim();
            if (desc !== '' && desc !== 'null' && desc !== 'undefined') {
                descripcion = desc;
            }
        }
        
        document.getElementById('detalleDescripcion').textContent = descripcion;
        
        const imagenContainer = document.getElementById('detalleImagenContainer');
        const imagen = document.getElementById('detalleImagen');
        
        console.log('Ruta de imagen en ticket:', ticket.tic_imagen);
        
        if (ticket.tic_imagen) {
            const imgPath = ticket.tic_imagen.toString().trim();
            
            if (imgPath !== '' && imgPath !== 'null' && imgPath !== 'undefined') {
                try {
                    
                    const rutasPosibles = [
                        `/uploads/tickets/2025/${imgPath}`, 
                        `/public/uploads/tickets/2025/${imgPath}`, 
                        `/app_ticket/uploads/tickets/2025/${imgPath}`,
                        `/uploads/${imgPath}`,
                        `/public/uploads/${imgPath}`,
                        `/app_ticket/uploads/${imgPath}`,
                        `/app_ticket/public/uploads/${imgPath}`,
                        `/${imgPath}` 
                    ];
                    
                    let rutaActual = 0;
                    
                    const probarRuta = () => {
                        if (rutaActual < rutasPosibles.length) {
                            const rutaAProbar = rutasPosibles[rutaActual];
                            console.log(`Probando ruta ${rutaActual + 1}/${rutasPosibles.length}:`, rutaAProbar);
                            
                            imagen.src = rutaAProbar;
                            rutaActual++;
                        } else {
                            console.log('❌ No se pudo cargar la imagen con ninguna ruta');
                            imagenContainer.style.display = 'none';
                        }
                    };
                    
                    imagen.onload = () => {
                        console.log('✅ Imagen cargada correctamente desde:', imagen.src);
                        imagenContainer.style.display = 'block';
                    };
                    
                    imagen.onerror = probarRuta;
                    
                    // Iniciar la primera prueba
                    probarRuta();
                    
                } catch (error) {
                    console.log('Error al procesar imagen:', error);
                    imagenContainer.style.display = 'none';
                }
            } else {
                console.log('📷 Campo de imagen vacío o null');
                imagenContainer.style.display = 'none';
            }
        } else {
            console.log('📷 No hay campo de imagen en el ticket');
            imagenContainer.style.display = 'none';
        }
        
        const modal = document.getElementById('modalDetalleTicket');
        modal.style.display = 'block';
        modal.classList.add('show');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        modal.removeAttribute('aria-hidden');
        modal.setAttribute('tabindex', '-1');
        
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
        
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        
        console.log('Modal abierto correctamente');
        
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

const cerrarModal = () => {
    const modal = document.getElementById('modalDetalleTicket');
    
    modal.style.display = 'none';
    modal.classList.remove('show');
    modal.removeAttribute('aria-modal');
    modal.removeAttribute('role');
    modal.setAttribute('aria-hidden', 'true');
    modal.setAttribute('tabindex', '-1');
    
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    
    console.log('Modal cerrado correctamente');
}

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

document.addEventListener('DOMContentLoaded', function() {
    inicializarDataTable();
    
    document.getElementById('filtroFechaInicio').addEventListener('change', BuscarTicketsHistorial);
    document.getElementById('filtroFechaFin').addEventListener('change', BuscarTicketsHistorial);

    btnRecibidos.addEventListener('click', () => cambiarVista('recibidos'));
    btnFinalizados.addEventListener('click', () => cambiarVista('finalizados'));
    btnRechazados.addEventListener('click', () => cambiarVista('rechazados'));

    document.addEventListener('click', function(event) {
        if (event.target.closest('.ver-detalle')) {
            event.preventDefault();
            
            const button = event.target.closest('.ver-detalle');
            const rowIndex = parseInt(button.getAttribute('data-row-index'));
            
            console.log('Índice de fila:', rowIndex);
            
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
        
        if (event.target.closest('.btn-close') || event.target.closest('[data-bs-dismiss="modal"]')) {
            event.preventDefault();
            cerrarModal();
        }
    });

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('modalDetalleTicket');
            if (modal.classList.contains('show')) {
                cerrarModal();
            }
        }
    });

    vistaActual = 'recibidos';
    actualizarIndicador();
    actualizarBotones();
    BuscarTicketsHistorial();
});