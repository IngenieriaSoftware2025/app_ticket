<?php 
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\TicketController;
use Controllers\EstadisticasController;
use Controllers\EstadoTicketController;


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
$router->post('/estado-tickets/modificarAPI', [EstadoTicketController::class, 'modificarAPI']);
$router->get('/estado-tickets/eliminar', [EstadoTicketController::class, 'EliminarAPI']);
$router->get('/estado-tickets/buscarTecnicosAPI', [EstadoTicketController::class, 'buscarTecnicosAPI']);
$router->get('/estado-tickets/buscarEstadosAPI', [EstadoTicketController::class, 'buscarEstadosAPI']);
$router->post('/estado-tickets/cambiarEstadoAPI', [EstadoTicketController::class, 'cambiarEstadoAPI']);

//estadisticas
$router->get('/estadisticas', [EstadisticasController::class,'renderizarPagina']);




$router->comprobarRutas();