<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\TicketAsignado;

class EstadoTicketController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        $router->render('estadoticket/index', []);
    }

    public static function guardarAPI()
    {
        getHeadersApi();
    
        try {
            $_POST['tic_numero_ticket'] = filter_var($_POST['tic_numero_ticket'], FILTER_SANITIZE_STRING);
            
            if (empty($_POST['tic_numero_ticket'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar un ticket'
                ]);
                exit;
            }

            $sql = "SELECT tic_id FROM tickets_asignados WHERE tic_numero_ticket = '{$_POST['tic_numero_ticket']}'";
            $existe = self::fetchFirst($sql);

            if ($existe) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Este ticket ya existe en la gestión'
                ]);
                exit;
            }

            $_POST['tic_encargado'] = filter_var($_POST['tic_encargado'], FILTER_SANITIZE_NUMBER_INT);
            
            if (empty($_POST['tic_encargado']) || $_POST['tic_encargado'] < 1) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar un técnico encargado'
                ]);
                exit;
            }

            // El estado siempre será 1 (RECIBIDO) para tickets nuevos
            $_POST['estado_ticket'] = 1;
            
            $ticketAsignado = new TicketAsignado($_POST);
            $resultado = $ticketAsignado->crear();

            if($resultado['resultado'] == 1){
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Ticket asignado correctamente',
                ]);
                exit;
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al asignar el ticket',
                ]);
                exit;
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
            exit;
        }
    }

    public static function buscarAPI()
    {
        try {
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            $estado = isset($_GET['estado']) ? $_GET['estado'] : null;

            // Solo mostrar tickets que no estén rechazados (form_estado = 1)
            $condiciones = ["ft.form_tick_num IS NOT NULL", "ft.form_estado = 1"];

            if ($fecha_inicio) {
                $condiciones[] = "ft.form_fecha_creacion >= '{$fecha_inicio}'";
            }

            if ($fecha_fin) {
                $condiciones[] = "ft.form_fecha_creacion <= '{$fecha_fin}'";
            }

            $where = implode(" AND ", $condiciones);
            
            // Consulta simplificada - primero obtener todos los tickets
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
            
            // Procesar cada ticket para agregar información de estado y encargado
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
                    // Ticket no asignado (estado RECIBIDO)
                    $ticket['tic_id'] = $ticket['form_tick_num'];
                    $ticket['encargado_nombre'] = 'SIN ASIGNAR';
                    $ticket['estado_descripcion'] = 'RECIBIDO';
                    $ticket['estado_ticket'] = 1;
                }
                
                // Aplicar filtro de estado si se especificó
                if ($estado && $ticket['estado_ticket'] != $estado) {
                    continue;
                }
                
                $data[] = $ticket;
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los tickets',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function cambiarEstadoAPI()
    {
        getHeadersApi();
        
        try {
            $ticket_numero = filter_var($_POST['ticket_numero'], FILTER_SANITIZE_STRING);
            $estado_actual = filter_var($_POST['estado_actual'], FILTER_SANITIZE_NUMBER_INT);
            
            if (empty($ticket_numero)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Número de ticket inválido'
                ]);
                return;
            }

            if (empty($estado_actual) || $estado_actual < 1 || $estado_actual > 3) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Estado actual inválido'
                ]);
                return;
            }

            // Determinar el siguiente estado
            $siguiente_estado = self::obtenerSiguienteEstado($estado_actual);
            
            if (!$siguiente_estado) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Este ticket ya está finalizado'
                ]);
                return;
            }

            // Verificar si el ticket está asignado o es nuevo
            $sql_check = "SELECT tic_id FROM tickets_asignados WHERE tic_numero_ticket = '{$ticket_numero}'";
            $ticket_asignado = self::fetchFirst($sql_check);

            if ($ticket_asignado) {
                // Actualizar ticket existente en tickets_asignados
                $sql_update = "UPDATE tickets_asignados SET estado_ticket = {$siguiente_estado} WHERE tic_numero_ticket = '{$ticket_numero}'";
                $resultado = self::SQL($sql_update);
            } else {
                // Crear nuevo registro en tickets_asignados para tickets que están en RECIBIDO
                if ($estado_actual == 1) { // RECIBIDO
                    // Obtener datos del ticket del formulario
                    $sql_ticket = "SELECT form_tic_usu FROM formulario_ticket WHERE form_tick_num = '{$ticket_numero}'";
                    $ticket_data = self::fetchFirst($sql_ticket);
                    
                    if ($ticket_data) {
                        $sql_insert = "INSERT INTO tickets_asignados (tic_numero_ticket, tic_encargado, estado_ticket) 
                                      VALUES ('{$ticket_numero}', {$ticket_data['form_tic_usu']}, {$siguiente_estado})";
                        $resultado = self::SQL($sql_insert);
                    } else {
                        http_response_code(400);
                        echo json_encode([
                            'codigo' => 0,
                            'mensaje' => 'No se encontró el ticket'
                        ]);
                        return;
                    }
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'Error en el estado del ticket'
                    ]);
                    return;
                }
            }

            if ($resultado) {
                // Obtener nombre del nuevo estado
                $sql_estado = "SELECT est_tic_desc FROM estado_ticket WHERE est_tic_id = {$siguiente_estado}";
                $nuevo_estado = self::fetchFirst($sql_estado);
                
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Estado del ticket actualizado a: ' . ($nuevo_estado['est_tic_desc'] ?? 'Nuevo Estado')
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se pudo actualizar el estado del ticket'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cambiar el estado',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    private static function obtenerSiguienteEstado($estado_actual)
    {
        // Nuevo mapeo simplificado: RECIBIDO → EN PROCESO → FINALIZADO
        $estados_flujo = [
            1 => 2, // RECIBIDO → EN PROCESO
            2 => 3, // EN PROCESO → FINALIZADO
            3 => null // FINALIZADO (estado final)
        ];

        return $estados_flujo[$estado_actual] ?? null;
    }

    public static function RechazarAPI()
    {
        getHeadersApi();
        
        try {
            $ticket_numero = filter_var($_POST['ticket_numero'], FILTER_SANITIZE_STRING);
            
            if (empty($ticket_numero)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Número de ticket inválido'
                ]);
                return;
            }

            // Verificar que el ticket esté en estado RECIBIDO (1)
            $sql_check_estado = "SELECT ta.estado_ticket FROM tickets_asignados ta WHERE ta.tic_numero_ticket = '{$ticket_numero}'";
            $ticket_estado = self::fetchFirst($sql_check_estado);
            
            if ($ticket_estado && $ticket_estado['estado_ticket'] != 1) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se pueden rechazar tickets en estado RECIBIDO'
                ]);
                return;
            }

            // Cambiar form_estado a 0 (rechazado) en lugar de eliminar
            $sql_rechazar = "UPDATE formulario_ticket SET form_estado = 0 WHERE form_tick_num = '{$ticket_numero}'";
            $resultado = self::SQL($sql_rechazar);

            if ($resultado) {
                // También eliminar de tickets_asignados si existe
                $sql_delete_asignado = "DELETE FROM tickets_asignados WHERE tic_numero_ticket = '{$ticket_numero}'";
                self::SQL($sql_delete_asignado);
                
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'El ticket ha sido rechazado correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se pudo rechazar el ticket'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al rechazar',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarTecnicosAPI()
    {
        try {
            $sql = "SELECT per_catalogo, per_nom1 || ' ' || per_nom2 || ' ' || per_ape1 AS tecnico_nombre 
                    FROM mper
                    WHERE per_situacion = 1
                    ORDER BY per_nom1, per_ape1";
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Técnicos obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los técnicos',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarEstadosAPI()
    {
        try {
            $sql = "SELECT est_tic_id, est_tic_desc 
                    FROM estado_ticket
                    ORDER BY est_tic_id";
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estados obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los estados',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}