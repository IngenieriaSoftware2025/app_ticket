<?php

namespace Classes;

use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use Exception;

class Email
{
    public $mail = null;

    public function __construct()
    {
        // Verificar variables de entorno
        if (!isset($_ENV['EMAIL_HOST']) || !isset($_ENV['EMAIL_USERNAME']) || 
            !isset($_ENV['EMAIL_PASSWORD']) || !isset($_ENV['EMAIL_PORT']) || 
            !isset($_ENV['EMAIL_FROM_ADDRESS'])) {
            throw new Exception('Las variables de entorno para correo no están configuradas.');
        }

        $this->mail = new PHPMailer(true);
        
        // Configuración SMTP específica para Procodegt
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Configuración de debug solo para testing
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF; // Cambiar a DEBUG_SERVER para ver errores
        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['EMAIL_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['EMAIL_USERNAME'];
        $this->mail->Password = $_ENV['EMAIL_PASSWORD'];
        
        // Configuración para tu servidor Procodegt
        if ($_ENV['EMAIL_PORT'] == 465) {
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL para puerto 465
        } else {
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS para puerto 587
        }
        
        $this->mail->Port = $_ENV['EMAIL_PORT'];
        $this->mail->CharSet = "UTF-8";
        
        // Configurar remitente con tu configuración
        $nombre_remitente = $_ENV['EMAIL_FROM_NAME'] ?? 'Sistema de Tickets - AUTOCOM';
        $this->mail->setFrom($_ENV['EMAIL_FROM_ADDRESS'], $nombre_remitente);
        $this->mail->addReplyTo($_ENV['EMAIL_FROM_ADDRESS'], $nombre_remitente);
        
        $this->mail->isHTML(true);
    }

    public function enviar($destinatario, $asunto, $cuerpo_html)
    {
        try {
            // Limpiar destinatarios previos
            $this->mail->clearAddresses();
            
            $this->mail->addAddress($destinatario);
            $this->mail->Subject = $asunto;
            $this->mail->Body = $cuerpo_html;
            
            $resultado = $this->mail->send();
            return [
                'exito' => true,
                'mensaje' => 'Correo enviado correctamente'
            ];
        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error al enviar correo: ' . $this->mail->ErrorInfo,
                'detalle' => $e->getMessage()
            ];
        }
    }
}