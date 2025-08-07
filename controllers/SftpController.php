<?php

namespace Controllers;

use phpseclib3\Net\SFTP;
use Exception;

class SftpController
{



    public static function SubirArchivo()
    {

        if (
            isset($_FILES['nombre_campo']) &&
            $_FILES['nombre_campo']['error'] === UPLOAD_ERR_OK
        ) {
            $archivoTmp = $_FILES['nombre_campo']['tmp_name'];
            $nombreOriginal = $_FILES['nombre_campo']['name'];

            // Obtener el tipo MIME del archivo o que tipo de  archivo se intenta subir al servidor
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $archivoTmp);
            finfo_close($finfo);

            // Validar tipo de archivo si fuera necesario
            if ($mimeType !== 'image/jpeg') {
                http_response_code(400);
                echo json_encode([
                    'codigo'  => 0,
                    'mensaje' => 'El archivo adjunto debe ser una imagen JPEG.'
                ]);
                return;
            }

            // Obtener extensión desde el nombre original
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);

            // Parámetros del servidor SFTP (mejor en .env)
            $sftpServidor = $_ENV['FILE_SERVER'] ?? '127.0.0.1';
            $sftpUsuario = $_ENV['FILE_USER'] ?? 'ftpuser';
            $sftpPassword = $_ENV['FILE_PASSWORD'] ?? 'ftppassword';
            $sftpDIR = rtrim($_ENV['FILE_DIR'], '/') . '/';

            // Conectar al servidor
            $sftp = new SFTP($sftpServidor);
            if (!$sftp->login($sftpUsuario, $sftpPassword)) {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error de autenticación al servidor SFTP']);
                return;
            }

            // Verificar y crear carpeta si no existe
            if (!$sftp->file_exists($sftpDIR)) {
                if (!$sftp->mkdir($sftpDIR, -1, true)) {
                    http_response_code(500);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo crear la carpeta remota']);
                    return;
                }
            }

            // Generar nombre único del archivo
            $archivo = 'nombre_' . uniqid() . '.' . $extension;
            $rutaRemota = $sftpDIR . $archivo;

            // Subir el archivo
            if (!$sftp->put($rutaRemota, $archivoTmp, SFTP::SOURCE_LOCAL_FILE)) {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al subir el archivo al servidor SFTP']);
                return;
            }

