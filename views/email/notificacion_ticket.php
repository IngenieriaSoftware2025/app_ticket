<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Notificación de Ticket</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa; 
            margin: 0; 
            padding: 20px; 
            line-height: 1.6;
            color: #333;
        }
        .contenedor { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        .encabezado { 
            background: linear-gradient(135deg, #2c5aa0, #1e3f73); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .logo { 
            width: 60px; 
            height: 60px; 
            margin-bottom: 15px; 
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .titulo-sistema { 
            font-size: 24px; 
            font-weight: bold; 
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .subtitulo { 
            font-size: 16px; 
            opacity: 0.9; 
            margin: 5px 0 0 0; 
        }
        .contenido { 
            padding: 30px 25px; 
        }
        .estado-badge { 
            background: <?= $config['color'] ?>; 
            color: white; 
            padding: 12px 24px; 
            border-radius: 25px; 
            display: inline-block; 
            font-weight: bold; 
            margin: 20px 0;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .mensaje-principal {
            font-size: 18px;
            color: #495057;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid <?= $config['color'] ?>;
        }
        .detalle-ticket { 
            background: #ffffff; 
            padding: 25px; 
            border-radius: 8px; 
            margin: 20px 0; 
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .info-item { 
            margin: 15px 0; 
            font-size: 16px; 
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .info-label { 
            font-weight: bold; 
            color: #2c5aa0;
            min-width: 140px;
        }
        .info-valor {
            color: #495057;
            flex: 1;
        }
        .descripcion-contenedor {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .descripcion-titulo {
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .descripcion-texto {
            color: #495057;
            line-height: 1.6;
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 3px solid <?= $config['color'] ?>;
        }
        .pie { 
            background: #2c5aa0; 
            color: white;
            padding: 25px 20px; 
            text-align: center; 
            font-size: 14px;
        }
        .pie p {
            margin: 5px 0;
            opacity: 0.9;
        }
        .separador {
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 20px 0;
        }
        @media (max-width: 600px) {
            .contenedor { margin: 10px; }
            .contenido { padding: 20px 15px; }
            .info-item { flex-direction: column; gap: 5px; }
            .info-label { min-width: auto; }
        }
    </style>
</head>
<body>
    <div class='contenedor'>
        <!-- Encabezado -->
        <div class='encabezado'>
            <div class='logo'><?= $config['icono'] ?></div>
            <div class='titulo-sistema'>Sistema de Tickets</div>
            <div class='subtitulo'>Comando de Informática y Tecnología</div>
        </div>
        
        <!-- Contenido Principal -->
        <div class='contenido'>
            <h2 style='color: #2c5aa0; margin-top: 0; font-size: 28px; text-align: center;'>
                <?= $config['titulo'] ?>
            </h2>
            
            <div class='mensaje-principal'>
                <?= $config['mensaje'] ?>
            </div>
            
            <div class='estado-badge'>
                <?= $config['icono'] ?> <?= strtoupper($tipo) ?>
            </div>
            
            <!-- Detalles del Ticket -->
            <div class='detalle-ticket'>
                <div class='info-item'>
                    <span class='info-label'>📋 Número de Ticket:</span>
                    <span class='info-valor'><strong><?= $datos['numero'] ?></strong></span>
                </div>
                
                <div class='info-item'>
                    <span class='info-label'>📅 Fecha de Actualización:</span>
                    <span class='info-valor'><?= $fecha_actual ?></span>
                </div>

                <?php if (isset($datos['solicitante'])): ?>
                <div class='info-item'>
                    <span class='info-label'>👤 Solicitante:</span>
                    <span class='info-valor'><?= $datos['solicitante'] ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($datos['tecnico'])): ?>
                <div class='info-item'>
                    <span class='info-label'>🔧 Técnico Asignado:</span>
                    <span class='info-valor'><?= $datos['tecnico'] ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($datos['descripcion']) && !empty($datos['descripcion'])): ?>
                    <?php 
                    $descripcion_corta = strlen($datos['descripcion']) > 200 ? 
                                        substr($datos['descripcion'], 0, 200) . '...' : 
                                        $datos['descripcion'];
                    ?>
                    <div class='separador'></div>
                    <div class='descripcion-contenedor'>
                        <div class='descripcion-titulo'>📝 Descripción del Problema:</div>
                        <div class='descripcion-texto'>
                            <?= nl2br(htmlspecialchars($descripcion_corta)) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pie del Correo -->
        <div class='pie'>
            <p><strong>&copy; <?= date('Y') ?> Comando de Informática y Tecnología</strong></p>
            <p>Todos los derechos reservados</p>
            <p style='font-size: 12px; margin-top: 15px; opacity: 0.8;'>
                Este es un mensaje automático, por favor no responda a este correo.
            </p>
        </div>
    </div>
</body>
</html>