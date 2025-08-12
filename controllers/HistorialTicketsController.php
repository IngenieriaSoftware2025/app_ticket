<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;

class HistorialTicketsController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        $router->render('historial/index', []);
    }

    public static function buscarAPI()
    {
        try {
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'recibidos'; // 'recibidos', 'finalizados' o 'rechazados'

            $condiciones = ["ft.form_tick_num IS NOT NULL"];
            
            // Filtrar según el estado del formulario
            if ($tipo == 'rechazados') {
                $condiciones[] = "ft.form_estado = 0"; // Solo rechazados
            } else {
                $condiciones[] = "ft.form_estado = 1"; // Solo activos
            }

            if ($fecha_inicio) {
                $condiciones[] = "ft.form_fecha_creacion >= '{$fecha_inicio}'";
            }

            if ($fecha_fin) {
                $condiciones[] = "ft.form_fecha_creacion <= '{$fecha_fin}'";
            }

            $where = implode(" AND ", $condiciones);
            
            // Consulta para obtener todos los tickets
            $sql = "SELECT ft.form_tick_num, 
                           ft.tic_comentario_falla, 
                           ft.tic_correo_electronico, 
                           ft.form_fecha_creacion, 
                           ft.tic_imagen,
                           ft.form_tic_usu,
                           mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_nom2 || ' ' || mp_solicitante.per_ape1 AS solicitante_nombre,
                           md.dep_desc_lg AS dependencia_nombre
                    FROM formulario_ticket ft
                    INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                    INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                    WHERE $where 
                    ORDER BY ft.form_fecha_creacion DESC";
                    
            $tickets = self::fetchArray($sql);
            
            // Procesar cada ticket para agregar información de estado
            $data = [];
            foreach ($tickets as $ticket) {
                if ($tipo == 'rechazados') {
                    // Para rechazados, solo agregar información básica
                    $ticket['tic_id'] = $ticket['form_tick_num'];
                    $ticket['encargado_nombre'] = 'SIN ASIGNAR';
                    $ticket['estado_descripcion'] = 'RECHAZADO';
                    $ticket['estado_ticket'] = 0;
                    $data[] = $ticket;
                } else {
                    // Para recibidos y finalizados, verificar si el ticket está asignado
                    $sql_asignado = "SELECT ta.tic_id, ta.tic_encargado, ta.estado_ticket,
                                            mp_encargado.per_nom1 || ' ' || mp_encargado.per_nom2 || ' ' || mp_encargado.per_ape1 AS encargado_nombre,
                                            et.est_tic_desc AS estado_descripcion
                                     FROM tickets_asignados ta
                                     INNER JOIN mper mp_encargado ON ta.tic_encargado = mp_encargado.per_catalogo
                                     INNER JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                     WHERE ta.tic_numero_ticket = '{$ticket['form_tick_num']}'";
                    
                    $asignado = self::fetchFirst($sql_asignado);
                    
                    if ($asignado) {
                        // Ticket asignado
                        $ticket['tic_id'] = $asignado['tic_id'];
                        $ticket['encargado_nombre'] = $asignado['encargado_nombre'];
                        $ticket['estado_descripcion'] = $asignado['estado_descripcion'];
                        $ticket['estado_ticket'] = $asignado['estado_ticket'];
                    } else {
                        // Ticket no asignado (estado RECIBIDO)
                        $ticket['tic_id'] = $ticket['form_tick_num'];
                        $ticket['encargado_nombre'] = 'SIN ASIGNAR';
                        $ticket['estado_descripcion'] = 'RECIBIDO';
                        $ticket['estado_ticket'] = 1;
                    }
                    
                    // Filtrar según el tipo solicitado
                    if ($tipo == 'recibidos') {
                        // RECIBIDOS: estados 1-2 (RECIBIDO y EN PROCESO)
                        if ($ticket['estado_ticket'] >= 1 && $ticket['estado_ticket'] <= 2) {
                            $data[] = $ticket;
                        }
                    } elseif ($tipo == 'finalizados') {
                        // FINALIZADOS: estado 3 (FINALIZADO)
                        if ($ticket['estado_ticket'] == 3) {
                            $data[] = $ticket;
                        }
                    }
                }
            }

            // Determinar el mensaje según el tipo
            $mensaje_tipo = '';
            switch($tipo) {
                case 'recibidos':
                    $mensaje_tipo = 'Tickets recibidos';
                    break;
                case 'finalizados':
                    $mensaje_tipo = 'Tickets finalizados';
                    break;
                case 'rechazados':
                    $mensaje_tipo = 'Tickets rechazados';
                    break;
                default:
                    $mensaje_tipo = 'Tickets';
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => $mensaje_tipo . ' obtenidos correctamente',
                'data' => $data,
                'tipo' => $tipo
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener el historial de tickets',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarCreadosAPI()
    {
        try {
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

            // Solo mostrar tickets que no estén rechazados (form_estado = 1)
            $condiciones = ["ft.form_tick_num IS NOT NULL", "ft.form_estado = 1"];

            if ($fecha_inicio) {
                $condiciones[] = "ft.form_fecha_creacion >= '{$fecha_inicio}'";
            }

            if ($fecha_fin) {
                $condiciones[] = "ft.form_fecha_creacion <= '{$fecha_fin}'";
            }

            $where = implode(" AND ", $condiciones);
            
            $sql = "SELECT ft.form_tick_num, 
                           ft.tic_comentario_falla, 
                           ft.tic_correo_electronico, 
                           ft.form_fecha_creacion, 
                           ft.tic_imagen,
                           ft.form_tic_usu,
                           mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_nom2 || ' ' || mp_solicitante.per_ape1 AS solicitante_nombre,
                           md.dep_desc_lg AS dependencia_nombre
                    FROM formulario_ticket ft
                    INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                    INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                    WHERE $where 
                    ORDER BY ft.form_fecha_creacion DESC";
                    
            $tickets = self::fetchArray($sql);
            
            $data = [];
            foreach ($tickets as $ticket) {
                $sql_asignado = "SELECT ta.tic_id, ta.tic_encargado, ta.estado_ticket,
                                        mp_encargado.per_nom1 || ' ' || mp_encargado.per_nom2 || ' ' || mp_encargado.per_ape1 AS encargado_nombre,
                                        et.est_tic_desc AS estado_descripcion
                                 FROM tickets_asignados ta
                                 INNER JOIN mper mp_encargado ON ta.tic_encargado = mp_encargado.per_catalogo
                                 INNER JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                 WHERE ta.tic_numero_ticket = '{$ticket['form_tick_num']}'";
                
                $asignado = self::fetchFirst($sql_asignado);
                
                if ($asignado) {
                    $ticket['tic_id'] = $asignado['tic_id'];
                    $ticket['encargado_nombre'] = $asignado['encargado_nombre'];
                    $ticket['estado_descripcion'] = $asignado['estado_descripcion'];
                    $ticket['estado_ticket'] = $asignado['estado_ticket'];
                } else {
                    $ticket['tic_id'] = $ticket['form_tick_num'];
                    $ticket['encargado_nombre'] = 'SIN ASIGNAR';
                    $ticket['estado_descripcion'] = 'RECIBIDO';
                    $ticket['estado_ticket'] = 1;
                }
                
                // Solo tickets recibidos (estados 1-2: RECIBIDO y EN PROCESO)
                if ($ticket['estado_ticket'] >= 1 && $ticket['estado_ticket'] <= 2) {
                    $data[] = $ticket;
                }
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets recibidos obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets recibidos',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarFinalizadosAPI()
    {
        try {
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

            // Solo mostrar tickets que no estén rechazados (form_estado = 1)
            $condiciones = ["ft.form_tick_num IS NOT NULL", "ft.form_estado = 1"];

            if ($fecha_inicio) {
                $condiciones[] = "ft.form_fecha_creacion >= '{$fecha_inicio}'";
            }

            if ($fecha_fin) {
                $condiciones[] = "ft.form_fecha_creacion <= '{$fecha_fin}'";
            }

            $where = implode(" AND ", $condiciones);
            
            $sql = "SELECT ft.form_tick_num, 
                           ft.tic_comentario_falla, 
                           ft.tic_correo_electronico, 
                           ft.form_fecha_creacion, 
                           ft.tic_imagen,
                           ft.form_tic_usu,
                           mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_nom2 || ' ' || mp_solicitante.per_ape1 AS solicitante_nombre,
                           md.dep_desc_lg AS dependencia_nombre
                    FROM formulario_ticket ft
                    INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                    INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                    WHERE $where 
                    ORDER BY ft.form_fecha_creacion DESC";
                    
            $tickets = self::fetchArray($sql);
            
            $data = [];
            foreach ($tickets as $ticket) {
                $sql_asignado = "SELECT ta.tic_id, ta.tic_encargado, ta.estado_ticket,
                                        mp_encargado.per_nom1 || ' ' || mp_encargado.per_nom2 || ' ' || mp_encargado.per_ape1 AS encargado_nombre,
                                        et.est_tic_desc AS estado_descripcion
                                 FROM tickets_asignados ta
                                 INNER JOIN mper mp_encargado ON ta.tic_encargado = mp_encargado.per_catalogo
                                 INNER JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                 WHERE ta.tic_numero_ticket = '{$ticket['form_tick_num']}'";
                
                $asignado = self::fetchFirst($sql_asignado);
                
                if ($asignado && $asignado['estado_ticket'] == 3) {
                    // Solo tickets finalizados (estado 3: FINALIZADO)
                    $ticket['tic_id'] = $asignado['tic_id'];
                    $ticket['encargado_nombre'] = $asignado['encargado_nombre'];
                    $ticket['estado_descripcion'] = $asignado['estado_descripcion'];
                    $ticket['estado_ticket'] = $asignado['estado_ticket'];
                    $data[] = $ticket;
                }
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets finalizados obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets finalizados',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}