import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

let estadoActual = 1; // Por defecto mostrar RECIBIDOS

const BuscarEstadoTickets = async () => {
    const fechaInicio = document.getElementById('filtroFechaInicio').value;
    const fechaFin = document.getElementById('filtroFechaFin').value;

    let url = `/app_ticket/estado-tickets/buscarAPI?`;
    const params = new URLSearchParams();

    if (fechaInicio) params.append('fecha_inicio', fechaInicio);
    if (fechaFin) params.append('fecha_fin', fechaFin);
    
    // Siempre filtrar por el estado actual seleccionado
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
            
            // Mostrar directamente los datos sin agrupaciones
            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(data).draw();
            }
            
            // Actualizar el indicador
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

const actualizarIndicador = (cantidad) => {
    const indicador = document.getElementById('textoIndicador');
    const estadosTexto = {
        1: 'recibidos',
        2: 'en proceso', 
        3: 'finalizados'
    };
    
    indicador.textContent = `Mostrando tickets ${estadosTexto[estadoActual]} (${cantidad} registros)`;
}

const cambiarEstado = (nuevoEstado) => {
    estadoActual = nuevoEstado;
    
    // Actualizar estilos de botones
    document.querySelectorAll('.estado-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.estado == nuevoEstado) {
            btn.classList.add('active');
            // Cambiar clases según el estado
            if (nuevoEstado == 1) {
                btn.className = 'btn btn-primary btn-lg mx-2 estado-btn active';
            } else if (nuevoEstado == 2) {
                btn.className = 'btn btn-primary btn-lg mx-2 estado-btn active';
            } else if (nuevoEstado == 3) {
                btn.className = 'btn btn-success btn-lg mx-2 estado-btn active';
            }
        } else {
            // Restablecer clases de botones inactivos
            if (btn.dataset.estado == 1) {
                btn.className = 'btn btn-outline-primary btn-lg mx-2 estado-btn';
            } else if (btn.dataset.estado == 2) {
                btn.className = 'btn btn-outline-primary btn-lg mx-2 estado-btn';
            } else if (btn.dataset.estado == 3) {
                btn.className = 'btn btn-outline-success btn-lg mx-2 estado-btn';
            }
        }
    });
    
    // Buscar tickets del nuevo estado
    BuscarEstadoTickets();
}

const cambiarEstadoTicket = async (e) => {
    const ticketNumero = e.currentTarget.dataset.ticket;
    const estadoActual = parseInt(e.currentTarget.dataset.estado);
    const estadoActualNombre = e.currentTarget.dataset.estadoNombre;

    // Nuevo mapeo simplificado
    const mapeoSiguienteEstado = {
        1: { id: 2, nombre: 'EN PROCESO', color: '#007bff' },
        2: { id: 3, nombre: 'FINALIZADO', color: '#28a745' },
        3: null // Estado final
    };

    const siguienteEstado = mapeoSiguienteEstado[estadoActual];

    if (!siguienteEstado) {
        await Swal.fire({
            position: "center",
            icon: "info",
            title: "Estado Final",
            text: "Este ticket ya está finalizado",
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
                return meta.row + 1; // Numeración simple sin separadores
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
            width: '18%'
        },
        { 
            title: 'Dependencia', 
            data: 'dependencia_nombre',
            width: '20%',
            render: (data, type, row, meta) => {
                return data ? data.substring(0, 30) + '...' : '';
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
                    case 'FINALIZADO': badgeClass = 'bg-success'; break;
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
            width: '30%',
            searchable: false,
            orderable: false,
            render: (data, type, row, meta) => {
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
                if (estadoId < 3) { // No mostrar si está FINALIZADO
                    const mapeoColores = {
                        1: 'btn-info',       // RECIBIDO
                        2: 'btn-primary',    // EN PROCESO
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

                // Botón Rechazar (solo para estado RECIBIDO)
                if (estadoId == 1) { // Solo RECIBIDO
                    botones += `
                        <button class='btn btn-danger btn-sm rechazar mx-1' 
                            data-ticket="${ticketNumero}"
                            title="Rechazar">
                            <i class="bi bi-x-circle"></i> Rechazar
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

// Eventos para filtros de fecha
document.getElementById('filtroFechaInicio').addEventListener('change', BuscarEstadoTickets);
document.getElementById('filtroFechaFin').addEventListener('change', BuscarEstadoTickets);

// Eventos para botones de estado
document.querySelectorAll('.estado-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const nuevoEstado = parseInt(e.currentTarget.dataset.estado);
        cambiarEstado(nuevoEstado);
    });
});

// Mostrar los tickets automáticamente al cargar la página (RECIBIDOS por defecto)
document.addEventListener('DOMContentLoaded', function() {
    BuscarEstadoTickets();
});

// Eventos del datatable
datatable.on('click', '.ver', mostrarDetalleTicket);
datatable.on('click', '.cambiar-estado', cambiarEstadoTicket);
datatable.on('click', '.rechazar', rechazarTicket);