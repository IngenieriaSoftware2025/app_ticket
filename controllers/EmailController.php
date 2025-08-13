<?php

namespace Controllers;

use Classes\Email;
use Exception;
use MVC\Router;

class EmailController
{
    
    // Método principal para enviar notificaciones de tickets
    // Solo necesitas llamar a este método desde otros controladores
     
    public static function enviarNotificacionTicket($tipo_notificacion, $datos_ticket, $email_destinatario)
    {
        try {
            // Validaciones básicas
            if (empty($email_destinatario) || !filter_var($email_destinatario, FILTER_VALIDATE_EMAIL)) {
                return self::respuestaError('Email destinatario no válido');
            }

            if (empty($datos_ticket['numero'])) {
                return self::respuestaError('Número de ticket requerido');
            }

            // Generar asunto
            $asunto = self::generarAsunto($tipo_notificacion, $datos_ticket['numero']);
            
            // Generar contenido HTML usando la vista
            $contenido_html = self::generarHTML($tipo_notificacion, $datos_ticket);
            
            // Enviar correo
            $email = new Email();
            return $email->enviar($email_destinatario, $asunto, $contenido_html);
            
        } catch (Exception $e) {
            return self::respuestaError('Error en EmailController: ' . $e->getMessage());
        }
    }

    
     //Generar asunto del correo según el tipo de notificación
     
    private static function generarAsunto($tipo, $numero_ticket)
    {
        $asuntos = [
            'creado' => '🎫 Nuevo Ticket Creado',
            'asignado' => '👨‍💻 Ticket Asignado',
            'en_proceso' => '⚙️ Ticket En Proceso',
            'finalizado' => '✅ Ticket Finalizado',
            'rechazado' => '❌ Ticket Rechazado'
        ];

        $titulo = $asuntos[$tipo] ?? '📋 Actualización de Ticket';
        return $titulo . ' - ' . $numero_ticket;
    }

    
     //Obtener configuración visual según el tipo
     
    private static function obtenerConfiguracionTipo($tipo)
    {
        $configuraciones = [
            'creado' => [
                'color' => '#28a745',
                'icono' => '🎫',
                'titulo' => 'Ticket Creado',
                'mensaje' => 'Se ha creado un nuevo ticket en el sistema de soporte técnico.'
            ],
            'asignado' => [
                'color' => '#007bff',
                'icono' => '👨‍💻',
                'titulo' => 'Ticket Asignado',
                'mensaje' => 'Su ticket ha sido asignado a un técnico especializado.'
            ],
            'en_proceso' => [
                'color' => '#ffc107',
                'icono' => '⚙️',
                'titulo' => 'Ticket En Proceso',
                'mensaje' => 'Su ticket está siendo atendido por nuestro equipo técnico.'
            ],
            'finalizado' => [
                'color' => '#28a745',
                'icono' => '✅',
                'titulo' => 'Ticket Finalizado',
                'mensaje' => 'Su ticket ha sido resuelto satisfactoriamente.'
            ],
            'rechazado' => [
                'color' => '#dc3545',
                'icono' => '❌',
                'titulo' => 'Ticket Rechazado',
                'mensaje' => 'Su ticket ha sido rechazado. Puede crear uno nuevo con más información.'
            ]
        ];

        return $configuraciones[$tipo] ?? $configuraciones['creado'];
    }

    
     //Generar HTML usando la vista
     
    private static function generarHTML($tipo, $datos)
    {
        // Obtener configuración del tipo
        $config = self::obtenerConfiguracionTipo($tipo);
        $fecha_actual = date('d/m/Y H:i');
        
        // Capturar el output de la vista
        ob_start();
        
        // Incluir la vista con las variables disponibles
        include __DIR__ . '/../views/email/notificacion_ticket.php';
        
        $html = ob_get_clean();
        
        return $html;
    }

    
     //Respuesta estandarizada para errores
    
    private static function respuestaError($mensaje)
    {
        return [
            'exito' => false,
            'mensaje' => $mensaje
        ];
    }

    
     //Método de prueba para verificar configuración
     
    public static function probarConfiguracion()
    {
        getHeadersApi();
        
        try {
            $email_prueba = $_POST['email_prueba'] ?? '';
            
            if (empty($email_prueba)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Se requiere un email de prueba'
                ]);
                return;
            }

            $datos_prueba = [
                'numero' => 'TEST-' . date('YmdHis'),
                'solicitante' => 'Usuario de Prueba',
                'descripcion' => 'Esta es una prueba del sistema de correos del sistema de tickets.',
                'tecnico' => 'Técnico de Prueba'
            ];

            $resultado = self::enviarNotificacionTicket('creado', $datos_prueba, $email_prueba);
            
            if ($resultado['exito']) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Correo de prueba enviado correctamente',
                    'email_destino' => $email_prueba
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al enviar correo de prueba',
                    'detalle' => $resultado['mensaje']
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error en la prueba de correos',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}