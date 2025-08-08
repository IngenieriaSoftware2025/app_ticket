import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import { validarFormulario } from '../funciones';
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";

const formEstadoTicket = document.getElementById('formEstadoTicket');
const BtnGuardar = document.getElementById('BtnGuardar');
const BtnModificar = document.getElementById('BtnModificar');
const BtnLimpiar = document.getElementById('BtnLimpiar');
const BtnBuscarTickets = document.getElementById('BtnBuscarTickets');
const SelectNumeroTicket = document.getElementById('tic_numero_ticket');
const SelectTecnico = document.getElementById('tic_encargado');
const SelectEstado = document.getElementById('estado_ticket');
const FiltroEstado = document.getElementById('filtroEstado');
const seccionTabla = document.getElementById('seccionTabla');

const cargarTecnicos = async () => {
    const url = `/app_ticket/estado-tickets/buscarTecnicosAPI`;
    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            SelectTecnico.innerHTML = '<option value="">Seleccione un técnico</option>';
            
            data.forEach(tecnico => {
                const option = document.createElement('option');
                option.value = tecnico.per_catalogo;
                option.textContent = tecnico.tecnico_nombre;
                SelectTecnico.appendChild(option);
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
            SelectEstado.innerHTML = '<option value="">Seleccione un estado</option>';
            FiltroEstado.innerHTML = '<option value="">Todos los estados</option>';
            
            data.forEach(estado => {
                const option = document.createElement('option');
                option.value = estado.est_tic_id;
                option.textContent = estado.est_tic_desc;
                SelectEstado.appendChild(option);

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

const cargarTicketsDisponibles = async () => {
    const url = `/app_ticket/ticket/buscarTicketsDisponiblesAPI`;
    const config = {
        method: 'GET'
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;

        if (codigo == 1) {
            SelectNumeroTicket.innerHTML = '<option value="">Seleccione un ticket</option>';
            
            data.forEach(ticket => {
                const option = document.createElement('option');
                option.value = ticket.form_tick_num;
                option.textContent = `${ticket.form_tick_num} - ${ticket.tic_correo_electronico}`;
                SelectNumeroTicket.appendChild(option);
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

const guardarEstadoTicket = async e => {
    e.preventDefault();
    BtnGuardar.disabled = true;

    if (!validarFormulario(formEstadoTicket, ['tic_id'])) {
        Swal.fire({
            position: "center",
            icon: "info",
            title: "FORMULARIO INCOMPLETO",
            text: "Debe de validar todos los campos",
            showConfirmButton: true,
        });
        BtnGuardar.disabled = false;
        return;
    }

    const body = new FormData(formEstadoTicket);
    const url = "/app_ticket/estado-tickets/guardarAPI";
    const config = {
        method: 'POST',
        body
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        console.log(datos);
        const { codigo, mensaje } = datos;

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "Exito",
                text: mensaje,
                showConfirmButton: true,
            });

            limpiarTodo();
            BuscarEstadoTickets();
            cargarTicketsDisponibles();
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
    BtnGuardar.disabled = false;
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

            if (datatable) {
                datatable.clear().draw();
                datatable.rows.add(data).draw();
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
    }
}

const MostrarTabla = () => {
    if (seccionTabla.style.display === 'none') {
        seccionTabla.style.display = 'block';
        BuscarEstadoTickets();
    } else {
        seccionTabla.style.display = 'none';
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
    columns: [
        {
            title: 'No.',
            data: 'tic_id',
            width: '5%',
            render: (data, type, row, meta) => meta.row + 1
        },
        { 
            title: 'Número Ticket', 
            data: 'form_tick_num',
            width: '15%'
        },
        { 
            title: 'Solicitante', 
            data: 'solicitante_nombre',
            width: '20%'
        },
        { 
            title: 'Técnico Encargado', 
            data: 'encargado_nombre',
            width: '15%'
        },
        { 
            title: 'Dependencia', 
            data: 'dependencia_nombre',
            width: '15%',
            render: (data, type, row) => {
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
            width: '10%'
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
                     <button class='btn btn-info btn-sm ver mx-1' 
                         data-bs-toggle="modal" 
                         data-bs-target="#modalDetalleTicket"
                         data-ticket='${JSON.stringify(row)}'
                         title="Ver Detalles">
                         <i class='bi bi-eye'></i>
                     </button>
                     <button class='btn btn-warning btn-sm modificar mx-1' 
                         data-id="${data}" 
                         data-numero="${row.form_tick_num || ''}"
                         data-encargado="${row.tic_encargado || ''}"
                         data-estado="${row.estado_ticket || ''}"
                         title="Modificar">
                         <i class='bi bi-pencil-square'></i>
                     </button>
                     <button class='btn btn-danger btn-sm eliminar mx-1' 
                         data-id="${data}"
                         title="Eliminar">
                        <i class="bi bi-trash3"></i>
                     </button>
                 </div>`;
            }
        }
    ]
});

const llenarFormulario = (event) => {
    const datos = event.currentTarget.dataset;

    document.getElementById('tic_id').value = datos.id;
    document.getElementById('tic_numero_ticket').value = datos.numero;
    document.getElementById('tic_encargado').value = datos.encargado;
    document.getElementById('estado_ticket').value = datos.estado;

    BtnGuardar.classList.add('d-none');
    BtnModificar.classList.remove('d-none');

    window.scrollTo({
        top: 0,
    });
}

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

const limpiarTodo = () => {
    formEstadoTicket.reset();
    BtnGuardar.classList.remove('d-none');
    BtnModificar.classList.add('d-none');
}

const ModificarEstadoTicket = async (event) => {
    event.preventDefault();
    BtnModificar.disabled = true;

    if (!validarFormulario(formEstadoTicket, ['tic_id'])) {
        Swal.fire({
            position: "center",
            icon: "info",
            title: "FORMULARIO INCOMPLETO",
            text: "Debe de validar todos los campos",
            showConfirmButton: true,
        });
        BtnModificar.disabled = false;
        return;
    }

    const body = new FormData(formEstadoTicket);
    const url = '/app_ticket/estado-tickets/modificarAPI';
    const config = {
        method: 'POST',
        body
    }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje } = datos;

        if (codigo == 1) {
            await Swal.fire({
                position: "center",
                icon: "success",
                title: "Exito",
                text: mensaje,
                showConfirmButton: true,
            });

            limpiarTodo();
            BuscarEstadoTickets();
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
    BtnModificar.disabled = false;
}

const EliminarEstadoTicket = async (e) => {
    const idTicket = e.currentTarget.dataset.id;

    const AlertaConfirmarEliminar = await Swal.fire({
        position: "center",
        icon: "info",
        title: "¿Desea ejecutar esta acción?",
        text: 'Esta completamente seguro que desea eliminar este registro',
        showConfirmButton: true,
        confirmButtonText: 'Si, Eliminar',
        confirmButtonColor: 'red',
        cancelButtonText: 'No, Cancelar',
        showCancelButton: true
    });

    if (AlertaConfirmarEliminar.isConfirmed) {
        const url = `/app_ticket/estado-tickets/eliminar?id=${idTicket}`;
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
                    title: "Exito",
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
        }
    }
}

// Eventos para filtros
document.getElementById('filtroFechaInicio').addEventListener('change', BuscarEstadoTickets);
document.getElementById('filtroFechaFin').addEventListener('change', BuscarEstadoTickets);
FiltroEstado.addEventListener('change', BuscarEstadoTickets);

// Cargar datos iniciales
cargarTecnicos();
cargarEstados();
cargarTicketsDisponibles();

// Eventos del datatable
datatable.on('click', '.eliminar', EliminarEstadoTicket);
datatable.on('click', '.modificar', llenarFormulario);
datatable.on('click', '.ver', mostrarDetalleTicket);

// Eventos del formulario
formEstadoTicket.addEventListener('submit', guardarEstadoTicket);
BtnLimpiar.addEventListener('click', limpiarTodo);
BtnModificar.addEventListener('click', ModificarEstadoTicket);
BtnBuscarTickets.addEventListener('click', MostrarTabla);