<?php

require_once __DIR__ . '/../models/FormularioTicket.php';
require_once __DIR__ . '/../models/TicketAsignado.php';
require_once __DIR__ . '/../models/HistorialTicket.php';
require_once __DIR__ . '/../models/EstadoTicket.php';


use Model\FormularioTicket;
use Model\TicketAsignado;
use Model\HistorialTicket;
use Model\EstadoTicket;
use Model\Personal;
use Model\Dependencia;

class TicketController {
    
    // ===========================================
    // VISTA PRINCIPAL DE TICKETS
    // ===========================================
    public static function index() {
        $titulo = 'Sistema de Tickets de Soporte';
        $router = new Router();
        $router->render('tickets/index', [
            'titulo' => $titulo
        ]);
    }

    // ===========================================
    // CREAR NUEVO TICKET - API
    // ===========================================
    public static function guardarAPI() {
        getHeadersApi();
        
        $erroresValidacion = [];
        
        // ========================================
        // VALIDACIÓN: Usuario Solicitante
        // ========================================
        if (empty($_POST['form_tic_usu'])) {
            $erroresValidacion[] = 'El usuario solicitante es obligatorio';
        } elseif (!is_numeric($_POST['form_tic_usu'])) {
            $erroresValidacion[] = 'El ID del usuario debe ser numérico';
        } else {
            // Verificar que el usuario existe en mper usando foreach
            $consultaUsuarios = Personal::consultarSQL("SELECT per_catalogo FROM mper WHERE per_catalogo = {$_POST['form_tic_usu']}");
            $usuarioEncontrado = false;
            
            foreach ($consultaUsuarios as $usuario) {
                if ($usuario->per_catalogo == $_POST['form_tic_usu']) {
                    $usuarioEncontrado = true;
                    break;
                }
            }
            
            if (!$usuarioEncontrado) {
                $erroresValidacion[] = 'El usuario seleccionado no existe en el sistema';
            }
        }

        // ========================================
        // VALIDACIÓN: Dependencia
        // ========================================
        if (empty($_POST['tic_dependencia'])) {
            $erroresValidacion[] = 'La dependencia es obligatoria';
        } elseif (!is_numeric($_POST['tic_dependencia'])) {
            $erroresValidacion[] = 'El ID de la dependencia debe ser numérico';
        } else {
            // Verificar que la dependencia existe en mdep usando foreach
            $consultaDependencias = Dependencia::consultarSQL("SELECT dep_llave FROM mdep WHERE dep_llave = {$_POST['tic_dependencia']} AND dep_situacion = 1");
            $dependenciaEncontrada = false;
            
            foreach ($consultaDependencias as $dependencia) {
                if ($dependencia->dep_llave == $_POST['tic_dependencia']) {
                    $dependenciaEncontrada = true;
                    break;
                }
            }
            
            if (!$dependenciaEncontrada) {
                $erroresValidacion[] = 'La dependencia seleccionada no existe o está inactiva';
            }
        }

        // ========================================
        // VALIDACIÓN: Descripción del Problema
        // ========================================
        if (empty($_POST['tic_comentario_falla'])) {
            $erroresValidacion[] = 'La descripción del problema es obligatoria';
        } else {
            $_POST['tic_comentario_falla'] = trim(htmlspecialchars($_POST['tic_comentario_falla']));
            
            if (strlen($_POST['tic_comentario_falla']) < 15) {
                $erroresValidacion[] = 'La descripción debe tener al menos 15 caracteres';
            }
            
            if (strlen($_POST['tic_comentario_falla']) > 2000) {
                $erroresValidacion[] = 'La descripción no puede exceder 2000 caracteres';
            }
            
            // Validar que no tenga solo espacios o caracteres especiales
            if (preg_match('/^[\s\W]*$/', $_POST['tic_comentario_falla'])) {
                $erroresValidacion[] = 'La descripción debe contener texto válido';
            }
        }

        // ========================================
        // VALIDACIÓN: Correo Electrónico
        // ========================================
        if (empty($_POST['tic_correo_electronico'])) {
            $erroresValidacion[] = 'El correo electrónico es obligatorio';
        } else {
            $_POST['tic_correo_electronico'] = trim(strtolower(htmlspecialchars($_POST['tic_correo_electronico'])));
            
            if (!filter_var($_POST['tic_correo_electronico'], FILTER_VALIDATE_EMAIL)) {
                $erroresValidacion[] = 'El formato del correo electrónico no es válido';
            }
            
            if (strlen($_POST['tic_correo_electronico']) > 250) {
                $erroresValidacion[] = 'El correo electrónico es demasiado largo (máximo 250 caracteres)';
            }
        }

        // ========================================
        // VALIDACIÓN: Imagen (Opcional)
        // ========================================
        $nombreImagenGuardada = '';
        if (isset($_FILES['tic_imagen']) && $_FILES['tic_imagen']['error'] === UPLOAD_ERR_OK) {
            $archivoImagen = $_FILES['tic_imagen'];
            
            // Validar tipo de archivo permitido
            $tiposImagenPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($archivoImagen['type'], $tiposImagenPermitidos)) {
                $erroresValidacion[] = 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP)';
            }
            
