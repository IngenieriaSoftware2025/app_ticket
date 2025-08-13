<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\TicketAsignado;
use Controllers\EmailController;

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

            // Permitir estados 1 (RECIBIDO), 2 (EN PROCESO) y 0 (RECHAZADO)
            if ($estado && !in_array($estado, [0, 1, 2])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Estado no válido'
                ]);
                return;
            }

            // Para rechazados, buscar en form_estado = 0, para otros en form_estado = 1
            if ($estado == 0) {
                $condiciones = ["ft.form_tick_num IS NOT NULL", "ft.form_estado = 0"];
            } else {
                $condiciones = ["ft.form_tick_num IS NOT NULL", "ft.form_estado = 1"];
            }

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
                           gma.gma_desc as aplicacion,
                           mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_nom2 || ' ' || mp_solicitante.per_ape1 AS solicitante_nombre,
                           md.dep_desc_lg AS dependencia_nombre
                    FROM formulario_ticket ft
                    INNER JOIN grupo_menuautocom gma ON ft.tic_app = gma.gma_codigo
                    INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                    INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                    WHERE $where 
                    ORDER BY ft.form_fecha_creacion DESC";
                    
            $tickets = self::fetchArray($sql);
            
            $data = [];
            foreach ($tickets as $ticket) {
                // Para tickets rechazados (estado 0), no buscar en tickets_asignados
                if ($estado == 0) {
                    $ticket['tic_id'] = $ticket['form_tick_num'];
                    $ticket['encargado_nombre'] = 'RECHAZADO';
                    $ticket['estado_descripcion'] = 'RECHAZADO';
                    $ticket['estado_ticket'] = 0;
                } else {
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
                    
                    // Filtrar por estado solo si se especifica y no es rechazado
                    if ($estado && $ticket['estado_ticket'] != $estado) {
                        continue;
                    }
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

            // Solo permitir cambio de estado 2 (EN PROCESO) - no hay más cambios ya que FINALIZADOS se maneja en otra vista
            if (empty($estado_actual) || $estado_actual != 2) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Estado actual inválido'
                ]);
                return;
            }

            $sql_check = "SELECT tic_id FROM tickets_asignados WHERE tic_numero_ticket = '{$ticket_numero}'";
            $ticket_asignado = self::fetchFirst($sql_check);

            if (!$ticket_asignado) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontró el ticket asignado'
                ]);
                return;
            }

            // En esta vista solo manejamos hasta EN PROCESO
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Los tickets en proceso se finalizan desde otra vista'
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cambiar el estado',
                'detalle' => $e->getMessage(),
            ]);
        }
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

            $sql_rechazar = "UPDATE formulario_ticket SET form_estado = 0 WHERE form_tick_num = '{$ticket_numero}'";
            $resultado = self::SQL($sql_rechazar);

            if ($resultado) {
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

    // NUEVAS FUNCIONES PARA ASIGNACIÓN (MOVIDAS DESDE AsignacionTicketController)

    public static function buscarOficialesAPI()
    {
        try {
            $sql = "SELECT 
                        mp.per_catalogo,
                        mp.per_nom1,
                        mp.per_ape1,
                        mp.per_grado
                    FROM mper mp 
                    WHERE mp.per_situacion = 1  
                    ORDER BY mp.per_grado, mp.per_nom1";
                    
            $oficiales = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Oficiales disponibles obtenidos correctamente',
                'data' => $oficiales
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener oficiales disponibles',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function asignarAPI()
    {
        try {
            $ticket_numero = $_POST['ticket_numero'] ?? '';
            $oficial_id = $_POST['oficial_id'] ?? '';

            if (empty($ticket_numero)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El número del ticket es obligatorio'
                ]);
                return;
            }

            if (empty($oficial_id)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar un oficial para asignar'
                ]);
                return;
            }

            $sql_datos_correo = "SELECT 
                                    ft.tic_correo_electronico,
                                    ft.tic_comentario_falla,
                                    mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_nom2 || ' ' || mp_solicitante.per_ape1 AS solicitante_nombre
                                FROM formulario_ticket ft
                                INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                                WHERE ft.form_tick_num = '{$ticket_numero}'";
            
            $datos_ticket = self::fetchFirst($sql_datos_correo);

            $sql_oficial_nombre = "SELECT per_nom1 || ' ' || per_ape1 as nombre FROM mper WHERE per_catalogo = '{$oficial_id}'";
            $oficial = self::fetchFirst($sql_oficial_nombre);
            $nombre_oficial = $oficial['nombre'] ?? 'Oficial Asignado';

            $sql_verificar = "SELECT form_tick_num 
                             FROM formulario_ticket 
                             WHERE form_tick_num = '$ticket_numero' 
                             AND form_estado = 1
                             AND form_tick_num NOT IN (
                                 SELECT tic_numero_ticket 
                                 FROM tickets_asignados 
                                 WHERE tic_numero_ticket IS NOT NULL
                             )";
            
            $ticket_existente = self::fetchFirst($sql_verificar);

            if (!$ticket_existente) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El ticket no existe o ya ha sido asignado'
                ]);
                return;
            }

            $sql_oficial_check = "SELECT per_catalogo FROM mper WHERE per_catalogo = '$oficial_id' AND per_situacion = 1";
            $oficial_existente = self::fetchFirst($sql_oficial_check);

            if (!$oficial_existente) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El oficial seleccionado no existe o no está activo'
                ]);
                return;
            }

            // Asignar con estado 2 (EN PROCESO) directamente
            $sql_asignar = "INSERT INTO tickets_asignados (tic_numero_ticket, tic_encargado, estado_ticket, tic_situacion) 
                           VALUES ('$ticket_numero', '$oficial_id', 2, 1)";
            
            $resultado = self::SQL($sql_asignar);

            if ($resultado) {
                $correo_enviado = false;
                try {
                    $datos_correo = [
                        'numero' => $ticket_numero,
                        'solicitante' => $datos_ticket['solicitante_nombre'] ?? 'Usuario',
                        'descripcion' => $datos_ticket['tic_comentario_falla'] ?? '',
                        'tecnico' => $nombre_oficial
                    ];
                    
                    if ($datos_ticket && !empty($datos_ticket['tic_correo_electronico'])) {
                        $resultado_correo = EmailController::enviarNotificacionTicket(
                            'asignado',
                            $datos_correo,
                            $datos_ticket['tic_correo_electronico']
                        );
                        
                        $correo_enviado = $resultado_correo['exito'] ?? false;
                        
                        if (!$correo_enviado) {
                            error_log('Error al enviar correo de asignación: ' . ($resultado_correo['mensaje'] ?? 'Error desconocido'));
                        }
                    }
                    
                } catch (Exception $e) {
                    error_log('Error en sistema de correos: ' . $e->getMessage());
                }

                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Ticket asignado correctamente y cambiado a EN PROCESO',
                    'ticket_numero' => $ticket_numero,
                    'oficial_asignado' => $nombre_oficial,
                    'correo_enviado' => $correo_enviado
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se pudo asignar el ticket'
                ]);
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al asignar el ticket',
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
            // Devolver estados 1, 2 y 0 (rechazado) para esta vista
            $sql = "SELECT est_tic_id, est_tic_desc 
                    FROM estado_ticket
                    WHERE est_tic_id IN (1, 2)
                    UNION ALL
                    SELECT 0 as est_tic_id, 'RECHAZADO' as est_tic_desc
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

    // NUEVA FUNCIÓN PARA REVERTIR TICKETS RECHAZADOS
    public static function revertirAPI()
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

            // Verificar que el ticket esté realmente rechazado
            $sql_check = "SELECT form_tick_num FROM formulario_ticket WHERE form_tick_num = '{$ticket_numero}' AND form_estado = 0";
            $ticket_rechazado = self::fetchFirst($sql_check);
            
            if (!$ticket_rechazado) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El ticket no existe o no está rechazado'
                ]);
                return;
            }

            // Revertir el ticket a estado RECIBIDO
            $sql_revertir = "UPDATE formulario_ticket SET form_estado = 1 WHERE form_tick_num = '{$ticket_numero}'";
            $resultado = self::SQL($sql_revertir);

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'El ticket ha sido revertido a RECIBIDO correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se pudo revertir el ticket'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al revertir el ticket',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}