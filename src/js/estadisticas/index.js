import { Dropdown } from "bootstrap";
import Swal from "sweetalert2";
import { validarFormulario } from "../funciones";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje";
import { Chart } from "chart.js/auto";

const grafico1 = document.getElementById("grafico1").getContext("2d");
const grafico2 = document.getElementById("grafico2").getContext("2d");
const grafico3 = document.getElementById("grafico3").getContext("2d");
const grafico4 = document.getElementById("grafico4").getContext("2d");
const grafico5 = document.getElementById("grafico5").getContext("2d");
const grafico6 = document.getElementById("grafico6").getContext("2d");
const grafico7 = document.getElementById("grafico7").getContext("2d");
const grafico8 = document.getElementById("grafico8").getContext("2d");
const grafico9 = document.getElementById("grafico9").getContext("2d");
const grafico10 = document.getElementById("grafico10").getContext("2d");
const grafico11 = document.getElementById("grafico11").getContext("2d");
const grafico12 = document.getElementById("grafico12").getContext("2d");

window.graficaTicketsPorEstado = new Chart(grafico1, {
    type: 'bar',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tickets por Estado' },
            legend: { display: false }
        },
        scales: { y: { beginAtZero: true } }
    }
});

window.graficaTicketsPorPrioridad = new Chart(grafico2, {
    type: 'pie',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tickets por Prioridad' },
            legend: { position: 'bottom' }
        }
    }
});

window.graficaTicketsPorAplicacion = new Chart(grafico3, {
    type: 'doughnut',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tickets por Aplicación Afectada' },
            legend: { position: 'right' }
        }
    }
});

window.graficaEvolucionTickets = new Chart(grafico4, {
    type: 'line',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Evolución de Tickets por Mes' },
            legend: { display: false }
        },
        scales: { y: { beginAtZero: true } }
    }
});

window.graficaUsuariosMasTickets = new Chart(grafico5, {
    type: 'bar',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: {
            title: { display: true, text: 'Top 10 Usuarios con Más Tickets' },
            legend: { display: false }
        },
        scales: { x: { beginAtZero: true } }
    }
});

window.graficaTicketsResueltosPortecnico = new Chart(grafico6, {
    type: 'bar',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tickets Resueltos por Técnico' },
            legend: { display: false }
        },
        scales: { y: { beginAtZero: true } }
    }
});

window.graficaTicketsPorDepartamento = new Chart(grafico7, {
    type: 'polarArea',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tickets por Departamento' },
            legend: { position: 'bottom' }
        }
    }
});

window.graficaPerformanceTecnicos = new Chart(grafico8, {
    type: 'bar',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: {
            title: { display: true, text: 'Performance de Técnicos (Promedio)' },
            legend: { display: false }
        },
        scales: { x: { beginAtZero: true } }
    }
});

window.graficaTiempoPromedioResolucion = new Chart(grafico9, {
    type: 'pie',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tiempo Promedio de Resolución' },
            legend: { position: 'right' }
        }
    }
});

window.graficaTiempoRespuestaPorPrioridad = new Chart(grafico10, {
    type: 'doughnut',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tiempo de Respuesta por Prioridad' },
            legend: { position: 'bottom' }
        }
    }
});

window.graficaSatisfaccionUsuario = new Chart(grafico11, {
    type: 'bar',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Satisfacción del Usuario' },
            legend: { display: false }
        },
        scales: { y: { beginAtZero: true } }
    }
});

window.graficaTicketsReabiertos = new Chart(grafico12, {
    type: 'doughnut',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true,
        plugins: {
            title: { display: true, text: 'Tickets Reabiertos vs Cerrados' },
            legend: { position: 'bottom' }
        }
    }
});

const BuscarTicketsPorEstado = async () => {
    const url = '/app_ticket/estadisticas/buscarTicketsPorEstadoAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Datos recibidos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.estado);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaTicketsPorEstado.data.labels = etiquetas;
            window.graficaTicketsPorEstado.data.datasets = [{
                label: 'Cantidad de Tickets',
                data: cantidades,
                backgroundColor: ['#008000', '#0000FF', '#FFFF00', '#FFA500', '#000000']
            }];
            window.graficaTicketsPorEstado.update();
        }
    } catch (error) {
        console.log('Error:', error);
    }
}

const BuscarTicketsPorPrioridad = async () => {
    const url = '/app_ticket/estadisticas/buscarTicketsPorPrioridadAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Prioridad datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.prioridad);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaTicketsPorPrioridad.data.labels = etiquetas;
            window.graficaTicketsPorPrioridad.data.datasets = [{
                label: 'Tickets por Prioridad',
                data: cantidades,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
            }];
            window.graficaTicketsPorPrioridad.update();
        }
    } catch (error) {
        console.log('Error:', error);
    }
}

const BuscarTicketsPorAplicacion = async () => {
    const url = '/app_ticket/estadisticas/buscarTicketsPorAplicacionAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Aplicación datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.aplicacion);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaTicketsPorAplicacion.data.labels = etiquetas;
            window.graficaTicketsPorAplicacion.data.datasets = [{
                label: 'Tickets por Aplicación',
                data: cantidades,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
            }];
            window.graficaTicketsPorAplicacion.update();
        }
    } catch (error) {
        console.log('Error:', error);
    }
}

