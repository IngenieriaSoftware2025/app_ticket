<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;

class HistorialTicketsController extends ActiveRecord
{
    // Constantes para estados de tickets
    const ESTADO_ACTIVO = 1;
    const ESTADO_RECHAZADO = 0;
    
    // Constantes para tipos de vista
    const TIPO_RECIBIDOS = 'recibidos';
    const TIPO_FINALIZADOS = 'finalizados';
    const TIPO_RECHAZADOS = 'rechazados';

    public static function renderizarPagina(Router $router)
    {
        $router->render('historial/index', []);
    }

    public static function buscarAPI()
    {
        try {
            // Obtener y validar parámetros
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            $tipo = $_GET['tipo'] ?? self::TIPO_RECIBIDOS;

            // Limpiar y validar fechas
            $fechaInicio = trim($fechaInicio);
            $fechaFin = trim($fechaFin);
            
            // Validar tipo de vista
            $tiposValidos = [self::TIPO_RECIBIDOS, self::TIPO_FINALIZADOS, self::TIPO_RECHAZADOS];
            if (!in_array($tipo, $tiposValidos)) {
                $tipo = self::TIPO_RECIBIDOS;
            }

            // Construir condiciones base
            $condiciones = ["ft.form_tick_num IS NOT NULL"];
            
            // Filtrar por estado del formulario según el tipo
            if ($tipo === self::TIPO_RECHAZADOS) {
                $condiciones[] = "ft.form_estado = " . self::ESTADO_RECHAZADO;
            } else {
                $condiciones[] = "ft.form_estado = " . self::ESTADO_ACTIVO;
            }

            // Agregar filtros de fecha 
            if (!empty($fechaInicio) && self::validarFecha($fechaInicio)) {
                // Para DATETIME YEAR TO SECOND: 'YYYY-MM-DD HH:MM:SS'
                $condiciones[] = "ft.form_fecha_creacion >= '{$fechaInicio} 00:00:00'";
            }
            
            if (!empty($fechaFin) && self::validarFecha($fechaFin)) {
                $condiciones[] = "ft.form_fecha_creacion <= '{$fechaFin} 23:59:59'";
            }

            $where = implode(" AND ", $condiciones);
            
            // Consulta principal 
            $sql = "SELECT ft.form_tick_num, 
                           ft.tic_comentario_falla, 
                           ft.tic_correo_electronico, 
                           ft.form_fecha_creacion, 
                           ft.tic_imagen,
                           ft.form_tic_usu,
                           CASE 
                               WHEN mp_solicitante.per_nom2 IS NOT NULL AND mp_solicitante.per_nom2 != '' 
                               THEN mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_nom2 || ' ' || mp_solicitante.per_ape1
                               ELSE mp_solicitante.per_nom1 || ' ' || mp_solicitante.per_ape1
                           END AS solicitante_nombre,
                           md.dep_desc_lg AS dependencia_nombre
                    FROM formulario_ticket ft
                    INNER JOIN mper mp_solicitante ON ft.form_tic_usu = mp_solicitante.per_catalogo
                    INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                    WHERE $where 
                    ORDER BY ft.form_fecha_creacion DESC";
                    
            error_log("SQL Query: " . $sql);
            
            $tickets = self::fetchArray($sql);
            
            // Procesar tickets según el tipo solicitado
            $data = [];
            
            if ($tipo === self::TIPO_RECHAZADOS) {
                // Para rechazados, agregar información básica
                foreach ($tickets as $ticket) {
                    $ticket['tic_id'] = $ticket['form_tick_num'];
                    $ticket['encargado_nombre'] = 'SIN ASIGNAR';
                    $ticket['estado_descripcion'] = 'RECHAZADO';
                    $ticket['estado_ticket'] = self::ESTADO_RECHAZADO;
                    $data[] = $ticket;
                }
            } else {
                // Para recibidos y finalizados, obtener información de asignación
                foreach ($tickets as $ticket) {
                    $ticketConEstado = self::obtenerEstadoTicket($ticket);
                    
                    // Filtrar según el tipo solicitado
                    if (self::esTicketDelTipoSolicitado($ticketConEstado, $tipo)) {
                        $data[] = $ticketConEstado;
                    }
                }
            }

            // Determinar mensaje según el tipo
            $mensajesTipo = [
                self::TIPO_RECIBIDOS => 'Tickets recibidos',
                self::TIPO_FINALIZADOS => 'Tickets finalizados',
                self::TIPO_RECHAZADOS => 'Tickets rechazados'
            ];

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => $mensajesTipo[$tipo] . ' obtenidos correctamente',
                'data' => $data,
                'tipo' => $tipo,
                'total' => count($data)
            ]);

        } catch (Exception $e) {
            error_log("Error en HistorialTicketsController::buscarAPI: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener el historial de tickets',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    // Validar formato de fecha para Informix
    private static function validarFecha($fecha)
    {
        if (empty($fecha) || $fecha === null) {
            return false;
        }
        
        // Validar formato YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return false;
        }
        
        // Validar que sea una fecha válida
        $fechaObj = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $fechaObj && $fechaObj->format('Y-m-d') === $fecha;
    }

    // Obtiene el estado de un ticket de la tabla tickets_asignados
    private static function obtenerEstadoTicket($ticket)
    {
        try {
            // Escapar el número de ticket para evitar inyección SQL
            $numeroTicket = self::escaparString($ticket['form_tick_num']);
            
            $sqlAsignado = "SELECT ta.tic_id, ta.tic_encargado, ta.estado_ticket,
                                   CASE 
                                       WHEN mp_encargado.per_nom2 IS NOT NULL AND mp_encargado.per_nom2 != '' 
                                       THEN mp_encargado.per_nom1 || ' ' || mp_encargado.per_nom2 || ' ' || mp_encargado.per_ape1
                                       ELSE mp_encargado.per_nom1 || ' ' || mp_encargado.per_ape1
                                   END AS encargado_nombre,
                                   et.est_tic_desc AS estado_descripcion
                            FROM tickets_asignados ta
                            INNER JOIN mper mp_encargado ON ta.tic_encargado = mp_encargado.per_catalogo
                            INNER JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                            WHERE ta.tic_numero_ticket = '$numeroTicket' 
                            AND ta.tic_situacion = 1";
            
            $asignado = self::fetchFirst($sqlAsignado);
            
            if ($asignado) {
                // Si el ticket está asignado
                $ticket['tic_id'] = $asignado['tic_id'];
                $ticket['encargado_nombre'] = $asignado['encargado_nombre'];
                $ticket['estado_descripcion'] = $asignado['estado_descripcion'];
                $ticket['estado_ticket'] = $asignado['estado_ticket'];
            } else {
                // Si no está asignado, es un ticket recibido
                $ticket['tic_id'] = $ticket['form_tick_num'];
                $ticket['encargado_nombre'] = 'SIN ASIGNAR';
                $ticket['estado_descripcion'] = 'RECIBIDO';
                $ticket['estado_ticket'] = 1;
            }
            
        } catch (Exception $e) {
            error_log("Error en obtenerEstadoTicket: " . $e->getMessage());
            // En caso de error, devolver como recibido
            $ticket['tic_id'] = $ticket['form_tick_num'];
            $ticket['encargado_nombre'] = 'SIN ASIGNAR';
            $ticket['estado_descripcion'] = 'RECIBIDO';
            $ticket['estado_ticket'] = 1;
        }
        
        return $ticket;
    }

    // Verifica si un ticket pertenece al tipo solicitado
    private static function esTicketDelTipoSolicitado($ticket, $tipo)
    {
        $estadoTicket = (int)$ticket['estado_ticket'];
        
        switch ($tipo) {
            case self::TIPO_RECIBIDOS:
                // Estados 1-2 son recibidos y en proceso
                return $estadoTicket >= 1 && $estadoTicket <= 2;
                
            case self::TIPO_FINALIZADOS:
                return $estadoTicket === 3;
                
            default:
                return false;
        }
    }

    private static function escaparString($string)
    {
        return addslashes($string);
    }
}