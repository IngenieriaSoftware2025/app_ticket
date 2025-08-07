<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>Sistema Celulares</title>
    <style>
        body {
            background: #f8fbff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            max-width: 100vw;
        }



        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: #2c5aa0 !important;
            box-shadow: 0 3px 15px rgba(44, 90, 160, 0.2);
            border-bottom: none;
            padding: 1rem 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-brand {
            font-weight: 600;
            font-size: 1.3rem;
            color: white !important;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
            padding: 1rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar-brand:hover {
            color: #e3f2fd !important;
        }



        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 0 10px 5px 10px;
            padding: 12px 16px !important;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.15) !important;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .dropdown-menu {
            background: #2c5aa0;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-radius: 8px;
            position: static !important;
            width: 100% !important;
            transform: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .dropdown-item {
            color: rgba(255, 255, 255, 0.9) !important;
            transition: all 0.3s ease;
            padding: 8px 40px;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
        }

        .btn {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: 600;
            border-radius: 12px;
            padding: 12px 24px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            position: relative;
            overflow: hidden;
        }



        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #bd2130);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #bd2130, #a71e2a);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        }

        .progress {
            background: rgba(44, 90, 160, 0.1);
            position: fixed;
            bottom: 0;
            left: 280px;
            right: 0;
            height: 6px;
        }

        .progress-bar {
            background: #2c5aa0;
        }

        .container-fluid.pt-5 {
            background: white;
            border-radius: 10px 10px 0 0;
            margin-top: 0;
            margin-left: 280px;
            width: calc(100vw - 280px);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            border-top: 3px solid #2c5aa0;
        }

        footer p {
            color: #2c5aa0;
            font-weight: 600;
        }

        .card, .table, .btn:not(.btn-danger) {
            border-color: #e3f2fd;
        }

        .btn-primary {
            background: #2c5aa0;
            border-color: #2c5aa0;
        }

        .btn-primary:hover {
            background: #1e3f73;
            border-color: #1e3f73;
        }

        .text-primary {
            color: #2c5aa0 !important;
        }

        .bg-primary {
            background-color: #2c5aa0 !important;
        }

        .footer-section {
            position: relative;
            border-top: 2px solid transparent;
            background-image: 
                linear-gradient(135deg, #2c5aa0, #1e3f73),
                linear-gradient(90deg, transparent, #4a90e2, #2c5aa0, #87ceeb, transparent);
            background-size: 100% 100%, 200% 2px;
            background-position: 0 0, 0 0;
            background-repeat: no-repeat;
            animation: gradientFlow 2.5s ease-in-out infinite;
            box-shadow: 0 -2px 8px rgba(44, 90, 160, 0.3);
            margin-left: 280px;
            width: calc(100vw - 280px);
        }

        @keyframes gradientFlow {
            0% { background-position: 0 0, 0% 0; }
            50% { background-position: 0 0, 100% 0; }
            100% { background-position: 0 0, 0% 0; }
        }

        .footer-content {
            text-align: center;
            color: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }



        .footer-copyright {
            font-size: 0.95rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }

        .footer-year {
            color: #87ceeb;
            font-weight: 700;
        }

        .dropdown-simple {
            position: relative;
        }

        .dropdown-content {
            display: none;
            background: rgba(0, 0, 0, 0.2);
            margin-top: 5px;
        }

        .dropdown-simple:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>


    <div class="sidebar">
        <a class="sidebar-brand" href="/proyecto02_macs/inicio">
            <i class="bi bi-phone-fill" style="font-size: 1.5rem; margin-right: 8px;"></i>
            MACS
        </a>
        
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" aria-current="page" href="/proyecto02_macs/inicio"><i class="bi bi-house-fill me-2"></i>Inicio</a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-3" href="/proyecto02_macs/clientes">
                    <i class="bi bi-people-fill me-2"></i>Clientes
                </a>
            </li>

            <?php if ($_SESSION['usuario_rol'] === 'ADMIN'): ?>
            <li class="nav-item">
                <a class="nav-link px-3" href="/proyecto02_macs/usuarios">
                    <i class="bi bi-people-fill me-2"></i>Usuarios/Empleados
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-3" href="/proyecto02_macs/marcas">
                    <i class="bi bi-tags-fill me-2"></i>Marcas
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-3" href="/proyecto02_macs/celulares">
                    <i class="bi bi-phone-fill me-2"></i>Celulares
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link px-3" href="/proyecto02_macs/inventario">
                    <i class="bi bi-box-seam-fill me-2"></i>Inventario
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-3" href="/proyecto02_macs/ventas">
                    <i class="bi bi-cart-fill me-2"></i>Ventas
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link px-3" href="/proyecto02_macs/reparaciones">
                    <i class="bi bi-tools me-2"></i>Reparaciones
                </a>
            </li>

            <?php if ($_SESSION['usuario_rol'] === 'ADMIN'): ?>
            <li class="nav-item dropdown-simple">
                <a class="nav-link px-3" href="#" style="display: flex; align-items: center;">
                    <i class="bi bi-bar-chart-fill me-2"></i>Reportes<i class="bi bi-chevron-down ms-2" style="font-size: 0.7em;"></i>
                </a>
                <ul class="dropdown-content" style="margin: 0;">
                    <li>
                        <a class="dropdown-item nav-link text-white" href="/proyecto02_macs/estadisticas"><i class="ms-lg-0 ms-2 bi bi-graph-up me-2"></i>Estadísticas</a>
                    </li>
                    <li>
                        <a class="dropdown-item nav-link text-white" href="/proyecto02_macs/historial"><i class="ms-lg-0 ms-2 bi bi-clock-history me-2"></i>Historial Ventas</a>
                    </li>
                    <li>
                        <a class="dropdown-item nav-link text-white" href="/proyecto02_macs/rutas_actividades"><i class="ms-lg-0 ms-2 bi bi-activity me-2"></i>Rutas Actividades</a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
        </ul> 
        
        <div style="padding: 15px 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: auto;">
            <div style="color: white; text-align: center; margin-bottom: 15px;">
                <i class="bi bi-person-circle" style="font-size: 1.2rem; margin-right: 8px;"></i>
                <div style="font-weight: 600; font-size: 0.95rem;"><?= $_SESSION['user'] ?? 'Usuario' ?></div>
                <div style="background: rgba(255, 255, 255, 0.2); padding: 3px 10px; border-radius: 10px; font-size: 0.8rem; margin-top: 5px; display: inline-block;">
                    <?= $_SESSION['usuario_rol'] ?? 'EMPLEADO' ?>
                </div>
            </div>
            <button onclick="logout()" class="btn btn-danger" style="margin: 0; width: 100%;"><i class="bi bi-arrow-bar-left"></i>Cerrar Sesión</button>
        </div>
    </div>

    <div class="progress fixed-bottom" style="height: 6px;">
        <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    
    <div class="container-fluid pt-5 mb-4" style="min-height: 85vh">
        <?php echo $contenido; ?>
    </div>

    <div class="footer-section">
        <div class="container">
            <div class="footer-content">
                <div class="footer-copyright">
                    Comando de Informática y Tecnología, <span class="footer-year"><?= date('Y') ?></span> &copy;
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const logout = async () => {
            try {
                const confirmacion = await Swal.fire({
                    title: '¿Cerrar sesión?',
                    text: "¿Estás seguro que deseas cerrar la sesión?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cerrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                });

                if (confirmacion.isConfirmed) {
                    await Swal.fire({
                        title: 'Cerrando sesión',
                        text: 'Cerrando sesión...',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1000,
                        timerProgressBar: true
                    });

                    location.href = '/proyecto02_macs/logout';
                }

            } catch (error) {
                console.log(error);
                location.href = '/proyecto02_macs/logout';
            }
        }
        window.logout = logout;
    </script>
</body>
</html>