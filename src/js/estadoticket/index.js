import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

const FiltroEstado = document.getElementById('filtroEstado');

const cargarEstados = async () => {
    const url = `/app_ticket/estado-tickets/buscarEstadosAPI`;
    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            FiltroEstado.innerHTML = '<option value="">Todos los estados</option>';
            
            data.forEach(estado => {
                const optionFiltro = document.createElement('option');
                optionFiltro.value = estado.est_tic_id;
                optionFiltro.textContent = estado.est_tic_desc;
                FiltroEstado.appendChild(optionFiltro);
            });
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
    }
}

const organizarDatosPorEstado = (data) => {
    const estados = [
        { id: 1, nombre: 'CREADO', key: 'CREADO' },
        { id: 2, nombre: 'RECIBIDO', key: 'RECIBIDO' },
        { id: 3, nombre: 'PENDIENTE ASIGNACION', key: 'PENDIENTE ASIGNACION' },
        { id: 4, nombre: 'ASIGNADO', key: 'ASIGNADO' },
        { id: 5, nombre: 'EN PROCESO', key: 'EN PROCESO' },
        { id: 6, nombre: 'EN ESPERA REQUERIMIENTOS', key: 'EN ESPERA REQUERIMIENTOS' },
        { id: 7, nombre: 'RESUELTO', key: 'RESUELTO' },
        { id: 8, nombre: 'CERRADO', key: 'CERRADO' }
    ];
    
    let datosOrganizados = [];
    let contador = 1;
    
    estados.forEach(estado => {
        const ticketsEstado = data.filter(ticket => ticket.estado_descripcion === estado.key);
        
        if (ticketsEstado.length > 0) {
            datosOrganizados.push({
                esSeparador: true,
                estado: estado.key,
                nombre: estado.nombre,
                cantidad: ticketsEstado.length
            });
            
            ticketsEstado.forEach(ticket => {
                datosOrganizados.push({
                    ...ticket,
                    numeroConsecutivo: contador++,
                    esSeparador: false
                });
            });
        }
    });
    
    return datosOrganizados;
}

const BuscarEstadoTickets = async () => {
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;
    const estado = FiltroEstado.value;

    let url = `/app_ticket/estado-tickets/buscarAPI?`;
    const params = new URLSearchParams();

    if (fechaInicio) params.append('fecha_inicio', fechaInicio);
    if (fechaFin) params.append('fecha_fin', fechaFin);
    if (estado) params.append('estado', estado);

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
            
            const datosOrganizados = organizarDatosPorEstado(data);

            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(datosOrganizados).draw();
            }
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

