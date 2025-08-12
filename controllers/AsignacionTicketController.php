<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;

class AsignacionTicketController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        $router->render('asignacion/index', []);
    }

    public static function buscarAPI()
    {
        try {
            // Mostrar tickets de formulario_ticket que NO están asignados
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
            // Consulta SIMPLIFICADA para obtener oficiales disponibles
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
            // Validar datos recibidos
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

            // Verificar que el ticket existe en formulario_ticket y no está asignado
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

            // Verificar que el oficial existe y está activo
            $sql_oficial = "SELECT per_catalogo FROM mper WHERE per_catalogo = '$oficial_id' AND per_situacion = 1";
            $oficial_existente = self::fetchFirst($sql_oficial);

            if (!$oficial_existente) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El oficial seleccionado no existe o no está activo'
                ]);
                return;
            }

            // Crear registro en tickets_asignados con estado "EN PROCESO" (2)
            $sql_asignar = "INSERT INTO tickets_asignados (tic_numero_ticket, tic_encargado, estado_ticket, tic_situacion) 
                           VALUES ('$ticket_numero', '$oficial_id', 2, 1)";
            
            $resultado = self::SQL($sql_asignar);

            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Ticket asignado correctamente',
                    'ticket_numero' => $ticket_numero
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