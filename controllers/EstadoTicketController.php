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

            $_POST['estado_ticket'] = filter_var($_POST['estado_ticket'], FILTER_SANITIZE_NUMBER_INT);
            
            if (empty($_POST['estado_ticket']) || $_POST['estado_ticket'] < 1) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar un estado'
                ]);
                exit;
            }
            
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

            $condiciones = ["ta.tic_id IS NOT NULL"];

            if ($fecha_inicio) {
                $condiciones[] = "ft.form_fecha_creacion >= '{$fecha_inicio}'";
            }

            if ($fecha_fin) {
                $condiciones[] = "ft.form_fecha_creacion <= '{$fecha_fin}'";
            }

            if ($estado) {
                $condiciones[] = "ta.estado_ticket = {$estado}";
            }

            $where = implode(" AND ", $condiciones);
            $sql = "SELECT ta.*, ft.form_tick_num, ft.tic_comentario_falla, ft.tic_correo_electronico, 
                           ft.form_fecha_creacion, ft.tic_imagen,
                           mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_nom2 || ' ' || mp_solicitante.per_ape1 AS solicitante_nombre,
                           mp_encargado.per_nom1 || ' ' || mp_encargado.per_nom2 || ' ' || mp_encargado.per_ape1 AS encargado_nombre,
                           et.est_tic_desc AS estado_descripcion,
                           md.dep_desc_lg AS dependencia_nombre
                    FROM tickets_asignados ta
                    INNER JOIN formulario_ticket ft ON ta.tic_numero_ticket = ft.form_tick_num
                    INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                    INNER JOIN mper mp_encargado ON ta.tic_encargado = mp_encargado.per_catalogo
                    INNER JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                    INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                    WHERE $where 
                    ORDER BY ft.form_fecha_creacion DESC";
            $data = self::fetchArray($sql);

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

    public static function modificarAPI()
    {
        getHeadersApi();

        $id = $_POST['tic_id'];
        $_POST['tic_encargado'] = filter_var($_POST['tic_encargado'], FILTER_SANITIZE_NUMBER_INT);
        
        if (empty($_POST['tic_encargado']) || $_POST['tic_encargado'] < 1) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Debe seleccionar un técnico encargado'
            ]);
            return;
        }

        $_POST['estado_ticket'] = filter_var($_POST['estado_ticket'], FILTER_SANITIZE_NUMBER_INT);
        
        if (empty($_POST['estado_ticket']) || $_POST['estado_ticket'] < 1) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Debe seleccionar un estado'
            ]);
            return;
        }

        try {
            $data = TicketAsignado::find($id);
            $data->sincronizar([
                'tic_encargado' => $_POST['tic_encargado'],
                'estado_ticket' => $_POST['estado_ticket']
            ]);
            $data->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'El ticket ha sido modificado exitosamente'
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al guardar',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function EliminarAPI()
    {
        try {
            $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            $ejecutar = TicketAsignado::EliminarTicketAsignado($id);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'El ticket ha sido eliminado correctamente'
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al eliminar',
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

    public static function cambiarEstadoAPI()
    {
        getHeadersApi();

        try {
            $id = filter_var($_POST['tic_id'], FILTER_SANITIZE_NUMBER_INT);
            $nuevoEstado = filter_var($_POST['nuevo_estado'], FILTER_SANITIZE_NUMBER_INT);
            
            if (empty($id) || $id < 1) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'ID de ticket inválido'
                ]);
                return;
            }

            if (empty($nuevoEstado) || $nuevoEstado < 1) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Estado inválido'
                ]);
                return;
            }

            $data = TicketAsignado::find($id);
            $data->sincronizar([
                'estado_ticket' => $nuevoEstado
            ]);
            $data->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estado del ticket actualizado correctamente'
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
}