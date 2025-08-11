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
            $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'creados'; // 'creados' o 'finalizados'

            $condiciones = ["ft.form_tick_num IS NOT NULL"];

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
                // Verificar si el ticket está asignado
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
                    // Ticket no asignado (estado CREADO)
                    $ticket['tic_id'] = $ticket['form_tick_num'];
                    $ticket['encargado_nombre'] = 'SIN ASIGNAR';
                    $ticket['estado_descripcion'] = 'CREADO';
                    $ticket['estado_ticket'] = 1;
                }
                
                // Filtrar según el tipo solicitado
                if ($tipo == 'creados') {
                    // CREADOS: estados 1-6 (CREADO hasta EN ESPERA REQUERIMIENTOS)
                    if ($ticket['estado_ticket'] >= 1 && $ticket['estado_ticket'] <= 6) {
                        $data[] = $ticket;
                    }
                } elseif ($tipo == 'finalizados') {
                    // FINALIZADOS: estados 7-8 (RESUELTO y CERRADO)
                    if ($ticket['estado_ticket'] >= 7 && $ticket['estado_ticket'] <= 8) {
                        $data[] = $ticket;
                    }
                }
            }

            // Determinar el mensaje según el tipo
            $mensaje_tipo = ($tipo == 'creados') ? 'Tickets creados' : 'Tickets finalizados';

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

            $condiciones = ["ft.form_tick_num IS NOT NULL"];

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
                    $ticket['estado_descripcion'] = 'CREADO';
                    $ticket['estado_ticket'] = 1;
                }
                
                // Solo tickets creados (estados 1-6)
                if ($ticket['estado_ticket'] >= 1 && $ticket['estado_ticket'] <= 6) {
                    $data[] = $ticket;
                }
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets creados obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets creados',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarFinalizadosAPI()
    {
        try {
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

            $condiciones = ["ft.form_tick_num IS NOT NULL"];

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
                
                if ($asignado && $asignado['estado_ticket'] >= 7 && $asignado['estado_ticket'] <= 8) {
                    // Solo tickets finalizados (estados 7-8: RESUELTO y CERRADO)
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