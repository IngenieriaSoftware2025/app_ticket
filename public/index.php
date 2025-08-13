<?php 
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\TicketController;
use Controllers\EstadisticasController;
use Controllers\EstadoTicketController;
use Controllers\HistorialTicketsController;
use Controllers\AsignacionTicketController;
use Controllers\EmailController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

// Ruta principal
$router->get('/', [AppController::class,'index']);

//ruta para mis tickets
$router->get('/mis-tickets', [EstadoTicketController::class,'renderizarPagina']);

// Rutas para tickets
$router->get('/ticket', [TicketController::class,'index']);
$router->post('/ticket/guardar', [TicketController::class,'guardarAPI']); // ORIGINAL
$router->get('/ticket/aplicaciones', [TicketController::class, 'obtenerAplicacionesAPI']); // ORIGINAL

//estado-tickets
$router->get('/estado-tickets', [EstadoTicketController::class, 'renderizarPagina']);
$router->post('/estado-tickets/guardarAPI', [EstadoTicketController::class, 'guardarAPI']);
$router->get('/estado-tickets/buscarAPI', [EstadoTicketController::class, 'buscarAPI']);
$router->post('/estado-tickets/rechazar', [EstadoTicketController::class, 'RechazarAPI']); // Cambiado de eliminar a rechazar
$router->get('/estado-tickets/buscarTecnicosAPI', [EstadoTicketController::class, 'buscarTecnicosAPI']);
$router->get('/estado-tickets/buscarEstadosAPI', [EstadoTicketController::class, 'buscarEstadosAPI']);
$router->post('/estado-tickets/cambiarEstadoAPI', [EstadoTicketController::class, 'cambiarEstadoAPI']);

//asignacion-tickets
$router->get('/asignacion', [AsignacionTicketController::class, 'renderizarPagina']);
$router->get('/asignacion/buscarAPI', [AsignacionTicketController::class, 'buscarAPI']);
$router->get('/asignacion/buscarOficialesAPI', [AsignacionTicketController::class, 'buscarOficialesAPI']);
$router->post('/asignacion/asignarAPI', [AsignacionTicketController::class, 'asignarAPI']);

//historial-tickets
$router->get('/historial', [HistorialTicketsController::class, 'renderizarPagina']);
$router->get('/historial/buscarAPI', [HistorialTicketsController::class, 'buscarAPI']);
$router->get('/historial/buscarCreadosAPI', [HistorialTicketsController::class, 'buscarCreadosAPI']);
$router->get('/historial/buscarFinalizadosAPI', [HistorialTicketsController::class, 'buscarFinalizadosAPI']);

//estadisticas
$router->get('/estadisticas', [EstadisticasController::class,'renderizarPagina']);

// APIs de estadisticas - TODAS LAS RUTAS
$router->get('/estadisticas/buscarTicketsPorEstadoAPI', [EstadisticasController::class, 'buscarTicketsPorEstadoAPI']);
$router->get('/estadisticas/buscarTicketsPorPrioridadAPI', [EstadisticasController::class, 'buscarTicketsPorPrioridadAPI']);
$router->get('/estadisticas/buscarTicketsPorAplicacionAPI', [EstadisticasController::class, 'buscarTicketsPorAplicacionAPI']);
$router->get('/estadisticas/buscarEvolucionTicketsAPI', [EstadisticasController::class, 'buscarEvolucionTicketsAPI']);
$router->get('/estadisticas/buscarUsuariosMasTicketsAPI', [EstadisticasController::class, 'buscarUsuariosMasTicketsAPI']);
$router->get('/estadisticas/buscarTicketsResueltosPortecnicoAPI', [EstadisticasController::class, 'buscarTicketsResueltosPortecnicoAPI']);
$router->get('/estadisticas/buscarTicketsPorDepartamentoAPI', [EstadisticasController::class, 'buscarTicketsPorDepartamentoAPI']);
$router->get('/estadisticas/buscarPerformanceTecnicosAPI', [EstadisticasController::class, 'buscarPerformanceTecnicosAPI']);
$router->get('/estadisticas/buscarTiempoPromedioResolucionAPI', [EstadisticasController::class, 'buscarTiempoPromedioResolucionAPI']);
$router->get('/estadisticas/buscarTiempoRespuestaPorPrioridadAPI', [EstadisticasController::class, 'buscarTiempoRespuestaPorPrioridadAPI']);
$router->get('/estadisticas/buscarSatisfaccionUsuarioAPI', [EstadisticasController::class, 'buscarSatisfaccionUsuarioAPI']);
$router->get('/estadisticas/buscarTicketsReabiertosAPI', [EstadisticasController::class, 'buscarTicketsReabiertosAPI']);

// RUTAS PARA SISTEMA DE CORREOS (NUEVAS)
$router->post('/email/probar', [EmailController::class, 'probarConfiguracion']);

$router->comprobarRutas();