            // Validar tamaño del archivo (máximo 8MB)
            if ($archivoImagen['size'] > 8 * 1024 * 1024) {
                $erroresValidacion[] = 'La imagen no puede ser mayor a 8MB';
            }
            
            // Validar dimensiones básicas
            $infoImagen = getimagesize($archivoImagen['tmp_name']);
            if ($infoImagen === false) {
                $erroresValidacion[] = 'El archivo no es una imagen válida';
            } elseif ($infoImagen[0] > 4000 || $infoImagen[1] > 4000) {
                $erroresValidacion[] = 'Las dimensiones de la imagen son demasiado grandes (máximo 4000x4000)';
            }
            
            if (empty($erroresValidacion)) {
                // Generar nombre único para evitar conflictos
                $extension = strtolower(pathinfo($archivoImagen['name'], PATHINFO_EXTENSION));
                $nombreImagenGuardada = 'ticket_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
                $rutaCompleta = __DIR__ . '/../public/uploads/tickets/' . $nombreImagenGuardada;
                
                // Crear directorio si no existe
                if (!is_dir(dirname($rutaCompleta))) {
                    mkdir(dirname($rutaCompleta), 0755, true);
                }
                
                // Mover archivo subido
                if (!move_uploaded_file($archivoImagen['tmp_name'], $rutaCompleta)) {
                    $erroresValidacion[] = 'Error al guardar la imagen en el servidor';
                    $nombreImagenGuardada = '';
                }
            }
        }

        // ========================================
        // SI HAY ERRORES, RETORNAR
        // ========================================
        if (!empty($erroresValidacion)) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Errores de validación: ' . implode(', ', $erroresValidacion),
                'detalle' => count($erroresValidacion),
                'data' => []
            ]);
            exit;
        }

        // ========================================
        // PROCESAR CREACIÓN DEL TICKET
        // ========================================
        try {
            // Generar número de ticket único: TK202501001
            $anioMes = date('Ym');
            
            // Buscar último ticket del mes (sintaxis Informix)
            $consultaUltimoTicket = FormularioTicket::consultarSQL(
                "SELECT form_tick_num FROM formulario_ticket 
                 WHERE form_tick_num LIKE 'TK{$anioMes}%' 
                 ORDER BY form_tick_num DESC"
            );
            
            if (empty($consultaUltimoTicket)) {
                $correlativoNuevo = '001';
            } else {
                $ultimoNumeroTicket = $consultaUltimoTicket[0]->form_tick_num;
                $ultimoCorrelativo = (int)substr($ultimoNumeroTicket, -3);
                $correlativoNuevo = str_pad($ultimoCorrelativo + 1, 3, '0', STR_PAD_LEFT);
            }
            
            $numeroTicketGenerado = "TK{$anioMes}{$correlativoNuevo}";
            
            // Preparar datos para inserción (sintaxis Informix)
            $comentarioEscapado = str_replace("'", "''", $_POST['tic_comentario_falla']);
            $correoEscapado = str_replace("'", "''", $_POST['tic_correo_electronico']);
            $imagenEscapada = str_replace("'", "''", $nombreImagenGuardada);
            
            // Insertar ticket principal
            $sqlInsercionTicket = "INSERT INTO formulario_ticket (
                                      form_tick_num, 
                                      form_tic_usu, 
                                      tic_dependencia, 
                                      tic_comentario_falla, 
                                      tic_correo_electronico, 
                                      tic_imagen, 
                                      form_fecha_creacion
                                  ) VALUES (
                                      '{$numeroTicketGenerado}',
                                      {$_POST['form_tic_usu']},
                                      {$_POST['tic_dependencia']},
                                      '{$comentarioEscapado}',
                                      '{$correoEscapado}',
                                      '{$imagenEscapada}',
                                      CURRENT
                                  )";
            
            $resultadoInsercion = FormularioTicket::getDB()->exec($sqlInsercionTicket);
            
            if ($resultadoInsercion > 0) {
                // Crear registro inicial en historial
                $sqlHistorialInicial = "INSERT INTO historial_incidentes_tickets (
                                           hist_tic_encargado,
                                           hist_tic_solicitante,
                                           hist_ticket,
                                           hist_dependencia,
                                           hist_tic_fecha_inicio
                                       ) VALUES (
                                           {$_POST['form_tic_usu']},
                                           {$_POST['form_tic_usu']},
                                           '{$numeroTicketGenerado}',
                                           {$_POST['tic_dependencia']},
                                           CURRENT
                                       )";
                
                FormularioTicket::getDB()->exec($sqlHistorialInicial);
                
                http_response_code(201);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Ticket creado exitosamente',
                    'detalle' => 'Su solicitud ha sido registrada correctamente',
                    'data' => [
                        'numero_ticket' => $numeroTicketGenerado,
                        'fecha_creacion' => date('Y-m-d H:i:s'),
                        'estado_inicial' => 'Creado'
                    ]
                ]);
            } else {
                throw new Exception('No se pudo insertar el ticket en la base de datos');
            }
            
        } catch (Exception $excepcionCapturada) {
            // Si hay error, eliminar imagen subida
            if ($nombreImagenGuardada && file_exists(__DIR__ . '/../public/uploads/tickets/' . $nombreImagenGuardada)) {
                unlink(__DIR__ . '/../public/uploads/tickets/' . $nombreImagenGuardada);
            }
            
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // BUSCAR TODOS LOS TICKETS - API
    // ===========================================
    public static function buscarAPI() {
        getHeadersApi();
        
        try {
            // Consulta con JOINs para obtener información completa (sintaxis Informix)
            $sqlTicketsCompletos = "SELECT 
                                       ft.form_tick_num,
                                       ft.tic_comentario_falla,
                                       ft.tic_correo_electronico,
                                       ft.tic_imagen,
                                       ft.form_fecha_creacion,
                                       (p.per_nom1 || ' ' || p.per_nom2 || ' ' || p.per_ape1 || ' ' || p.per_ape2) as nombre_completo_solicitante,
                                       d.dep_desc_lg as nombre_dependencia,
                                       d.dep_desc_md as nombre_dependencia_corto,
                                       ta.estado_ticket,
                                       et.est_tic_desc as descripcion_estado,
                                       (enc.per_nom1 || ' ' || enc.per_ape1) as nombre_encargado
                                   FROM formulario_ticket ft
                                   INNER JOIN mper p ON ft.form_tic_usu = p.per_catalogo
                                   INNER JOIN mdep d ON ft.tic_dependencia = d.dep_llave
                                   LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                                   LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                   LEFT JOIN mper enc ON ta.tic_encargado = enc.per_catalogo
                                   ORDER BY ft.form_fecha_creacion DESC";
            
            $ticketsEncontrados = FormularioTicket::consultarSQL($sqlTicketsCompletos);
            
            if (!empty($ticketsEncontrados)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Tickets encontrados correctamente',
                    'detalle' => count($ticketsEncontrados),
                    'data' => $ticketsEncontrados
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontraron tickets registrados',
                    'detalle' => 0,
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al buscar tickets',
                'detalle' => $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // OBTENER PERSONAL PARA SELECTS - API
    // ===========================================
    public static function obtenerPersonalAPI() {
        getHeadersApi();
        
        try {
            // Consultar personal activo usando foreach
            $consultaPersonal = Personal::consultarSQL("SELECT per_catalogo, per_nom1, per_nom2, per_ape1, per_ape2, per_desc_empleo FROM mper WHERE per_situacion = '01' ORDER BY per_ape1, per_nom1");
            
            $listaPersonal = [];
            foreach ($consultaPersonal as $persona) {
                $nombreCompleto = trim($persona->per_nom1 . ' ' . $persona->per_nom2 . ' ' . $persona->per_ape1 . ' ' . $persona->per_ape2);
                $listaPersonal[] = [
                    'per_catalogo' => $persona->per_catalogo,
                    'nombre_completo' => $nombreCompleto,
                    'descripcion_empleo' => $persona->per_desc_empleo
                ];
            }
            
            if (!empty($listaPersonal)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Personal encontrado correctamente',
                    'detalle' => count($listaPersonal),
                    'data' => $listaPersonal
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontró personal activo',
                    'detalle' => 0,
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener personal',
                'detalle' => $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // OBTENER DEPENDENCIAS PARA SELECTS - API
    // ===========================================
    public static function obtenerDependenciasAPI() {
        getHeadersApi();
        
        try {
            // Consultar dependencias activas usando foreach
            $consultaDependencias = Dependencia::consultarSQL("SELECT dep_llave, dep_desc_lg, dep_desc_md, dep_desc_ct FROM mdep WHERE dep_situacion = 1 ORDER BY dep_desc_lg");
            
            $listaDependencias = [];
            foreach ($consultaDependencias as $dependencia) {
                $listaDependencias[] = [
                    'dep_llave' => $dependencia->dep_llave,
                    'descripcion_larga' => $dependencia->dep_desc_lg,
                    'descripcion_media' => $dependencia->dep_desc_md,
                    'descripcion_corta' => $dependencia->dep_desc_ct
                ];
            }
            
            if (!empty($listaDependencias)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Dependencias encontradas correctamente',
                    'detalle' => count($listaDependencias),
                    'data' => $listaDependencias
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontraron dependencias activas',
                    'detalle' => 0,
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener dependencias',
                'detalle' => $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // ASIGNAR TICKET A ENCARGADO - API
    // ===========================================
    public static function asignarTicketAPI() {
        getHeadersApi();
        
        $erroresValidacion = [];
        
        // ========================================
        // VALIDACIÓN: Número de Ticket
        // ========================================
        if (empty($_POST['tic_numero_ticket'])) {
            $erroresValidacion[] = 'El número de ticket es obligatorio';
        } else {
            $_POST['tic_numero_ticket'] = trim(htmlspecialchars($_POST['tic_numero_ticket']));
            
            // Verificar que el ticket existe usando foreach
            $consultaTicket = FormularioTicket::consultarSQL("SELECT form_tick_num FROM formulario_ticket WHERE form_tick_num = '{$_POST['tic_numero_ticket']}'");
            $ticketEncontrado = false;
            
            foreach ($consultaTicket as $ticket) {
                if ($ticket->form_tick_num === $_POST['tic_numero_ticket']) {
                    $ticketEncontrado = true;
                    break;
                }
            }
            
            if (!$ticketEncontrado) {
                $erroresValidacion[] = 'El ticket especificado no existe';
            }
        }

        // ========================================
        // VALIDACIÓN: Encargado
        // ========================================
        if (empty($_POST['tic_encargado'])) {
            $erroresValidacion[] = 'El encargado es obligatorio';
        } elseif (!is_numeric($_POST['tic_encargado'])) {
            $erroresValidacion[] = 'El ID del encargado debe ser numérico';
        } else {
            // Verificar que el encargado existe en mper usando foreach
            $consultaEncargado = Personal::consultarSQL("SELECT per_catalogo FROM mper WHERE per_catalogo = {$_POST['tic_encargado']} AND per_situacion = '01'");
            $encargadoEncontrado = false;
            
            foreach ($consultaEncargado as $encargado) {
                if ($encargado->per_catalogo == $_POST['tic_encargado']) {
                    $encargadoEncontrado = true;
                    break;
                }
            }
            
            if (!$encargadoEncontrado) {
                $erroresValidacion[] = 'El encargado seleccionado no existe o está inactivo';
            }
        }

        // ========================================
        // VALIDACIÓN: Estado del Ticket
        // ========================================
        if (empty($_POST['estado_ticket'])) {
            $_POST['estado_ticket'] = 3; // Estado "Asignado" por defecto
        } elseif (!is_numeric($_POST['estado_ticket'])) {
            $erroresValidacion[] = 'El estado del ticket debe ser numérico';
        } else {
            // Verificar que el estado existe usando foreach
            $consultaEstado = EstadoTicket::consultarSQL("SELECT est_tic_id FROM estado_ticket WHERE est_tic_id = {$_POST['estado_ticket']}");
            $estadoEncontrado = false;
            
            foreach ($consultaEstado as $estado) {
                if ($estado->est_tic_id == $_POST['estado_ticket']) {
                    $estadoEncontrado = true;
                    break;
                }
            }
            
            if (!$estadoEncontrado) {
                $erroresValidacion[] = 'El estado seleccionado no es válido';
            }
        }

        // ========================================
        // SI HAY ERRORES, RETORNAR
        // ========================================
        if (!empty($erroresValidacion)) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Errores de validación: ' . implode(', ', $erroresValidacion),
                'detalle' => count($erroresValidacion),
                'data' => []
            ]);
            exit;
        }

        // ========================================
        // PROCESAR ASIGNACIÓN
        // ========================================
        try {
            // Verificar si ya existe una asignación para este ticket
            $consultaAsignacionExistente = TicketAsignado::consultarSQL("SELECT tic_id FROM tickets_asignados WHERE tic_numero_ticket = '{$_POST['tic_numero_ticket']}'");
            
            if (!empty($consultaAsignacionExistente)) {
                // Actualizar asignación existente
                $sqlActualizarAsignacion = "UPDATE tickets_asignados SET 
                                              tic_encargado = {$_POST['tic_encargado']},
                                              estado_ticket = {$_POST['estado_ticket']}
                                          WHERE tic_numero_ticket = '{$_POST['tic_numero_ticket']}'";
                
                $resultadoActualizacion = TicketAsignado::getDB()->exec($sqlActualizarAsignacion);
                $mensaje = 'Asignación de ticket actualizada correctamente';
                
            } else {
                // Crear nueva asignación
                $sqlNuevaAsignacion = "INSERT INTO tickets_asignados (
                                         tic_numero_ticket,
                                         tic_encargado,
                                         estado_ticket
                                     ) VALUES (
                                         '{$_POST['tic_numero_ticket']}',
                                         {$_POST['tic_encargado']},
                                         {$_POST['estado_ticket']}
                                     )";
                
                $resultadoActualizacion = TicketAsignado::getDB()->exec($sqlNuevaAsignacion);
                $mensaje = 'Ticket asignado correctamente';
            }
            
            if ($resultadoActualizacion >= 0) {
                // Actualizar historial
                $sqlActualizarHistorial = "UPDATE historial_incidentes_tickets SET 
                                             hist_tic_encargado = {$_POST['tic_encargado']}
                                         WHERE hist_ticket = '{$_POST['tic_numero_ticket']}' 
                                         AND hist_tic_fecha_finalizacion IS NULL";
                
                HistorialTicket::getDB()->exec($sqlActualizarHistorial);
                
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => $mensaje,
                    'detalle' => 'El ticket ha sido procesado exitosamente',
                    'data' => [
                        'numero_ticket' => $_POST['tic_numero_ticket'],
                        'encargado_id' => $_POST['tic_encargado'],
                        'estado_id' => $_POST['estado_ticket'],
                        'fecha_asignacion' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                throw new Exception('No se pudo procesar la asignación del ticket');
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno al asignar ticket',
                'detalle' => $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // OBTENER ESTADOS DE TICKETS - API
    // ===========================================
    public static function obtenerEstadosAPI() {
        getHeadersApi();
        
        try {
            $consultaEstados = EstadoTicket::consultarSQL("SELECT est_tic_id, est_tic_desc FROM estado_ticket ORDER BY est_tic_id");
            
            $listaEstados = [];
            foreach ($consultaEstados as $estado) {
                $listaEstados[] = [
                    'est_tic_id' => $estado->est_tic_id,
                    'est_tic_desc' => $estado->est_tic_desc
                ];
            }
            
            if (!empty($listaEstados)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Estados encontrados correctamente',
                    'detalle' => count($listaEstados),
                    'data' => $listaEstados
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontraron estados configurados',
                    'detalle' => 0,
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener estados',
                'detalle' => $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // CAMBIAR ESTADO DE TICKET - API
    // ===========================================
    public static function cambiarEstadoAPI() {
        getHeadersApi();
        
        $erroresValidacion = [];
        
        // ========================================
        // VALIDACIONES
        // ========================================
        if (empty($_POST['tic_numero_ticket'])) {
            $erroresValidacion[] = 'El número de ticket es obligatorio';
        }
        
        if (empty($_POST['nuevo_estado'])) {
            $erroresValidacion[] = 'El nuevo estado es obligatorio';
        } elseif (!is_numeric($_POST['nuevo_estado'])) {
            $erroresValidacion[] = 'El estado debe ser numérico';
        }
        
        if (!empty($erroresValidacion)) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => implode(', ', $erroresValidacion),
                'data' => []
            ]);
            exit;
        }

        try {
            // Actualizar estado en tickets_asignados
            $sqlCambiarEstado = "UPDATE tickets_asignados SET 
                                   estado_ticket = {$_POST['nuevo_estado']}
                               WHERE tic_numero_ticket = '{$_POST['tic_numero_ticket']}'";
            
            $resultadoCambio = TicketAsignado::getDB()->exec($sqlCambiarEstado);
            
            if ($resultadoCambio >= 0) {
                // Si el estado es "Resuelto" (4), actualizar fecha de finalización en historial
                if ($_POST['nuevo_estado'] == 4) {
                    $sqlFinalizarHistorial = "UPDATE historial_incidentes_tickets SET 
                                                hist_tic_fecha_finalizacion = CURRENT
                                            WHERE hist_ticket = '{$_POST['tic_numero_ticket']}' 
                                            AND hist_tic_fecha_finalizacion IS NULL";
                    
                    HistorialTicket::getDB()->exec($sqlFinalizarHistorial);
                }
                
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Estado del ticket actualizado correctamente',
                    'data' => [
                        'numero_ticket' => $_POST['tic_numero_ticket'],
                        'nuevo_estado' => $_POST['nuevo_estado'],
                        'fecha_cambio' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                throw new Exception('No se pudo actualizar el estado del ticket');
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cambiar estado: ' . $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // OBTENER HISTORIAL DE UN TICKET - API
    // ===========================================
    public static function obtenerHistorialAPI() {
        getHeadersApi();
        
        if (empty($_GET['numero_ticket'])) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Número de ticket es requerido',
                'data' => []
            ]);
            exit;
        }
        
        try {
            $numeroTicket = trim(htmlspecialchars($_GET['numero_ticket']));
            
            $sqlHistorial = "SELECT 
                               h.hist_tic_id,
                               h.hist_tic_fecha_inicio,
                               h.hist_tic_fecha_finalizacion,
                               (enc.per_nom1 || ' ' || enc.per_ape1) as nombre_encargado,
                               (sol.per_nom1 || ' ' || sol.per_ape1) as nombre_solicitante,
                               d.dep_desc_lg as nombre_dependencia
                           FROM historial_incidentes_tickets h
                           INNER JOIN mper enc ON h.hist_tic_encargado = enc.per_catalogo
                           INNER JOIN mper sol ON h.hist_tic_solicitante = sol.per_catalogo
                           INNER JOIN mdep d ON h.hist_dependencia = d.dep_llave
                           WHERE h.hist_ticket = '{$numeroTicket}'
                           ORDER BY h.hist_tic_fecha_inicio DESC";
            
            $historialTicket = HistorialTicket::consultarSQL($sqlHistorial);
            
            if (!empty($historialTicket)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Historial encontrado correctamente',
                    'detalle' => count($historialTicket),
                    'data' => $historialTicket
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontró historial para este ticket',
                    'detalle' => 0,
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener historial: ' . $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
    // ===========================================
    // OBTENER DETALLES COMPLETOS DE UN TICKET - API
    // ===========================================
    public static function obtenerDetalleAPI() {
        getHeadersApi();
        
        if (empty($_GET['numero_ticket'])) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Número de ticket es requerido',
                'data' => []
            ]);
            exit;
        }
        
        try {
            $numeroTicket = trim(htmlspecialchars($_GET['numero_ticket']));
            
            // Consulta completa del ticket con toda su información
            $sqlDetalleCompleto = "SELECT 
                                     ft.form_tick_num,
                                     ft.tic_comentario_falla,
                                     ft.tic_correo_electronico,
                                     ft.tic_imagen,
                                     ft.form_fecha_creacion,
                                     (p.per_nom1 || ' ' || p.per_nom2 || ' ' || p.per_ape1 || ' ' || p.per_ape2) as solicitante_completo,
                                     p.per_desc_empleo as cargo_solicitante,
                                     d.dep_desc_lg as dependencia_completa,
                                     ta.estado_ticket,
                                     et.est_tic_desc as estado_descripcion,
                                     (enc.per_nom1 || ' ' || enc.per_ape1) as encargado_nombre,
                                     enc.per_desc_empleo as cargo_encargado
                                 FROM formulario_ticket ft
                                 INNER JOIN mper p ON ft.form_tic_usu = p.per_catalogo
                                 INNER JOIN mdep d ON ft.tic_dependencia = d.dep_llave
                                 LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                                 LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                 LEFT JOIN mper enc ON ta.tic_encargado = enc.per_catalogo
                                 WHERE ft.form_tick_num = '{$numeroTicket}'";
            
            $detalleTicket = FormularioTicket::consultarSQL($sqlDetalleCompleto);
            
            if (!empty($detalleTicket)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Detalle del ticket obtenido correctamente',
                    'data' => $detalleTicket[0]
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontró el ticket especificado',
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener detalle: ' . $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // DASHBOARD - MÉTRICAS GENERALES - API  
    // ===========================================
    public static function obtenerMetricasAPI() {
        getHeadersApi();
        
        try {
            // Contar tickets por estado
            $sqlTicketsPorEstado = "SELECT 
                                      et.est_tic_desc as estado,
                                      COUNT(*) as cantidad
                                  FROM formulario_ticket ft
                                  LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                                  LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                  GROUP BY et.est_tic_id, et.est_tic_desc
                                  ORDER BY et.est_tic_id";
            
            $ticketsPorEstado = FormularioTicket::consultarSQL($sqlTicketsPorEstado);
            
            // Contar tickets totales del mes actual
            $anioMes = date('Ym');
            $sqlTicketsMesActual = "SELECT COUNT(*) as total 
                                   FROM formulario_ticket 
                                   WHERE form_tick_num LIKE 'TK{$anioMes}%'";
            
            $ticketsMes = FormularioTicket::consultarSQL($sqlTicketsMesActual);
            
            // Contar tickets por dependencia (top 5)
            $sqlTicketsPorDependencia = "SELECT 
                                           d.dep_desc_md as dependencia,
                                           COUNT(*) as cantidad
                                       FROM formulario_ticket ft
                                       INNER JOIN mdep d ON ft.tic_dependencia = d.dep_llave
                                       GROUP BY d.dep_llave, d.dep_desc_md
                                       ORDER BY cantidad DESC";
            
            $ticketsPorDependencia = FormularioTicket::consultarSQL($sqlTicketsPorDependencia);
            
            // Tickets resueltos vs pendientes
            $sqlResumenGeneral = "SELECT 
                                    SUM(CASE WHEN ta.estado_ticket = 4 THEN 1 ELSE 0 END) as resueltos,
                                    SUM(CASE WHEN ta.estado_ticket != 4 OR ta.estado_ticket IS NULL THEN 1 ELSE 0 END) as pendientes,
                                    COUNT(*) as total
                                FROM formulario_ticket ft
                                LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket";
            
            $resumenGeneral = FormularioTicket::consultarSQL($sqlResumenGeneral);
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Métricas obtenidas correctamente',
                'data' => [
                    'tickets_por_estado' => $ticketsPorEstado,
                    'tickets_mes_actual' => $ticketsMes[0]->total ?? 0,
                    'tickets_por_dependencia' => $ticketsPorDependencia,
                    'resumen_general' => $resumenGeneral[0] ?? null
                ]
            ]);
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener métricas: ' . $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // BUSCAR TICKETS POR FILTROS - API
    // ===========================================
    public static function buscarConFiltrosAPI() {
        getHeadersApi();
        
        try {
            $filtros = [];
            $condicionesSQL = [];
            
            // Construir filtros dinámicamente
            if (!empty($_POST['estado_filtro'])) {
                $condicionesSQL[] = "ta.estado_ticket = {$_POST['estado_filtro']}";
            }
            
            if (!empty($_POST['dependencia_filtro'])) {
                $condicionesSQL[] = "ft.tic_dependencia = {$_POST['dependencia_filtro']}";
            }
            
            if (!empty($_POST['encargado_filtro'])) {
                $condicionesSQL[] = "ta.tic_encargado = {$_POST['encargado_filtro']}";
            }
            
            if (!empty($_POST['fecha_desde'])) {
                $condicionesSQL[] = "ft.form_fecha_creacion >= '{$_POST['fecha_desde']}'";
            }
            
            if (!empty($_POST['fecha_hasta'])) {
                $condicionesSQL[] = "ft.form_fecha_creacion <= '{$_POST['fecha_hasta']}'";
            }
            
            if (!empty($_POST['numero_ticket'])) {
                $numeroLimpio = trim(strtoupper($_POST['numero_ticket']));
                $condicionesSQL[] = "ft.form_tick_num LIKE '%{$numeroLimpio}%'";
            }
            
            // Base de la consulta
            $sqlFiltrada = "SELECT 
                             ft.form_tick_num,
                             ft.tic_comentario_falla,
                             ft.tic_correo_electronico,
                             ft.form_fecha_creacion,
                             (p.per_nom1 || ' ' || p.per_ape1) as nombre_solicitante,
                             d.dep_desc_md as nombre_dependencia,
                             ta.estado_ticket,
                             et.est_tic_desc as descripcion_estado,
                             (enc.per_nom1 || ' ' || enc.per_ape1) as nombre_encargado
                           FROM formulario_ticket ft
                           INNER JOIN mper p ON ft.form_tic_usu = p.per_catalogo
                           INNER JOIN mdep d ON ft.tic_dependencia = d.dep_llave
                           LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                           LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                           LEFT JOIN mper enc ON ta.tic_encargado = enc.per_catalogo";
            
            // Agregar condiciones WHERE si hay filtros
            if (!empty($condicionesSQL)) {
                $sqlFiltrada .= " WHERE " . implode(' AND ', $condicionesSQL);
            }
            
            $sqlFiltrada .= " ORDER BY ft.form_fecha_creacion DESC";
            
            $ticketsFiltrados = FormularioTicket::consultarSQL($sqlFiltrada);
            
            if (!empty($ticketsFiltrados)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Búsqueda completada correctamente',
                    'detalle' => count($ticketsFiltrados),
                    'data' => $ticketsFiltrados
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se encontraron tickets con los filtros aplicados',
                    'detalle' => 0,
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error en la búsqueda: ' . $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
    // ===========================================
    // BUSCAR TICKETS POR USUARIO - API
    // ===========================================
    public static function buscarTicketsUsuarioAPI() {
        getHeadersApi();
        
        $erroresValidacion = [];
        
        // Validar que se proporcione el ID del usuario
        if (empty($_POST['usuario_id'])) {
            $erroresValidacion[] = 'El ID del usuario es obligatorio';
        } elseif (!is_numeric($_POST['usuario_id'])) {
            $erroresValidacion[] = 'El ID del usuario debe ser numérico';
        }
        
        if (!empty($erroresValidacion)) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => implode(', ', $erroresValidacion),
                'data' => []
            ]);
            exit;
        }
        
        try {
            // Consulta filtrada por usuario
            $sqlTicketsUsuario = "SELECT 
                                     ft.form_tick_num,
                                     ft.tic_comentario_falla,
                                     ft.tic_correo_electronico,
                                     ft.tic_imagen,
                                     ft.form_fecha_creacion,
                                     (p.per_nom1 || ' ' || p.per_nom2 || ' ' || p.per_ape1 || ' ' || p.per_ape2) as nombre_completo_solicitante,
                                     d.dep_desc_lg as nombre_dependencia,
                                     d.dep_desc_md as nombre_dependencia_corto,
                                     ta.estado_ticket,
                                     et.est_tic_desc as descripcion_estado,
                                     (enc.per_nom1 || ' ' || enc.per_ape1) as nombre_encargado
                                 FROM formulario_ticket ft
                                 INNER JOIN mper p ON ft.form_tic_usu = p.per_catalogo
                                 INNER JOIN mdep d ON ft.tic_dependencia = d.dep_llave
                                 LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                                 LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                 LEFT JOIN mper enc ON ta.tic_encargado = enc.per_catalogo
                                 WHERE ft.form_tic_usu = {$_POST['usuario_id']}
                                 ORDER BY ft.form_fecha_creacion DESC";
            
            $ticketsUsuario = FormularioTicket::consultarSQL($sqlTicketsUsuario);
            
            if (!empty($ticketsUsuario)) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Tickets del usuario encontrados correctamente',
                    'detalle' => count($ticketsUsuario),
                    'data' => $ticketsUsuario
                ]);
            } else {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario no tiene tickets registrados',
                    'detalle' => 0,
                    'data' => []
                ]);
            }
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al buscar tickets del usuario: ' . $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    // ===========================================
    // REPORTES ADICIONALES - API
    // ===========================================
    public static function obtenerReportesAvanzadosAPI() {
        getHeadersApi();
        
        try {
            // Reporte de tickets por mes
            $sqlTicketsPorMes = "SELECT 
                                   SUBSTR(form_tick_num, 3, 6) as periodo,
                                   COUNT(*) as cantidad
                               FROM formulario_ticket
                               GROUP BY SUBSTR(form_tick_num, 3, 6)
                               ORDER BY periodo DESC";
            
            $ticketsPorMes = FormularioTicket::consultarSQL($sqlTicketsPorMes);
            
            // Reporte de encargados más activos
            $sqlEncargadosActivos = "SELECT 
                                       (enc.per_nom1 || ' ' || enc.per_ape1) as nombre_encargado,
                                       COUNT(*) as tickets_asignados,
                                       SUM(CASE WHEN ta.estado_ticket = 4 THEN 1 ELSE 0 END) as tickets_resueltos
                                   FROM tickets_asignados ta
                                   INNER JOIN mper enc ON ta.tic_encargado = enc.per_catalogo
                                   GROUP BY enc.per_catalogo, enc.per_nom1, enc.per_ape1
                                   ORDER BY tickets_asignados DESC";
            
            $encargadosActivos = TicketAsignado::consultarSQL($sqlEncargadosActivos);
            
            // Tickets por estado (conteo detallado)
            $sqlEstadosDetallados = "SELECT 
                                       et.est_tic_desc as estado,
                                       COUNT(*) as cantidad,
                                       ROUND((COUNT(*) * 100.0 / 
                                         (SELECT COUNT(*) FROM formulario_ticket)), 2) as porcentaje
                                   FROM formulario_ticket ft
                                   LEFT JOIN tickets_asignados ta ON ft.form_tick_num = ta.tic_numero_ticket
                                   LEFT JOIN estado_ticket et ON ta.estado_ticket = et.est_tic_id
                                   GROUP BY et.est_tic_id, et.est_tic_desc
                                   ORDER BY cantidad DESC";
            
            $estadosDetallados = FormularioTicket::consultarSQL($sqlEstadosDetallados);
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Reportes avanzados generados correctamente',
                'data' => [
                    'tickets_por_mes' => $ticketsPorMes,
                    'encargados_activos' => $encargadosActivos,
                    'estados_detallados' => $estadosDetallados
                ]
            ]);
            
        } catch (Exception $excepcionCapturada) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al generar reportes: ' . $excepcionCapturada->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }
}