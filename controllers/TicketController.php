<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\FormularioTicket;

class TicketController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        hasPermission(['ADMIN', 'EMPLEADO']);
        $router->render('ticket/index', []);
    }

    public static function guardarAPI()
    {
        hasPermissionApi(['ADMIN', 'EMPLEADO']);
        getHeadersApi();
    
        
        // Obtener datos automáticos de la sesión
        $_POST['form_tic_usu'] = $_SESSION['per_catalogo'] ?? null;
        $_POST['tic_dependencia'] = $_SESSION['dep_llave'] ?? null;

        // Validar que existan los datos de sesión
        if (!$_POST['form_tic_usu']) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'No se encontró el catálogo de usuario en la sesión'
            ]);
            exit;
        }

        if (!$_POST['tic_dependencia']) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'No se encontró la dependencia del usuario en la sesión'
            ]);
            exit;
        }

        // Validar correo electrónico
        $_POST['tic_correo_electronico'] = filter_var($_POST['tic_correo_electronico'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($_POST['tic_correo_electronico'], FILTER_VALIDATE_EMAIL)){
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El correo electrónico no es válido'
            ]);
            exit;
        }

        if (strlen($_POST['tic_correo_electronico']) > 250) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El correo electrónico no puede exceder 250 caracteres'
            ]);
            exit;
        }

        // Validar descripción del problema
        $_POST['tic_comentario_falla'] = trim(htmlspecialchars($_POST['tic_comentario_falla']));
        $cantidad_comentario = strlen($_POST['tic_comentario_falla']);
        
        if ($cantidad_comentario < 15) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'La descripción del problema debe tener al menos 15 caracteres'
            ]);
            exit;
        }

        if ($cantidad_comentario > 2000) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'La descripción del problema no puede exceder 2000 caracteres'
            ]);
            exit;
        }

        // Generar número de ticket único
        $_POST['form_tick_num'] = 'TK' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $_POST['form_fecha_creacion'] = '';

        // Validar y procesar imagen si existe
        $rutaImagen = '';
        if (isset($_FILES['tic_imagen']) && $_FILES['tic_imagen']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['tic_imagen'];
            $nombreArchivo = $archivo['name'];
            $archivoTemporal = $archivo['tmp_name'];
            $tamañoArchivo = $archivo['size'];

            // Validar extensión
            $extensionArchivo = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($extensionArchivo, $extensionesPermitidas)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se permiten archivos de imagen: JPG, PNG, GIF, WEBP'
                ]);
                exit;
            }

            // Validar tamaño (8MB máximo)
            if ($tamañoArchivo > 8 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La imagen no puede ser mayor a 8MB'
                ]);
                exit;
            }

            // Validar que sea realmente una imagen
            $infoImagen = getimagesize($archivoTemporal);
            if ($infoImagen === false) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El archivo no es una imagen válida'
                ]);
                exit;
            }

            // Generar nombre único para el archivo
            $nombreUnico = $_POST['form_tick_num'] . '_' . time() . '.' . $extensionArchivo;
            $rutaDestino = "storage/imagenesTickets/$nombreUnico";
            $rutaCompleta = __DIR__ . "/../../" . $rutaDestino;

            // Crear directorio si no existe
            $directorio = dirname($rutaCompleta);
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Mover archivo
            if (move_uploaded_file($archivoTemporal, $rutaCompleta)) {
                $rutaImagen = $rutaDestino;
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al subir la imagen'
                ]);
                exit;
            }
        }

        try {
            $ticket = new FormularioTicket($_POST);
            if ($rutaImagen) {
                $ticket->tic_imagen = $rutaImagen;
            }
            $resultado = $ticket->crear();

            if($resultado['resultado'] == 1){
                
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Ticket enviado correctamente',
                    'data' => [
                        'numero_ticket' => $_POST['form_tick_num'],
                        'id' => $resultado['id']
                    ]
                ]);
            } else {
                // Si falla, eliminar imagen subida
                if ($rutaImagen && file_exists(__DIR__ . "/../../" . $rutaImagen)) {
                    unlink(__DIR__ . "/../../" . $rutaImagen);
                }

                
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al enviar el ticket'
                ]);
            }
        } catch (Exception $e) {
            // Si falla, eliminar imagen subida
            if ($rutaImagen && file_exists(__DIR__ . "/../../" . $rutaImagen)) {
                unlink(__DIR__ . "/../../" . $rutaImagen);
            }

            
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}