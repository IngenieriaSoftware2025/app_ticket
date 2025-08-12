<?php 
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\TicketController;
use Controllers\EstadisticasController;
use Controllers\EstadoTicketController;
use Controllers\HistorialTicketsController;


$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

// Ruta principal
$router->get('/', [AppController::class,'index']);

//ruta para mis tickets - CORREGIDA
$router->get('/mis-tickets', [EstadoTicketController::class,'renderizarPagina']);

// Rutas para tickets
$router->get('/ticket', [TicketController::class,'renderizarPagina']);
$router->post('/ticket/guardar', [TicketController::class,'guardarAPI']);
$router->get('/ticket/aplicaciones', [TicketController::class, 'obtenerAplicacionesAPI']);


//estado-tickets
$router->get('/estado-tickets', [EstadoTicketController::class, 'renderizarPagina']);
$router->post('/estado-tickets/guardarAPI', [EstadoTicketController::class, 'guardarAPI']);
$router->get('/estado-tickets/buscarAPI', [EstadoTicketController::class, 'buscarAPI']);
$router->get('/estado-tickets/eliminar', [EstadoTicketController::class, 'EliminarAPI']);
$router->get('/estado-tickets/buscarTecnicosAPI', [EstadoTicketController::class, 'buscarTecnicosAPI']);
$router->get('/estado-tickets/buscarEstadosAPI', [EstadoTicketController::class, 'buscarEstadosAPI']);
$router->post('/estado-tickets/cambiarEstadoAPI', [EstadoTicketController::class, 'cambiarEstadoAPI']);

//historial-tickets
$router->get('/historial', [HistorialTicketsController::class, 'renderizarPagina']);
$router->get('/historial/buscarAPI', [HistorialTicketsController::class, 'buscarAPI']);
$router->get('/historial/buscarCreadosAPI', [HistorialTicketsController::class, 'buscarCreadosAPI']);
$router->get('/historial/buscarFinalizadosAPI', [HistorialTicketsController::class, 'buscarFinalizadosAPI']);

//estadisticas
$router->get('/estadisticas', [EstadisticasController::class,'renderizarPagina']);

// APIs de estadisticas
$router->get('/estadisticas/buscarTicketsPorEstadoAPI', [EstadisticasController::class, 'buscarTicketsPorEstadoAPI']);


$router->comprobarRutas();