const BuscarEvolucionTickets = async () => {
    const url = '/app_ticket/estadisticas/buscarEvolucionTicketsAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Evolución datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.mes);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaEvolucionTickets.data.labels = etiquetas;
            window.graficaEvolucionTickets.data.datasets = [{
                label: 'Tickets por Mes',
                data: cantidades,
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }];
            window.graficaEvolucionTickets.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarUsuariosMasTickets = async () => {
    const url = '/app_ticket/estadisticas/buscarUsuariosMasTicketsAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Usuarios datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.usuario);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaUsuariosMasTickets.data.labels = etiquetas;
            window.graficaUsuariosMasTickets.data.datasets = [{
                label: 'Tickets por Usuario',
                data: cantidades,
                backgroundColor: '#FF6384'
            }];
            window.graficaUsuariosMasTickets.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarTicketsResueltosPortecnico = async () => {
    const url = '/app_ticket/estadisticas/buscarTicketsResueltosPortecnicoAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Técnicos datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.tecnico);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaTicketsResueltosPortecnico.data.labels = etiquetas;
            window.graficaTicketsResueltosPortecnico.data.datasets = [{
                label: 'Tickets Resueltos',
                data: cantidades,
                backgroundColor: '#36A2EB'
            }];
            window.graficaTicketsResueltosPortecnico.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarTicketsPorDepartamento = async () => {
    const url = '/app_ticket/estadisticas/buscarTicketsPorDepartamentoAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Departamento datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.departamento);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaTicketsPorDepartamento.data.labels = etiquetas;
            window.graficaTicketsPorDepartamento.data.datasets = [{
                label: 'Tickets por Departamento',
                data: cantidades,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }];
            window.graficaTicketsPorDepartamento.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarPerformanceTecnicos = async () => {
    const url = '/app_ticket/estadisticas/buscarPerformanceTecnicosAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Performance datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.tecnico);
            const promedios = data.map(d => parseFloat(d.promedio));
            
            window.graficaPerformanceTecnicos.data.labels = etiquetas;
            window.graficaPerformanceTecnicos.data.datasets = [{
                label: 'Promedio de Resolución (días)',
                data: promedios,
                backgroundColor: '#FFCE56'
            }];
            window.graficaPerformanceTecnicos.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarTiempoPromedioResolucion = async () => {
    const url = '/app_ticket/estadisticas/buscarTiempoPromedioResolucionAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Tiempo resolución datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.categoria);
            const tiempos = data.map(d => parseFloat(d.tiempo));
            
            window.graficaTiempoPromedioResolucion.data.labels = etiquetas;
            window.graficaTiempoPromedioResolucion.data.datasets = [{
                label: 'Tiempo Promedio (días)',
                data: tiempos,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
            }];
            window.graficaTiempoPromedioResolucion.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarTiempoRespuestaPorPrioridad = async () => {
    const url = '/app_ticket/estadisticas/buscarTiempoRespuestaPorPrioridadAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Tiempo prioridad datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.prioridad);
            const tiempos = data.map(d => parseFloat(d.tiempo));
            
            window.graficaTiempoRespuestaPorPrioridad.data.labels = etiquetas;
            window.graficaTiempoRespuestaPorPrioridad.data.datasets = [{
                label: 'Tiempo Respuesta (horas)',
                data: tiempos,
                backgroundColor: ['#4BC0C0', '#9966FF', '#FF9F40']
            }];
            window.graficaTiempoRespuestaPorPrioridad.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarSatisfaccionUsuario = async () => {
    const url = '/app_ticket/estadisticas/buscarSatisfaccionUsuarioAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Satisfacción datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.calificacion);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaSatisfaccionUsuario.data.labels = etiquetas;
            window.graficaSatisfaccionUsuario.data.datasets = [{
                label: 'Calificaciones',
                data: cantidades,
                backgroundColor: '#28a745'
            }];
            window.graficaSatisfaccionUsuario.update();
        }
    } catch (error) {
        console.log(error);
    }
}

const BuscarTicketsReabiertos = async () => {
    const url = '/app_ticket/estadisticas/buscarTicketsReabiertosAPI';
    const config = { method: 'GET' }

    try {
        const respuesta = await fetch(url, config);
        const datos = await respuesta.json();
        const { codigo, mensaje, data } = datos;
        
        console.log('Reabiertos datos:', data);
        
        if (codigo == 1 && data.length > 0) {
            const etiquetas = data.map(d => d.tipo);
            const cantidades = data.map(d => parseInt(d.cantidad));
            
            window.graficaTicketsReabiertos.data.labels = etiquetas;
            window.graficaTicketsReabiertos.data.datasets = [{
                label: 'Tickets',
                data: cantidades,
                backgroundColor: ['#dc3545', '#28a745']
            }];
            window.graficaTicketsReabiertos.update();
        }
    } catch (error) {
        console.log(error);
    }
}

// Ejecutar todas las funciones al cargar la página
BuscarTicketsPorEstado();
BuscarTicketsPorPrioridad();
BuscarTicketsPorAplicacion();
BuscarEvolucionTickets();
BuscarUsuariosMasTickets();
BuscarTicketsResueltosPortecnico();
BuscarTicketsPorDepartamento();
BuscarPerformanceTecnicos();
BuscarTiempoPromedioResolucion();
BuscarTiempoRespuestaPorPrioridad();
BuscarSatisfaccionUsuario();
BuscarTicketsReabiertos();