<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\FormularioTicket;
use phpseclib3\Net\SFTP;

class TicketController extends ActiveRecord
{

    public static function renderizarPagina(Router $enrutador)
    {
        $enrutador->render('ticket/index', []);
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
            $consulta_usuario = "SELECT per_telefono, per_nom1, per_nom2, per_ape1, per_desc_empleo 
                                FROM mper 
                                WHERE per_catalogo = {$_POST['form_tic_usu']}";
            $datos_usuario = self::fetchFirst($consulta_usuario);

            if (!$datos_usuario) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontraron datos del usuario en el sistema'
                ]);
                exit;
            }

            // Asignar teléfono automáticamente
            $_POST['tic_telefono'] = $datos_usuario['per_telefono'];
            
            if (empty($_POST['tic_telefono'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario no tiene teléfono registrado en el sistema'
                ]);
                exit;
            }

            // Validar aplicación - CAMBIO: ahora validamos que exista en grupo_menuautocom
            $_POST['tic_app'] = filter_var($_POST['tic_app'], FILTER_SANITIZE_NUMBER_INT);
            
            if (empty($_POST['tic_app']) || $_POST['tic_app'] < 1) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar una aplicación'
                ]);
                exit;
            }

            // Validar que la aplicación seleccionada existe y está activa
            $consulta_aplicacion = "SELECT gma_codigo, gma_desc 
                                   FROM grupo_menuautocom 
                                   WHERE gma_codigo = {$_POST['tic_app']} 
                                   AND gma_situacion = '1'";
            $aplicacion_valida = self::fetchFirst($consulta_aplicacion);

            if (!$aplicacion_valida) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La aplicación seleccionada no es válida o no está disponible'
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
            $rutas_imagenes = [];
            $nombres_imagenes_subidas = [];
            
            if (isset($_FILES['tic_imagen']) && !empty($_FILES['tic_imagen']['name'][0])) {
                $total_archivos = count($_FILES['tic_imagen']['name']);
                
                // Validar máximo de imágenes (Solo se pueden cargar 5 imagenes)
                if ($total_archivos > 5) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'No se pueden subir más de 5 imágenes'
                    ]);
                    exit;
                }

                // Configuración SFTP desde variables de entorno
                $servidor_sftp = $_ENV['FILE_SERVER'] ?? 'sftp_server';
                $usuario_sftp = $_ENV['FILE_USER'] ?? 'ftpuser';
                $password_sftp = $_ENV['FILE_PASSWORD'] ?? 'ftppassword';
                $directorio_base = '/home/ftpuser/upload/images_ticket/'; // Usando la ruta del .env

                // Conectar al servidor SFTP
                $conexion_sftp = new SFTP('sftp_server', 22);
                if (!$conexion_sftp->login('ftpuser', 'ftppassword')) {
                    http_response_code(500);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'Error de autenticación al servidor SFTP'
                    ]);
                    exit;
                }

                // Encontrar carpeta base disponible
                $rutas_posibles = [
                    '/home/ftpuser/upload/images_ticket/',
                    '/home/ftpuser/upload/',
                    '/upload/images_ticket/',
                    '/upload/'
                ];
                
                $carpeta_base = '/home/ftpuser/upload/'; // Por defecto
                
                foreach ($rutas_posibles as $ruta) {
                    if ($conexion_sftp->is_dir($ruta)) {
                        $carpeta_base = $ruta;
                        break;
                    }
                }
                
                // Crear estructura: carpeta_base/tickets/YYYY/nombre_del_ticket/
                $año = date('Y');
                $numero_ticket = $_POST['form_tick_num'];
                
                $carpeta_tickets = $carpeta_base . "tickets/{$año}/{$numero_ticket}/";
                
                // Crear la estructura de carpetas si no existe
                $carpetas_a_crear = [
                    $carpeta_base . "tickets/",
                    $carpeta_base . "tickets/{$año}/",
                    $carpeta_base . "tickets/{$año}/{$numero_ticket}/"
                ];
                
                foreach ($carpetas_a_crear as $carpeta) {
                    if (!$conexion_sftp->is_dir($carpeta)) {
                        if (!$conexion_sftp->mkdir($carpeta, 0755)) {
                            error_log("No se pudo crear la carpeta: $carpeta");
                        }
                    }
                }
                
                // Si no se pudo crear la estructura completa, usar la carpeta base
                if (!$conexion_sftp->is_dir($carpeta_tickets)) {
                    $carpeta_tickets = $carpeta_base;
                }

                for ($i = 0; $i < $total_archivos; $i++) {
                    if ($_FILES['tic_imagen']['error'][$i] === UPLOAD_ERR_OK) {
                        $nombre_archivo = $_FILES['tic_imagen']['name'][$i];
                        $archivo_temporal = $_FILES['tic_imagen']['tmp_name'][$i];
                        $tamaño_archivo = $_FILES['tic_imagen']['size'][$i];

                        // Validar extensión (Para cargar las imagenes)
                        $extension_archivo = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
                        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff'];

                        if (!in_array($extension_archivo, $extensiones_permitidas)) {
                            // Eliminar archivos ya subidos al SFTP
                            self::eliminarArchivosSubidos($conexion_sftp, $nombres_imagenes_subidas);
                            $conexion_sftp->disconnect();
                            
                            http_response_code(400);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "Solo se permiten archivos de imagen: JPG, JPEG, PNG, GIF, WEBP, BMP, TIFF",
                                'archivo_enviado' => $nombre_archivo,
                                'extension_detectada' => $extension_archivo,
                                'debug_mime' => $_FILES['tic_imagen']['type'][$i] ?? 'no detectado'
                            ]);
                            exit;
                        }

                        // Validar tamaño (8MB máximo por imagen)
                        if ($tamaño_archivo > 8 * 1024 * 1024) {
                            // Eliminar archivos ya subidos al SFTP
                            self::eliminarArchivosSubidos($conexion_sftp, $nombres_imagenes_subidas);
                            $conexion_sftp->disconnect();
                            
                            http_response_code(400);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "La imagen no puede ser mayor a 8MB (archivo: $nombre_archivo)"
                            ]);
                            exit;
                        }

                        // Validar que sea realmente una imagen
                        $info_imagen = @getimagesize($archivo_temporal);
                        if ($info_imagen === false) {
                            //Intentar obtener más info del archivo
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $tipo_mime = finfo_file($finfo, $archivo_temporal);
                            finfo_close($finfo);
                            
                            // Solo rechazar si claramente NO es una imagen
                            if (!str_contains($tipo_mime, 'image/')) {
                                self::eliminarArchivosSubidos($conexion_sftp, $nombres_imagenes_subidas);
                                $conexion_sftp->disconnect();
                                
                                http_response_code(400);
                                echo json_encode([
                                    'codigo' => 0,
                                    'mensaje' => "El archivo no parece ser una imagen válida",
                                    'archivo' => $nombre_archivo,
                                    'tipo_mime_detectado' => $tipo_mime,
                                    'debug_getimagesize' => 'falló'
                                ]);
                                exit;
                            }
                        }

                        // Validar tipo MIME
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $tipo_mime = finfo_file($finfo, $archivo_temporal);
                        finfo_close($finfo);
                        
                        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff'];
                        if (!in_array($tipo_mime, $tipos_permitidos)) {
                            // Solo advertir, no bloquear (más permisivo)
                            error_log("Advertencia: tipo MIME no reconocido: $tipo_mime para archivo: $nombre_archivo");
                        }

                        // Generar nombre único para el archivo
                        $nombre_unico = $_POST['form_tick_num'] . '_img' . ($i + 1) . '_' . uniqid() . '.' . $extension_archivo;
                        $ruta_completa_sftp = $carpeta_tickets . $nombre_unico;

                        // Subir archivo al SFTP
                        if ($conexion_sftp->put($ruta_completa_sftp, $archivo_temporal, SFTP::SOURCE_LOCAL_FILE)) {
                            $rutas_imagenes[] = $ruta_completa_sftp;
                            $nombres_imagenes_subidas[] = $ruta_completa_sftp;
                        } else {
                            // Mostrar información del error
                            $error_sftp = $conexion_sftp->getLastSFTPError();
                            
                            // Eliminar archivos ya subidos al SFTP
                            self::eliminarArchivosSubidos($conexion_sftp, $nombres_imagenes_subidas);
                            $conexion_sftp->disconnect();
                            
                            http_response_code(500);
                            echo json_encode([
                                'codigo' => 0,
                                'mensaje' => "Error al subir la imagen al servidor SFTP (archivo: $nombre_archivo)",
                                'debug_ruta' => $ruta_completa_sftp,
                                'debug_carpeta' => $carpeta_tickets,
                                'debug_error_sftp' => $error_sftp
                            ]);
                            exit;
                        }
                    } else {
                        // Eliminar archivos ya subidos al SFTP si hay error
                        if (isset($conexion_sftp)) {
                            self::eliminarArchivosSubidos($conexion_sftp, $nombres_imagenes_subidas);
                            $conexion_sftp->disconnect();
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
                $conexion_sftp->disconnect();
            }

            // Crear el ticket en la base de datos
            $ticket = new FormularioTicket($_POST);
            if (!empty($rutas_imagenes)) {
                // Guardar las rutas como JSON
                $ticket->tic_imagen = json_encode($rutas_imagenes);
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
                        'nombre_usuario' => trim($datos_usuario['per_nom1'] . ' ' . $datos_usuario['per_nom2'] . ' ' . $datos_usuario['per_ape1']),
                        'telefono_usuario' => $datos_usuario['per_telefono'],
                        'correo_usuario' => $_POST['tic_correo_electronico'],
                        'aplicacion_seleccionada' => $aplicacion_valida['gma_desc'], // Nueva información
                        'imagenes_subidas' => count($rutas_imagenes),
                        'rutas_sftp' => $rutas_imagenes
                    ]
                ]);
                exit;
            } else {
                // Si falla la creación del ticket, eliminar imágenes del SFTP
                if (!empty($nombres_imagenes_subidas)) {
                    $conexion_sftp_limpieza = new SFTP('sftp_server', 22);
                    if ($conexion_sftp_limpieza->login('ftpuser', 'ftppassword')) {
                        self::eliminarArchivosSubidos($conexion_sftp_limpieza, $nombres_imagenes_subidas);
                        $conexion_sftp_limpieza->disconnect();
                    }
                }
                
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al crear el ticket en la base de datos'
                ]);
                exit;
            }
            
        } catch (Exception $excepcion) {
            // Si falla, eliminar imágenes del SFTP
            if (isset($nombres_imagenes_subidas) && !empty($nombres_imagenes_subidas)) {
                try {
                    $servidor_sftp = $_ENV['FILE_SERVER'] ?? 'sftp_server';
                    $usuario_sftp = $_ENV['FILE_USER'] ?? 'ftpuser';
                    $password_sftp = $_ENV['FILE_PASSWORD'] ?? 'ftppassword';
                    
                    $conexion_sftp_limpieza = new SFTP('sftp_server', 22);
                    if ($conexion_sftp_limpieza->login('ftpuser', 'ftppassword')) {
                        self::eliminarArchivosSubidos($conexion_sftp_limpieza, $nombres_imagenes_subidas);
                        $conexion_sftp_limpieza->disconnect();
                    }
                } catch (Exception $excepcion_limpieza) {
                    // Log del error de limpieza si es necesario
                    error_log('Error al limpiar archivos SFTP: ' . $excepcion_limpieza->getMessage());
                }
            }

            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $excepcion->getMessage(),
                'archivo' => $excepcion->getFile(),
                'linea' => $excepcion->getLine(),
                'trace' => $excepcion->getTraceAsString()
            ]);
            exit;
        }
    }

    /**
     * Función auxiliar para eliminar archivos subidos al SFTP en caso de error
     */
    private static function eliminarArchivosSubidos($conexion_sftp, $rutas_archivos)
    {
        foreach ($rutas_archivos as $ruta_archivo) {
            try {
                if ($conexion_sftp->file_exists($ruta_archivo)) {
                    $conexion_sftp->delete($ruta_archivo);
                }
            } catch (Exception $excepcion) {
                // Log del error si es necesario
                error_log('Error al eliminar archivo SFTP: ' . $ruta_archivo . ' - ' . $excepcion->getMessage());
            }
        }
    }

    public static function obtenerAplicacionesAPI() 
    {
        getHeadersApi();
        
        try {
            // CAMBIO PRINCIPAL: Consulta actualizada para usar grupo_menuautocom
            $consulta_aplicaciones = "SELECT gma_codigo, gma_desc 
                                     FROM grupo_menuautocom 
                                     WHERE gma_situacion = '1' 
                                     ORDER BY gma_desc";
            $datos_aplicaciones = self::fetchArray($consulta_aplicaciones);
            
            // Verificar si se encontraron aplicaciones
            if (empty($datos_aplicaciones)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'No se encontraron aplicaciones disponibles',
                    'data' => []
                ]);
                exit;
            }
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Aplicaciones obtenidas correctamente',
                'total_aplicaciones' => count($datos_aplicaciones),
                'data' => $datos_aplicaciones
            ]);

        } catch (Exception $excepcion) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las aplicaciones',
                'detalle' => $excepcion->getMessage(),
            ]);
        }
    }
}

//Este es un commit de prueba