            $sftp->disconnect();

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Archivo cargado correctamente',
                'archivo' => $archivo,
                'ruta_remota' => $rutaRemota
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'No se envió ningún archivo válido.'
            ]);
        }
    }


    public static function ModificarEliminarArchivo()
    {
        // Obtener parámetros del formulario
        $accion = $_POST['accion'] ?? null;         // 'eliminar' o 'modificar'
        $archivo = $_POST['archivo'] ?? null;       // nombre actual del archivo
        $nuevoNombre = $_POST['nuevo_nombre'] ?? null; // nuevo nombre (solo si se quiere modificar)

        // Validar que venga la acción y el nombre del archivo
        if (!$accion || !$archivo) {
            http_response_code(400); // Error 400: solicitud incorrecta
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Se requieren los campos: acción y archivo.'
            ]);
            return;
        }

        // Parámetros de conexión (se recomienda tenerlos en .env por seguridad)
        $sftpServidor  = $_ENV['FILE_SERVER'] ?? '127.0.0.1';
        $sftpUsuario   = $_ENV['FILE_USER'] ?? 'ftpuser';
        $sftpPassword  = $_ENV['FILE_PASSWORD'] ?? 'ftppassword';
        $sftpDIR       = rtrim($_ENV['FILE_DIR'] ?? '', '/') . '/';

        // Validar que la ruta del directorio esté configurada
        if (empty($sftpDIR)) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ruta del directorio no configurada.']);
            return;
        }

        // Conectarse al servidor SFTP
        $sftp = new SFTP($sftpServidor);
        if (!$sftp->login($sftpUsuario, $sftpPassword)) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo autenticar con el servidor SFTP.']);
            return;
        }

        // Verificar que el directorio exista en el servidor
        if (!$sftp->is_dir($sftpDIR)) {
            http_response_code(404);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Directorio remoto no encontrado.']);
            return;
        }

        // Construir ruta completa del archivo
        $rutaArchivo = $sftpDIR . $archivo;

        // Verificar que el archivo exista
        if (!$sftp->file_exists($rutaArchivo)) {
            http_response_code(404);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El archivo no existe en el servidor.']);
            return;
        }

        // Procesar la acción solicitada
        if ($accion === 'eliminar') {
            // Intentar eliminar el archivo
            if (!$sftp->delete($rutaArchivo)) {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo eliminar el archivo.']);
                return;
            }

            echo json_encode(['codigo' => 1, 'mensaje' => 'Archivo eliminado correctamente.']);
        } elseif ($accion === 'modificar') {
            // Validar que venga el nuevo nombre
            if (!$nuevoNombre) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Falta el nuevo nombre para renombrar.']);
                return;
            }

            $nuevaRuta = $sftpDIR . $nuevoNombre;

            // Verificar si ya existe un archivo con ese nuevo nombre
            if ($sftp->file_exists($nuevaRuta)) {
                http_response_code(409); // Error 409: conflicto
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un archivo con el nuevo nombre.']);
                return;
            }

            // Intentar renombrar
            if (!$sftp->rename($rutaArchivo, $nuevaRuta)) {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo renombrar el archivo.']);
                return;
            }

            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Archivo renombrado correctamente.',
                'nuevo_nombre' => $nuevoNombre
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Acción no válida. Usa "eliminar" o "modificar".']);
        }

        // Cerrar conexión
        $sftp->disconnect();
    }



    public static function ObtenerArchivosJson()
    {
        // Conectarse usando variables de entorno
        $sftpServidor  = $_ENV['FILE_SERVER'] ?? '127.0.0.1';
        $sftpUsuario   = $_ENV['FILE_USER'] ?? 'ftpuser';
        $sftpPassword  = $_ENV['FILE_PASSWORD'] ?? 'ftppassword';
        $sftpDIR       = rtrim($_ENV['FILE_DIR'] ?? '', '/') . '/';

        // Verificar que la ruta esté configurada
        if (empty($sftpDIR)) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ruta de directorio no configurada.']);
            return;
        }

        // Conexión SFTP
        $sftp = new SFTP($sftpServidor);
        if (!$sftp->login($sftpUsuario, $sftpPassword)) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error de autenticación al servidor SFTP.']);
            return;
        }

        // Validar que la carpeta exista
        if (!$sftp->is_dir($sftpDIR)) {
            http_response_code(404);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Directorio no encontrado en el servidor.']);
            return;
        }

        // Recolectar parámetros: archivo individual o lista
        $archivo = $_GET['archivo'] ?? null;
        $archivos = $_GET['archivos'] ?? null;

        // Resultado final
        $resultados = [];

        // Opción 1: varios archivos
        if ($archivos && is_array($archivos)) {
            foreach ($archivos as $nombre) {
                $ruta = $sftpDIR . basename($nombre); // sanitizar
                if ($sftp->file_exists($ruta)) {
                    $contenido = $sftp->get($ruta);
                    $resultados[] = [
                        'nombre' => $nombre,
                        'contenido_base64' => base64_encode($contenido)
                    ];
                } else {
                    $resultados[] = [
                        'nombre' => $nombre,
                        'error' => 'Archivo no encontrado'
                    ];
                }
            }

            // Opción 2: un solo archivo
        } elseif ($archivo) {
            $ruta = $sftpDIR . basename($archivo);
            if ($sftp->file_exists($ruta)) {
                $contenido = $sftp->get($ruta);
                $resultados[] = [
                    'nombre' => $archivo,
                    'contenido_base64' => base64_encode($contenido)
                ];
            } else {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El archivo especificado no existe.']);
                return;
            }

            // Opción 3: todos los archivos
        } else {
            $todos = $sftp->nlist($sftpDIR);
            if (!is_array($todos)) {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudieron listar los archivos.']);
                return;
            }

            foreach ($todos as $item) {
                if ($item === '.' || $item === '..') continue;
                $ruta = $sftpDIR . $item;
                if ($sftp->is_file($ruta)) {
                    $contenido = $sftp->get($ruta);
                    $resultados[] = [
                        'nombre' => $item,
                        'contenido_base64' => base64_encode($contenido)
                    ];
                }
            }
        }

        // Desconectar
        $sftp->disconnect();

        // Devolver resultado
        echo json_encode([
            'codigo' => 1,
            'mensaje' => 'Archivos obtenidos correctamente.',
            'cantidad' => count($resultados),
            'archivos' => $resultados
        ]);
    }


    //ESTOS CONTROLADORES SON ESPECIFICOS PARA SUBIR, MODIFICAR, ELIMINAR O OBTENER UN ARCHIVO CABE RESALTAR QUE LOS ARCHIVOS VAN CODIFICADOS EN BASE64
    //ASI MISMO LA LOGICA QUE USTEDE UTILICEN ES TOTALEMTEN DIFERENTE YA QUE DEBEN DE TRABAJAR CON TRANSACCIONES PARA EVITAR QUE UNA O VARIAS OPERACIONES 
    //SE REALICEN DE LO CONTRARIO ABORTAR LOS INSERTS, UPDATES  EN LA BD.
    //ASI MISMO  EJEMPLO SI YA SUBI MI ARCHIVO AL SERVIDOR NECESITO ALMACENAR  LA RUTA O INDEPENDIENTEMENTE SI ES DINAMICA O GUARDADO EN UN CAMPO 
    // PERO CON ESTOS CONTROLADORES TAMBIEN SE REQUIERE QUE  SE REALICEN INTERACCIONES CON LA BD EJEMPLO YO SUBO UN ARCHIVO Y NECESITO ALAMCENAR LA RUTA 
    // ASI MISMO VALIDAR QUE LAS OPERACIONES HAYAN SIDO EXITOSOS. 
    //ACA LES DEJO UN PEQUEÑO EJEMPLO DE COMO  PODRIAN HACERLO INTERACTUANDO CON SFTP Y BD


    public static function subirPDF()
    {
        getHeadersApi();

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(404);
            echo json_encode(['codigo' => 0, 'mensaje' => 'No se recibió el archivo correctamente']);
            return;
        }

        $identificador = $_POST['identificador'] ?? null;
        if (!$identificador) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'No se recibió el identificador']);
            return;
        }

        $unidad = $_SESSION['unidad_seleccionada'] ?? null;
        $usuario = $_SESSION['auth_user'] ?? null;
        if (!$unidad || !$usuario) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Sesión no válida']);
            return;
        }

        $resultadoDep = Firmantes::VerificarCodigoDependencia($unidad);
        $dependencia = $resultadoDep[0]['uni_dependencia'] ?? null;
        if (!$dependencia) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo obtener la dependencia']);
            return;
        }

        $fecha = new DateTime();
        $fecha->modify('-1 month');
        $anio = $fecha->format('Y');
        $mes = $fecha->format('m');

        $ftpServer = $_ENV['FILE_SERVER'];
        $ftpUsername = $_ENV['FILE_USER'];
        $ftpPassword = $_ENV['FILE_PASSWORD'];
        $remoteBaseDir = rtrim($_ENV['FILE_DIR'], '/') . '/';

        $remoteFolder = "{$remoteBaseDir}{$dependencia}/{$unidad}/{$anio}/{$mes}/";
        $remoteFile = $remoteFolder . "{$identificador}.pdf";

        $tempFilePath = $_FILES['archivo']['tmp_name'];
        $sftp = new SFTP($ftpServer);
        if (!$sftp->login($ftpUsername, $ftpPassword)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error de autenticación al servidor SFTP']);
            return;
        }

        if ($sftp->file_exists($remoteFile)) {
            $sftp->delete($remoteFile);
        }

        if (!$sftp->file_exists($remoteFolder)) {
            if (!$sftp->mkdir($remoteFolder, -1, true)) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo crear la carpeta remota']);
                return;
            }
        }

        try {
            if ($sftp->put($remoteFile, $tempFilePath, SFTP::SOURCE_LOCAL_FILE)) {
                $conexion = Firmantes::getDB();
                $conexion->beginTransaction();

                $idLibro = intval($identificador);

                $sqlObtenerFirmantes = "SELECT FI_ID, FI_ORDEN_FIRMA FROM LIBROS_FIRMANTES 
                WHERE FI_LIBRO_ID = $idLibro AND FI_ANIO = $anio 
                AND FI_MES = $mes AND FI_UNIDAD_FIRMANTE = $unidad AND FI_SITUACION = 1";


                $ObtenerFirmantes = self::fetchArray($sqlObtenerFirmantes);


                if (!$ObtenerFirmantes) {
                    $conexion->rollBack();
                    echo json_encode(['codigo' => 0, 'mensaje' => 'No está autorizado para firmar este libro']);
                    return;
                }

                $firmante = $ObtenerFirmantes[0];

                $sqlVersion = "SELECT MAX(VE_VERSION) as version_actual FROM LIBROS_FIRMAS_VERSIONES 
                                WHERE VE_UNIDAD = $unidad AND VE_LIBRO_ID = $idLibro 
                                AND VE_ANIO = $anio AND VE_MES = $mes";

                $versionActual = self::fetchArray($sqlVersion)[0]['version_actual'] ?? 1;


                $fecha_actual = date('Y-m-d H:i');

                $firma = new FirmasRealizadas([
                    'fr_firmante_id' => $firmante['fi_id'],
                    'fr_fecha_firma' => $fecha_actual,
                    'fr_usuario' => $usuario,
                    'fr_tipo_accion' => 1, // Firma/Aprobación
                    'fr_estado' => 1,      // Activo
                    'fr_version' => $versionActual
                ]);


                $firma->crear();

                // Si es la primera firma, crear la versión inicial
                if ($versionActual == 1) {
                    $version = new FirmasVersiones([
                        've_unidad' => $unidad,
                        've_libro_id' => $idLibro,
                        've_anio' => $anio,
                        've_mes' => $mes,
                        've_version' => 1,
                        've_fecha' => $fecha_actual,
                        've_usuario' => $usuario,
                        've_motivo' => 'Firma inicial del libro'
                    ]);
                    $version->crear();
                }

                $sqlFirmantesTotales = "SELECT COUNT(*) AS total FROM LIBROS_FIRMANTES 
                                    WHERE FI_LIBRO_ID = $idLibro AND FI_ANIO = $anio AND FI_MES = $mes AND FI_SITUACION = 1 AND FI_UNIDAD = $unidad";

                $sqlFirmasRealizadas = "SELECT COUNT(*) AS firmados FROM LIBROS_FIRMAS_REALIZADAS FR
                                    INNER JOIN LIBROS_FIRMANTES LF ON FR.FR_FIRMANTE_ID = LF.FI_ID
                                    WHERE LF.FI_LIBRO_ID = $idLibro AND LF.FI_ANIO = $anio AND LF.FI_MES = $mes 
                                    AND FR.FR_ESTADO = 1 AND FR.FR_TIPO_ACCION = 1 AND LF.FI_UNIDAD = $unidad";

                $totalFirmantes = self::fetchArray($sqlFirmantesTotales)[0]['total'] ?? 0;
                $totalFirmados = self::fetchArray($sqlFirmasRealizadas)[0]['firmados'] ?? 0;

                if ($totalFirmantes > 0 && $totalFirmantes == $totalFirmados) {

                    if ($idLibro === 18) {
                        FirmasCompletadas::eliminarComprobantesMes($unidad, $mes, $anio);
                    }

                    // 2) Guardas tu registro de firmas completadas
                    $registroFinal = new FirmasCompletadas([
                        'fc_libro_id'       => $idLibro,
                        'fc_unidad'         => $unidad,
                        'fc_anio'           => $anio,
                        'fc_mes'            => $mes,
                        'fc_fecha_completado' => $fecha_actual,
                        'fc_usuario'        => $usuario
                    ]);
                    $registroFinal->crear();
                }
                $conexion->commit();

                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Archivo firmado y registrado exitosamente',
                    'ruta' => $remoteFile
                ]);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al subir el archivo al servidor']);
            }
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error durante el proceso de firma', 'error' => $e->getMessage()]);
        } finally {
            $sftp->disconnect();
        }
    }
}
