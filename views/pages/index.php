<?php
session_start();
$isAdmin = ($_SESSION['usuario_rol'] ?? 'EMPLEADO') === 'ADMIN';
$userName = $_SESSION['user'] ?? 'Usuario';
$userRole = $_SESSION['usuario_rol'] ?? 'EMPLEADO';
?>
<style>
    body {
        background: #f8fbff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 80%, rgba(44, 90, 160, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(74, 144, 226, 0.2) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(135, 206, 235, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 60% 70%, rgba(227, 242, 253, 0.1) 0%, transparent 50%);
        z-index: -1;
        animation: backgroundMove 20s ease-in-out infinite;
    }

    @keyframes backgroundMove {
        0%, 100% { transform: translateX(0) translateY(0); }
        25% { transform: translateX(-20px) translateY(-10px); }
        50% { transform: translateX(20px) translateY(10px); }
        75% { transform: translateX(-10px) translateY(20px); }
    }

    .header {
        padding: 3rem 2rem;
        text-align: center;
        border-radius: 20px;
        margin-top: 2rem;
        margin-bottom: 3rem;
        max-width: 1140px;
        margin-left: auto;
        margin-right: auto;
        background: rgba(44, 90, 160, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(44, 90, 160, 0.2);
        box-shadow: 0 8px 25px rgba(44, 90, 160, 0.15);
    }
    
    .logo {
        font-size: 3.5rem;
        font-weight: 800;
        color: #2c5aa0;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(44, 90, 160, 0.3);
    }

    .welcome-section {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        border: 1px solid rgba(44, 90, 160, 0.2);
        box-shadow: 0 5px 15px rgba(44, 90, 160, 0.1);
    }

    .user-welcome {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 1rem;
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        background: #2c5aa0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .user-details h3 {
        margin: 0;
        color: #2c5aa0;
        font-weight: 700;
    }

    .user-role-badge {
        background: #28a745;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }


    
    .container {
        max-width: 1140px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        z-index: 1;
    }
    
    .product-img {
        border-radius: 15px;
        width: 100%;
        height: 200px;
        max-height: 300px;
        object-fit: cover;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 8px 25px rgba(30, 60, 114, 0.2);
        margin-bottom: 1.5rem;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 1.2rem;
        text-align: center;
        overflow: hidden;
        position: relative;
    }

    .product-img::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(30, 60, 114, 0.1), rgba(74, 144, 226, 0.1));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-img:hover::before {
        opacity: 1;
    }

    .product-img:hover {
        transform: scale(1.08) rotate(-2deg);
        box-shadow: 0 20px 40px rgba(30, 60, 114, 0.3);
        cursor: pointer;
    }

    .product-img img {
        width: 100%;
        height: 100%;
        border-radius: 15px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .product-img:hover img {
        transform: scale(1.1);
    }
    
    .card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(74, 144, 226, 0.3);
        border-radius: 20px;
        box-shadow: 
            0 8px 25px rgba(0, 0, 0, 0.3),
            inset 0 1px 0 rgba(74, 144, 226, 0.2);
        position: relative;
        overflow: hidden;
    }

    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(74, 144, 226, 0.2), transparent);
        transition: left 0.6s ease;
    }

    .card:hover::before {
        left: 100%;
    }
    
    .card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: 
            0 25px 50px rgba(0, 0, 0, 0.4),
            inset 0 1px 0 rgba(74, 144, 226, 0.3);
        background: rgba(0, 0, 0, 0.4);
    }

    .card-body {
        position: relative;
        z-index: 1;
        padding: 2rem 1.5rem;
    }

    .lead {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 2.5rem;
        border-radius: 20px;
        border: 1px solid rgba(44, 90, 160, 0.2);
        box-shadow: 0 8px 25px rgba(44, 90, 160, 0.15);
        color: #2c5aa0;
        font-weight: 500;
        font-size: 1.1rem;
    }

    h2 {
        color: #2c5aa0 !important;
        text-shadow: 0 2px 4px rgba(44, 90, 160, 0.3);
        font-weight: 700;
    }

    .text-muted {
        color: #1e3f73 !important;
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

    .btn-primary {
        background: linear-gradient(135deg, #2c5aa0, #1e3f73);
        box-shadow: 0 4px 15px rgba(44, 90, 160, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1e3f73, #1a365d);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(44, 90, 160, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #1e7e34, #155724);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: #000;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #e0a800, #d39e00);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8, #117a8b);
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #117a8b, #0c5460);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6c757d, #545b62);
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #545b62, #383d41);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545, #bd2130);
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #bd2130, #a71e2a);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
    }

    .card-title {
        color: #2c5aa0;
        font-weight: 700;
    }

    .mt-5 .row .col-md-6 {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 4rem 3rem;
        border-radius: 25px;
        border: 1px solid rgba(44, 90, 160, 0.2);
        box-shadow: 0 8px 25px rgba(44, 90, 160, 0.15);
    }

    .mt-5 h2 {
        margin-bottom: 2.5rem;
        font-size: 2.5rem;
    }

    .mt-5 p {
        color: #2c5aa0;
        font-weight: 500;
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
    }

    .mt-5 strong {
        color: #1e3f73;
    }
</style>

<body>
    <div class="header">
        <div class="logo">¡Bienvenido al Sistema de Celulares!</div>
    </div>
    
    <div class="container">
        <div class="welcome-section">
            <div class="user-welcome">
            <div class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="user-details text-center">
                <h3><?= $userName ?></h3>
                <span class="user-role-badge"><?= $userRole ?></span>
            </div>
            </div>
            <p class="text-center text-muted mb-0">
            <?php if ($isAdmin): ?>
                Tienes acceso completo a todos los módulos del sistema
            <?php else: ?>
                Tienes acceso a: Clientes, Ventas, Reparaciones e Inventario
            <?php endif; ?>
            </p>
        </div>
        
        <div class="row mb-4">
            <div class="col-12 text-center mb-4">
                <h2 class="text-uppercase fw-bold">Módulos del Sistema</h2>
                <p class="text-muted">Gestiona todos los aspectos de tu negocio desde una sola plataforma.</p>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://www.questionpro.com/blog/wp-content/uploads/2016/08/Portada-gestion-de-clientes.jpg" alt="Gestión de Clientes" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-people-fill me-2 text-primary"></i>Gestión de Clientes
                        </h5>
                        <p class="card-text text-muted">Administra tu base de clientes con información completa y segura.</p>
                        <a href="/proyecto02_macs/clientes" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://cdn-icons-png.flaticon.com/512/10691/10691856.png" alt="Usuarios/Empleados" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-people-fill me-2 text-success"></i>Usuarios/Empleados
                        </h5>
                        <p class="card-text text-muted">Gestiona empleados y usuarios del sistema con roles específicos.</p>
                        <a href="/proyecto02_macs/usuarios" class="btn btn-success">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://down-br.img.susercontent.com/file/br-11134207-7r98o-lxy2q2lgji4yfa" alt="Marcas" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-tags-fill me-2 text-warning"></i>Marcas
                        </h5>
                        <p class="card-text text-muted">Administra las marcas de celulares disponibles en tu negocio.</p>
                        <a href="/proyecto02_macs/marcas" class="btn btn-warning">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://noticias.bidcom.com.ar/wp-content/uploads/2023/10/Samsung-vs.-Motorola-1.jpg" alt="Celulares" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-phone-fill me-2 text-info"></i>Celulares
                        </h5>
                        <p class="card-text text-muted">Gestiona el catálogo de modelos y especificaciones de celulares.</p>
                        <a href="/proyecto02_macs/celulares" class="btn btn-info">Acceder</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://img.freepik.com/vector-gratis/personal-tienda-verifica-cantidad-productos-que-deben-entregar-clientes-dia_1150-51079.jpg" alt="Inventario" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-box-seam-fill me-2 text-secondary"></i>Inventario
                        </h5>
                        <p class="card-text text-muted">
                            <?php if ($isAdmin): ?>
                                Controla el stock, precios y disponibilidad de productos.
                            <?php else: ?>
                                Consulta disponibilidad y precios de productos.
                            <?php endif; ?>
                        </p>
                        <a href="/proyecto02_macs/inventario" class="btn btn-secondary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://soporte.mygestion.com/media/wp-content/uploads/ventas.jpg" alt="Ventas" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-cart-fill me-2 text-success"></i>Ventas
                        </h5>
                        <p class="card-text text-muted">Procesa ventas, gestiona facturación y controla transacciones.</p>
                        <a href="/proyecto02_macs/ventas" class="btn btn-success">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://www.guatemala.com/fotos/201709/portada-885x500.jpg" alt="Reparaciones" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-tools me-2 text-danger"></i>Reparaciones
                        </h5>
                        <p class="card-text text-muted">Administra el servicio técnico y reparaciones de dispositivos.</p>
                        <a href="/proyecto02_macs/reparaciones" class="btn btn-danger">Acceder</a>
                    </div>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://s1.significados.com/foto/estadistica-como-herramienta-de-analisis.jpg?class=article" alt="Estadísticas" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-graph-up me-2 text-primary"></i>Estadísticas
                        </h5>
                        <p class="card-text text-muted">Analiza el rendimiento del negocio con reportes y gráficos.</p>
                        <a href="/proyecto02_macs/estadisticas" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://active-campaign.transforms.svdcdn.com/production/general/wp/2022_04_sales-methodology.png?w=1600&q=80&auto=format&fit=clip&dm=1732095556&s=996c257e59bdf6b3808da3bff06971cd" alt="Historial de Ventas" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-clock-history me-2 text-info"></i>Historial de Ventas
                        </h5>
                        <p class="card-text text-muted">Consulta el historial completo de transacciones y actividades.</p>
                        <a href="/proyecto02_macs/historial" class="btn btn-info">Acceder</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-6 mx-auto text-center">
                <a href="/proyecto02_macs/clientes" class="btn btn-primary btn-lg mt-3">Comenzar a administrar</a><br><br>
            </div>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="<?= asset('build/js/inicio.js') ?>"></script>
</body>