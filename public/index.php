<?php 
require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\TicketController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

// Ruta principal
$router->get('/', [AppController::class,'index']);

//ruta para mis tickets
$router->get('/mis-tickets', [TicketController::class,'misTickets']);

// Rutas para tickets
$router->get('/ticket', [TicketController::class,'renderizarPagina']);
$router->post('/ticket/guardar', [TicketController::class,'guardarAPI']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();