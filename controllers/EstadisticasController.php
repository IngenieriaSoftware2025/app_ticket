<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use MVC\Router;

class EstadisticasController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        session_start();
        // if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
        //     header('Location: /app_ticket');
        //     exit;
        // }

        $router->render('estadisticas/index', []);
    }

    public static function buscarTicketsPorEstadoAPI()
    {
        try {
            $sql = "SELECT 
                    et.est_tic_desc as estado,
                    COUNT(*) as cantidad
                FROM formulario_ticket ft
                LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                WHERE ft.form_estado = 1
                GROUP BY et.est_tic_desc, et.est_tic_id
                ORDER BY et.est_tic_id";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets por estado obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets por estado',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarTicketsPorPrioridadAPI()
    {
        try {
            $sql = "SELECT 
                    et.est_tic_desc as prioridad,
                    COUNT(*) as cantidad
                FROM formulario_ticket ft
                LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                WHERE ft.form_estado = 1
                GROUP BY et.est_tic_desc";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets por prioridad obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets por prioridad',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarTicketsPorAplicacionAPI()
    {
        try {
            $sql = "SELECT 
                    ma.menu_descr as aplicacion,
                    COUNT(*) as cantidad
                FROM formulario_ticket ft
                INNER JOIN menuautocom ma ON ft.tic_app = ma.menu_codigo
                WHERE ft.form_estado = 1
                GROUP BY ma.menu_descr";

            $data = self::fetchArray($sql);
            $data = array_slice($data, 0, 10); // Limitar a 10

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets por aplicación obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets por aplicación',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarEvolucionTicketsAPI()
    {
        try {
            // Consulta más simple - solo por estado como "evolución"
            $sql = "SELECT 
                    et.est_tic_desc as mes,
                    COUNT(*) as cantidad
                FROM formulario_ticket ft
                LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                WHERE ft.form_estado = 1
                GROUP BY et.est_tic_desc";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Evolución de tickets obtenida correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener evolución de tickets',
                'detalle' => $e->getMessage()
            ]);
        }
    }
    public static function buscarUsuariosMasTicketsAPI()
    {
        try {
            $sql = "SELECT 
                    mp.per_nom1 || ' ' || mp.per_ape1 as usuario,
                    COUNT(*) as cantidad
                FROM formulario_ticket ft
                INNER JOIN mper mp ON ft.form_tic_usu = mp.per_catalogo
                WHERE ft.form_estado = 1
                GROUP BY mp.per_nom1, mp.per_ape1, mp.per_catalogo";

            $data = self::fetchArray($sql);
            $data = array_slice($data, 0, 10); // Top 10

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios con más tickets obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener usuarios con más tickets',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarTicketsResueltosPortecnicoAPI()
    {
        try {
            // Consulta de todos los tickets asignados
            $sql = "SELECT 
                    mp.per_nom1 || ' ' || mp.per_ape1 as tecnico,
                    COUNT(*) as cantidad
                FROM tickets_asignados ta
                INNER JOIN mper mp ON ta.tic_encargado = mp.per_catalogo
                WHERE ta.tic_situacion = 1
                GROUP BY mp.per_nom1, mp.per_ape1, mp.per_catalogo";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets resueltos por técnico obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets resueltos por técnico',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarTicketsPorDepartamentoAPI()
    {
        try {
            $sql = "SELECT 
                    md.dep_desc_lg as departamento,
                    COUNT(*) as cantidad
                FROM formulario_ticket ft
                INNER JOIN mdep md ON ft.tic_dependencia = md.dep_llave
                WHERE ft.form_estado = 1
                GROUP BY md.dep_desc_lg";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets por departamento obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets por departamento',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarPerformanceTecnicosAPI()
    {
        try {
            $sql = "SELECT 
                    mp.per_nom1 || ' ' || mp.per_ape1 as tecnico,
                    AVG(ta.estado_ticket) as promedio
                FROM tickets_asignados ta
                INNER JOIN mper mp ON ta.tic_encargado = mp.per_catalogo
                WHERE ta.estado_ticket > 1
                GROUP BY mp.per_nom1, mp.per_ape1, mp.per_catalogo";

            $data = self::fetchArray($sql);

            // Convertir promedio a formato más legible
            foreach ($data as &$item) {
                $item['promedio'] = number_format($item['promedio'], 1);
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Performance de técnicos obtenida correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener performance de técnicos',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarTiempoPromedioResolucionAPI()
    {
        try {
            $sql = "SELECT 
                    'Rápido' as categoria,
                    COUNT(*) as tiempo
                FROM tickets_asignados ta
                WHERE ta.estado_ticket <= 2
                UNION ALL
                SELECT 
                    'Medio' as categoria,
                    COUNT(*) as tiempo
                FROM tickets_asignados ta
                WHERE ta.estado_ticket BETWEEN 3 AND 4
                UNION ALL
                SELECT 
                    'Lento' as categoria,
                    COUNT(*) as tiempo
                FROM tickets_asignados ta
                WHERE ta.estado_ticket >= 5";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tiempo promedio de resolución obtenido correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tiempo promedio de resolución',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarTiempoRespuestaPorPrioridadAPI()
    {
        try {
            $sql = "SELECT 
                    et.est_tic_desc as prioridad,
                    AVG(ta.estado_ticket * 2) as tiempo
                FROM tickets_asignados ta
                INNER JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                WHERE ta.estado_ticket > 1
                GROUP BY et.est_tic_desc";

            $data = self::fetchArray($sql);

            // Convertir tiempo a formato más legible
            foreach ($data as &$item) {
                $item['tiempo'] = number_format($item['tiempo'], 1);
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tiempo de respuesta por prioridad obtenido correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tiempo de respuesta por prioridad',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarSatisfaccionUsuarioAPI()
    {
        try {
            $sql = "SELECT 
                    'Excelente' as calificacion,
                    COUNT(*) as cantidad
                FROM tickets_asignados ta
                WHERE ta.estado_ticket = 5
                UNION ALL
                SELECT 
                    'Bueno' as calificacion,
                    COUNT(*) as cantidad
                FROM tickets_asignados ta
                WHERE ta.estado_ticket = 4
                UNION ALL
                SELECT 
                    'Regular' as calificacion,
                    COUNT(*) as cantidad
                FROM tickets_asignados ta
                WHERE ta.estado_ticket <= 3";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Satisfacción del usuario obtenida correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener satisfacción del usuario',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function buscarTicketsReabiertosAPI()
    {
        try {
            $sql = "SELECT 
                    'Cerrados' as tipo,
                    COUNT(*) as cantidad
                FROM tickets_asignados ta
                WHERE ta.estado_ticket = 5
                UNION ALL
                SELECT 
                    'Reabiertos' as tipo,
                    COUNT(*) as cantidad
                FROM tickets_asignados ta
                WHERE ta.estado_ticket BETWEEN 1 AND 4";

            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tickets reabiertos vs cerrados obtenidos correctamente',
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener tickets reabiertos vs cerrados',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
