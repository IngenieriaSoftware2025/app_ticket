<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Controllers\EmailController;

class AsignacionTicketController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        $router->render('asignacion/index', []);
    }

    public static function buscarAPI()
    {
        try {
            $sql = "SELECT 
                        ft.form_tick_num as tic_numero_ticket,
                        ft.tic_comentario_falla,
                        gma.gma_desc as aplicacion,
                        mp_solicitante.per_nom1 as solicitante_nombre,
                        md.dep_desc_lg AS dependencia_nombre,
                        ft.form_fecha_creacion,
                        ft.tic_imagen,
                        ft.form_tick_num as tic_id
                    FROM formulario_ticket ft
                    INNER JOIN grupo_menuautocom gma ON ft.tic_app = gma.gma_codigo
                    INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                    INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                    WHERE ft.form_estado = 1  
                    AND ft.form_tick_num NOT IN (
                        SELECT tic_numero_ticket 
                        FROM tickets_asignados 
                        WHERE tic_numero_ticket IS NOT NULL
                    )
                    ORDER BY ft.form_fecha_creacion DESC";
                    
            $tickets = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets pendientes de asignación obtenidos correctamente',
                'data' => $tickets
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets pendientes de asignación',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

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
                    'mensaje' => 'Ticket asignado correctamente',
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
}