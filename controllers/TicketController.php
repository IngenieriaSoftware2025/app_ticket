<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\FormularioTicket;
use phpseclib3\Net\SFTP;

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
            $nombresImagenesSubidas = [];
            
            if (isset($_FILES['tic_imagen']) && !empty($_FILES['tic_imagen']['name'][0])) {
                $totalArchivos = count($_FILES['tic_imagen']['name']);
                
                // Validar máximo de imágenes (Solo se pueden cargar 5 imagenes)
                if ($totalArchivos > 5) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'No se pueden subir más de 5 imágenes'
                    ]);
                    exit;
                }

                // Configuración SFTP desde variables de entorno
                $servidorSftp = $_ENV['FILE_SERVER'] ?? 'ftp-1';
                $usuarioSftp = $_ENV['FILE_USER'] ?? 'ftpuser';
                $passwordSftp = $_ENV['FILE_PASSWORD'] ?? 'ftppassword';
                $directorioBase = '/home/ftpuser/upload/images_ticket/'; // Usando la ruta del .env

                // Conectar al servidor SFTP
                $conexionSftp = new SFTP('docker-ftp-1', 22);
                if (!$conexionSftp->login('ftpuser', 'ftppassword')) {
                    http_response_code(500);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'Error de autenticación al servidor SFTP'
                    ]);
                    exit;
                }

                // Encontrar carpeta base disponible
                $rutasPosibles = [
                    '/home/ftpuser/upload/images_ticket/',
                    '/home/ftpuser/upload/',
                    '/upload/images_ticket/',
                    '/upload/'
                ];
                
                $carpetaBase = '/home/ftpuser/upload/'; // Por defecto
                
                foreach ($rutasPosibles as $ruta) {
                    if ($conexionSftp->is_dir($ruta)) {
                        $carpetaBase = $ruta;
                        break;
                    }
                }
                
                // Crear estructura: carpeta_base/tickets/YYYY/nombre_del_ticket/
                $año = date('Y');
                $numeroTicket = $_POST['form_tick_num'];
                
                $carpetaTickets = $carpetaBase . "tickets/{$año}/{$numeroTicket}/";
                
                // Crear la estructura de carpetas si no existe
                $carpetasACrear = [
                    $carpetaBase . "tickets/",
                    $carpetaBase . "tickets/{$año}/",
                    $carpetaBase . "tickets/{$año}/{$numeroTicket}/"
                ];
                
                foreach ($carpetasACrear as $carpeta) {
                    if (!$conexionSftp->is_dir($carpeta)) {
                        if (!$conexionSftp->mkdir($carpeta, 0755)) {
                            error_log("No se pudo crear la carpeta: $carpeta");
                        }
                    }
                }
                
                // Si no se pudo crear la estructura completa, usar la carpeta base
                if (!$conexionSftp->is_dir($carpetaTickets)) {
                    $carpetaTickets = $carpetaBase;
                }

                for ($i = 0; $i < $totalArchivos; $i++) {
                    if ($_FILES['tic_imagen']['error'][$i] === UPLOAD_ERR_OK) {
                        $nombreArchivo = $_FILES['tic_imagen']['name'][$i];
                        $archivoTemporal = $_FILES['tic_imagen']['tmp_name'][$i];
                        $tamañoArchivo = $_FILES['tic_imagen']['size'][$i];

                        // Validar extensión (Para cargar las imagenes)
                        $extensionArchivo = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
                        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff'];

                        if (!in_array($extensionArchivo, $extensionesPermitidas)) {
                            // Eliminar archivos ya subidos al SFTP
                            self::eliminarArchivosSubidos($conexionSftp, $nombresImagenesSubidas);
                            $conexionSftp->disconnect();
                            
                            http_response_code(400);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "Solo se permiten archivos de imagen: JPG, JPEG, PNG, GIF, WEBP, BMP, TIFF",
                                'archivo_enviado' => $nombreArchivo,
                                'extension_detectada' => $extensionArchivo,
                                'debug_mime' => $_FILES['tic_imagen']['type'][$i] ?? 'no detectado'
                            ]);
                            exit;
                        }

                        // Validar tamaño (8MB máximo por imagen)
                        if ($tamañoArchivo > 8 * 1024 * 1024) {
                            // Eliminar archivos ya subidos al SFTP
                            self::eliminarArchivosSubidos($conexionSftp, $nombresImagenesSubidas);
                            $conexionSftp->disconnect();
                            
                            http_response_code(400);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "La imagen no puede ser mayor a 8MB (archivo: $nombreArchivo)"
                            ]);
                            exit;
                        }

                        // Validar que sea realmente una imagen
                        $infoImagen = @getimagesize($archivoTemporal);
                        if ($infoImagen === false) {
                            //Intentar obtener más info del archivo
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $tipoMime = finfo_file($finfo, $archivoTemporal);
                            finfo_close($finfo);
                            
                            // Solo rechazar si claramente NO es una imagen
                            if (!str_contains($tipoMime, 'image/')) {
                                self::eliminarArchivosSubidos($conexionSftp, $nombresImagenesSubidas);
                                $conexionSftp->disconnect();
                                
                                http_response_code(400);
                                echo json_encode([
                                    'codigo' => 0,
                                    'mensaje' => "El archivo no parece ser una imagen válida",
                                    'archivo' => $nombreArchivo,
                                    'tipo_mime_detectado' => $tipoMime,
                                    'debug_getimagesize' => 'falló'
                                ]);
                                exit;
                            }
                        }

                        // Validar tipo MIME
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $tipoMime = finfo_file($finfo, $archivoTemporal);
                        finfo_close($finfo);
                        
                        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff'];
                        if (!in_array($tipoMime, $tiposPermitidos)) {
                            // Solo advertir, no bloquear (más permisivo)
                            error_log("Advertencia: tipo MIME no reconocido: $tipoMime para archivo: $nombreArchivo");
                        }

                        // Generar nombre único para el archivo
                        $nombreUnico = $_POST['form_tick_num'] . '_img' . ($i + 1) . '_' . uniqid() . '.' . $extensionArchivo;
                        $rutaCompletaSftp = $carpetaTickets . $nombreUnico;

                        // Subir archivo al SFTP
                        if ($conexionSftp->put($rutaCompletaSftp, $archivoTemporal, SFTP::SOURCE_LOCAL_FILE)) {
                            $rutasImagenes[] = $rutaCompletaSftp;
                            $nombresImagenesSubidas[] = $rutaCompletaSftp;
                        } else {
                            // Mostrar información del error
                            $errorSftp = $conexionSftp->getLastSFTPError();
                            
                            // Eliminar archivos ya subidos al SFTP
                            self::eliminarArchivosSubidos($conexionSftp, $nombresImagenesSubidas);
                            $conexionSftp->disconnect();
                            
                            http_response_code(500);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "Error al subir la imagen al servidor SFTP (archivo: $nombreArchivo)",
                                'debug_ruta' => $rutaCompletaSftp,
                                'debug_carpeta' => $carpetaTickets,
                                'debug_error_sftp' => $errorSftp
                            ]);
                            exit;
                        }
                    } else {
                        // Eliminar archivos ya subidos al SFTP si hay error
                        if (isset($conexionSftp)) {
                            self::eliminarArchivosSubidos($conexionSftp, $nombresImagenesSubidas);
                            $conexionSftp->disconnect();
                        }
                        
                        http_response_code(400);
                        echo json_encode([
                            'codigo' => 0,
                            'mensaje' => 'Error al procesar una de las imágenes'
                        ]);
                        exit;
                    }
                }

                // Cerrar conexión SFTP
                $conexionSftp->disconnect();
            }

            // Crear el ticket en la base de datos
            $ticket = new FormularioTicket($_POST);
            if (!empty($rutasImagenes)) {
                // Guardar las rutas como JSON
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
                        'imagenes_subidas' => count($rutasImagenes),
                        'rutas_sftp' => $rutasImagenes
                    ]
                ]);
                exit;
            } else {
                // Si falla la creación del ticket, eliminar imágenes del SFTP
                if (!empty($nombresImagenesSubidas)) {
                    $conexionSftpLimpieza = new SFTP('docker-ftp-1', 22);
                    if ($conexionSftpLimpieza->login('ftpuser', 'ftppassword')) {
                        self::eliminarArchivosSubidos($conexionSftpLimpieza, $nombresImagenesSubidas);
                        $conexionSftpLimpieza->disconnect();
                    }
                }
                
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al crear el ticket en la base de datos'
                ]);
                exit;
            }
            
        } catch (Exception $e) {
            // Si falla, eliminar imágenes del SFTP
            if (isset($nombresImagenesSubidas) && !empty($nombresImagenesSubidas)) {
                try {
                    $servidorSftp = $_ENV['FILE_SERVER'] ?? 'ftp-1';
                    $usuarioSftp = $_ENV['FILE_USER'] ?? 'ftpuser';
                    $passwordSftp = $_ENV['FILE_PASSWORD'] ?? 'ftppassword';
                    
                    $conexionSftpLimpieza = new SFTP('docker-ftp-1', 22);
                    if ($conexionSftpLimpieza->login('ftpuser', 'ftppassword')) {
                        self::eliminarArchivosSubidos($conexionSftpLimpieza, $nombresImagenesSubidas);
                        $conexionSftpLimpieza->disconnect();
                    }
                } catch (Exception $excepcionLimpieza) {
                    // Log del error de limpieza si es necesario
                    error_log('Error al limpiar archivos SFTP: ' . $excepcionLimpieza->getMessage());
                }
            }

            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            exit;
        }
    }

    /**
     * Función auxiliar para eliminar archivos subidos al SFTP en caso de error
     */
    private static function eliminarArchivosSubidos($conexionSftp, $rutasArchivos)
    {
        foreach ($rutasArchivos as $rutaArchivo) {
            try {
                if ($conexionSftp->file_exists($rutaArchivo)) {
                    $conexionSftp->delete($rutaArchivo);
                }
            } catch (Exception $e) {
                // Log del error si es necesario
                error_log('Error al eliminar archivo SFTP: ' . $rutaArchivo . ' - ' . $e->getMessage());
            }
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


//Este es un commit de prueba