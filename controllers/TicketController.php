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
        $router->render('ticket/index', []);
    }

    public static function guardarAPI()
    {
        getHeadersApi();
    
        try {
            // Validar usuario en sesión
            if (!$_POST['form_tic_usu']) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontró el catálogo de usuario en la sesión'
                ]);
                exit;
            }

            // Validar dependencia en sesión
            if (!$_POST['tic_dependencia']) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontró la dependencia del usuario en la sesión'
                ]);
                exit;
            }

            // Obtener datos del usuario desde mper automáticamente
            $sqlUsuario = "SELECT per_telefono, per_nom1, per_nom2, per_ape1, per_desc_empleo 
                          FROM mper 
                          WHERE per_catalogo = {$_POST['form_tic_usu']}";
            $datosUsuario = self::fetchFirst($sqlUsuario);

            if (!$datosUsuario) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontraron datos del usuario en el sistema'
                ]);
                exit;
            }

            // Asignar teléfono automáticamente
            $_POST['tic_telefono'] = $datosUsuario['per_telefono'];
            
            if (empty($_POST['tic_telefono'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario no tiene teléfono registrado en el sistema'
                ]);
                exit;
            }

            // Validar aplicación
            $_POST['tic_app'] = filter_var($_POST['tic_app'], FILTER_SANITIZE_NUMBER_INT);
            
            if (empty($_POST['tic_app']) || $_POST['tic_app'] < 1) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar una aplicación'
                ]);
                exit;
            }

            // Validar correo electrónico (ingresado manualmente)
            $_POST['tic_correo_electronico'] = filter_var($_POST['tic_correo_electronico'], FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($_POST['tic_correo_electronico'], FILTER_VALIDATE_EMAIL)){
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El correo electrónico no es válido'
                ]);
                exit;
            }

            if (strlen($_POST['tic_correo_electronico']) > 100) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El correo electrónico no puede exceder 100 caracteres'
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
            $_POST['form_estado'] = 1; // Campo estado por defecto: 1 = Activo

            // Validar y procesar imágenes si existen
            $rutasImagenes = [];
            if (isset($_FILES['tic_imagen']) && !empty($_FILES['tic_imagen']['name'][0])) {
                $totalArchivos = count($_FILES['tic_imagen']['name']);
                
                // Validar máximo de imágenes (ej: 5 imágenes máximo)
                if ($totalArchivos > 5) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'No se pueden subir más de 5 imágenes'
                    ]);
                    exit;
                }

                for ($i = 0; $i < $totalArchivos; $i++) {
                    if ($_FILES['tic_imagen']['error'][$i] === UPLOAD_ERR_OK) {
                        $nombreArchivo = $_FILES['tic_imagen']['name'][$i];
                        $archivoTemporal = $_FILES['tic_imagen']['tmp_name'][$i];
                        $tamañoArchivo = $_FILES['tic_imagen']['size'][$i];

                        // Validar extensión
                        $extensionArchivo = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
                        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                        if (!in_array($extensionArchivo, $extensionesPermitidas)) {
                            // Limpiar archivos ya subidos
                            foreach ($rutasImagenes as $rutaEliminar) {
                                if (file_exists(__DIR__ . "/../../" . $rutaEliminar)) {
                                    unlink(__DIR__ . "/../../" . $rutaEliminar);
                                }
                            }
                            http_response_code(400);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "Solo se permiten archivos de imagen: JPG, PNG, GIF, WEBP (archivo: $nombreArchivo)"
                            ]);
                            exit;
                        }

                        // Validar tamaño (8MB máximo por imagen)
                        if ($tamañoArchivo > 8 * 1024 * 1024) {
                            // Limpiar archivos ya subidos
                            foreach ($rutasImagenes as $rutaEliminar) {
                                if (file_exists(__DIR__ . "/../../" . $rutaEliminar)) {
                                    unlink(__DIR__ . "/../../" . $rutaEliminar);
                                }
                            }
                            http_response_code(400);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "La imagen no puede ser mayor a 8MB (archivo: $nombreArchivo)"
                            ]);
                            exit;
                        }

                        // Validar que sea realmente una imagen
                        $infoImagen = getimagesize($archivoTemporal);
                        if ($infoImagen === false) {
                            // Limpiar archivos ya subidos
                            foreach ($rutasImagenes as $rutaEliminar) {
                                if (file_exists(__DIR__ . "/../../" . $rutaEliminar)) {
                                    unlink(__DIR__ . "/../../" . $rutaEliminar);
                                }
                            }
                            http_response_code(400);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "El archivo no es una imagen válida (archivo: $nombreArchivo)"
                            ]);
                            exit;
                        }

                        // Generar nombre único para el archivo
                        $nombreUnico = $_POST['form_tick_num'] . '_' . ($i + 1) . '_' . time() . '.' . $extensionArchivo;
                        $rutaDestino = "storage/imagenesTickets/$nombreUnico";
                        $rutaCompleta = __DIR__ . "/../../" . $rutaDestino;

                        // Crear directorio si no existe
                        $directorio = dirname($rutaCompleta);
                        if (!is_dir($directorio)) {
                            mkdir($directorio, 0755, true);
                        }

                        // Mover archivo
                        if (move_uploaded_file($archivoTemporal, $rutaCompleta)) {
                            $rutasImagenes[] = $rutaDestino;
                        } else {
                            // Limpiar archivos ya subidos
                            foreach ($rutasImagenes as $rutaEliminar) {
                                if (file_exists(__DIR__ . "/../../" . $rutaEliminar)) {
                                    unlink(__DIR__ . "/../../" . $rutaEliminar);
                                }
                            }
                            http_response_code(500);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => 'Error al subir las imágenes'
                            ]);
                            exit;
                        }
                    }
                }
            }

            $ticket = new FormularioTicket($_POST);
            if (!empty($rutasImagenes)) {
                // Guardar las rutas como JSON o separadas por comas
                $ticket->tic_imagen = json_encode($rutasImagenes);
            }
            $resultado = $ticket->crear();

            if($resultado['resultado'] == 1){
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Ticket creado correctamente',
                    'data' => [
                        'numero_ticket' => $_POST['form_tick_num'],
                        'id' => $resultado['id'],
                        'nombre_usuario' => trim($datosUsuario['per_nom1'] . ' ' . $datosUsuario['per_nom2'] . ' ' . $datosUsuario['per_ape1']),
                        'telefono_usuario' => $datosUsuario['per_telefono'],
                        'correo_usuario' => $_POST['tic_correo_electronico'],
                        'imagenes' => $rutasImagenes
                    ]
                ]);
                exit;
            } else {
                // Si falla, eliminar imágenes subidas
                foreach ($rutasImagenes as $rutaEliminar) {
                    if (file_exists(__DIR__ . "/../../" . $rutaEliminar)) {
                        unlink(__DIR__ . "/../../" . $rutaEliminar);
                    }
                }
                
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al crear el ticket'
                ]);
                exit;
            }
            
        } catch (Exception $e) {
            // Si falla, eliminar imágenes subidas
            if (isset($rutasImagenes) && !empty($rutasImagenes)) {
                foreach ($rutasImagenes as $rutaEliminar) {
                    if (file_exists(__DIR__ . "/../../" . $rutaEliminar)) {
                        unlink(__DIR__ . "/../../" . $rutaEliminar);
                    }
                }
            }

            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
            exit;
        }
    }

    public static function obtenerAplicacionesAPI() 
    {
        getHeadersApi();
        
        try {
            $sql = "SELECT menu_codigo, menu_descr 
                    FROM menuautocom 
                    WHERE menu_situacion = 1 
                    ORDER BY menu_descr";
            $data = self::fetchArray($sql);
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Aplicaciones obtenidas correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las aplicaciones',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}