const cambiarEstadoTicket = async (e) => {
    const ticketNumero = e.currentTarget.dataset.ticket;
    const estadoActual = parseInt(e.currentTarget.dataset.estado);
    const estadoActualNombre = e.currentTarget.dataset.estadoNombre;

    const mapeoSiguienteEstado = {
        1: { id: 2, nombre: 'RECIBIDO', color: '#17a2b8' },
        2: { id: 3, nombre: 'PENDIENTE ASIGNACION', color: '#ffc107' },
        3: { id: 4, nombre: 'ASIGNADO', color: '#28a745' },
        4: { id: 5, nombre: 'EN PROCESO', color: '#007bff' },
        5: { id: 6, nombre: 'EN ESPERA REQUERIMIENTOS', color: '#fd7e14' },
        6: { id: 7, nombre: 'RESUELTO', color: '#20c997' },
        7: { id: 8, nombre: 'CERRADO', color: '#6c757d' },
        8: null // Estado final
    };

    const siguienteEstado = mapeoSiguienteEstado[estadoActual];

    if (!siguienteEstado) {
        await Swal.fire({
            position: "center",
            icon: "info",
            title: "Estado Final",
            text: "Este ticket ya está en el estado final",
            showConfirmButton: true,
        });
        return;
    }

    const AlertaConfirmar = await Swal.fire({
        position: "center",
        icon: "question",
        title: `¿Cambiar estado del ticket?`,
        html: `
            <div class="mb-3">
                <strong>Ticket:</strong> ${ticketNumero}<br>
                <strong>Estado actual:</strong> <span class="badge bg-secondary">${estadoActualNombre}</span><br>
                <strong>Nuevo estado:</strong> <span class="badge" style="background-color: ${siguienteEstado.color}">${siguienteEstado.nombre}</span>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: `Sí, cambiar a ${siguienteEstado.nombre}`,
        confirmButtonColor: siguienteEstado.color,
        cancelButtonText: 'No, Cancelar',
        showCancelButton: true
    });

    if (AlertaConfirmar.isConfirmed) {
        const body = new FormData();
        body.append('ticket_numero', ticketNumero);
        body.append('estado_actual', estadoActual);
        
        const url = `/app_ticket/estado-tickets/cambiarEstadoAPI`;
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
                    title: "Estado Actualizado",
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

const eliminarTicket = async (e) => {
    const ticketNumero = e.currentTarget.dataset.ticket;

    const AlertaConfirmarEliminar = await Swal.fire({
        position: "center",
        icon: "warning",
        title: "¿Eliminar ticket?",
        html: `
            <div class="mb-3">
                <strong>Ticket:</strong> ${ticketNumero}<br>
                <span class="text-danger">Esta acción no se puede deshacer</span>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Sí, Eliminar',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'No, Cancelar',
        showCancelButton: true
    });

    if (AlertaConfirmarEliminar.isConfirmed) {
        const url = `/app_ticket/estado-tickets/eliminar?ticket_numero=${encodeURIComponent(ticketNumero)}`;
        const config = {
            method: 'GET'
        }

        try {
            const consulta = await fetch(url, config);
            const respuesta = await consulta.json();
            const { codigo, mensaje } = respuesta;

            if (codigo == 1) {
                await Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Ticket Eliminado",
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
                if (row.esSeparador) {
                    return '';
                }
                return row.numeroConsecutivo;
            }
        },
        { 
            title: 'Número Ticket', 
            data: 'form_tick_num',
            width: '12%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) {
                    return `<strong class="text-primary fs-5 text-center w-100 d-block">${row.nombre} (${row.cantidad})</strong>`;
                }
                return data;
            }
        },
        { 
            title: 'Solicitante', 
            data: 'solicitante_nombre',
            width: '15%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data;
            }
        },
        { 
            title: 'Técnico Encargado', 
            data: 'encargado_nombre',
            width: '12%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data;
            }
        },
        { 
            title: 'Dependencia', 
            data: 'dependencia_nombre',
            width: '15%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data ? data.substring(0, 25) + '...' : '';
            }
        },
        {
            title: 'Estado',
            data: 'estado_descripcion',
            width: '10%',
            render: (data, type, row) => {
                if (row.esSeparador) return '';
                
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
            width: '10%',
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                return data;
            }
        },
        {
            title: 'Acciones',
            data: 'tic_id',
            width: '23%',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
                if (row.esSeparador) return '';
                
                const estadoId = row.estado_ticket;
                const estadoNombre = row.estado_descripcion;
                const ticketNumero = row.form_tick_num;
                
                let botones = '';

                // Botón Ver Detalles (siempre visible)
                botones += `
                    <button class='btn btn-info btn-sm ver mx-1' 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalDetalleTicket"
                        data-ticket='${JSON.stringify(row)}'
                        title="Ver Detalles">
                        <i class='bi bi-eye'></i>
                    </button>
                `;

                // Botón de Estado (cambiar al siguiente estado)
                if (estadoId < 8) { // No mostrar si está CERRADO
                    const mapeoColores = {
                        1: 'btn-primary',    // CREADO
                        2: 'btn-info',       // RECIBIDO  
                        3: 'btn-warning',    // PENDIENTE ASIGNACION
                        4: 'btn-success',    // ASIGNADO
                        5: 'btn-primary',    // EN PROCESO
                        6: 'btn-warning',    // EN ESPERA REQUERIMIENTOS
                        7: 'btn-success',    // RESUELTO
                    };
                    
                    const colorBoton = mapeoColores[estadoId] || 'btn-secondary';
                    
                    botones += `
                        <button class='btn ${colorBoton} btn-sm cambiar-estado mx-1' 
                            data-ticket="${ticketNumero}"
                            data-estado="${estadoId}"
                            data-estado-nombre="${estadoNombre}"
                            title="Cambiar Estado">
                            ${estadoNombre}
                        </button>
                    `;
                }



                // Botón Eliminar (solo para ciertos estados)
                if (estadoId <= 2 || estadoId == 8) { // CREADO, RECIBIDO o CERRADO
                    botones += `
                        <button class='btn btn-danger btn-sm eliminar mx-1' 
                            data-ticket="${ticketNumero}"
                            title="Eliminar">
                            <i class="bi bi-trash3"></i>
                        </button>
                    `;
                }

                return `<div class='d-flex justify-content-center flex-wrap'>${botones}</div>`;
            }
        }
    ],
    rowCallback: function(row, data) {
        if (data.esSeparador) {
            row.classList.add('table-secondary');
            row.style.backgroundColor = '#f8f9fa';
            row.cells[1].colSpan = 7;
            for (let i = 2; i < row.cells.length; i++) {
                row.cells[i].style.display = 'none';
            }
        }
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

const modificarTicket = (e) => {
    // Función eliminada - ya no disponible
}

// Eventos para filtros
document.getElementById('filtroFechaInicio').addEventListener('change', BuscarEstadoTickets);
document.getElementById('filtroFechaFin').addEventListener('change', BuscarEstadoTickets);
FiltroEstado.addEventListener('change', BuscarEstadoTickets);

// Cargar datos iniciales
cargarEstados();

// Mostrar los tickets automáticamente al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    BuscarEstadoTickets();
});

// Eventos del datatable
datatable.on('click', '.ver', mostrarDetalleTicket);
datatable.on('click', '.cambiar-estado', cambiarEstadoTicket);
datatable.on('click', '.eliminar', eliminarTicket);