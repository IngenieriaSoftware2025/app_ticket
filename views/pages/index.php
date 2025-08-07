<?php
session_start();
// $isAdmin = ($_SESSION['usuario_rol'] ?? 'EMPLEADO') === 'ADMIN';
$userName = $_SESSION['user'] ?? 'Usuario';
// $userRole = $_SESSION['usuario_rol'] ?? 'EMPLEADO';
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
        <div class="logo">🎫 Sistema de Tickets de Atención al Usuario <br><br>
            <h5 class="text-uppercase fw-bold">Reporta problemas, gestiona solicitudes y da seguimiento a todas las incidencias.</h5>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome-section">
            <div class="user-welcome">
            <div class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="user-details text-center">
                <h3><?= $userName ?></h3>
                <!-- <span class="user-role-badge"><?= $userRole ?></span> -->
            </div>
            </div>
            <p class="text-center text-muted mb-0">
            Gestiona todos los tickets de soporte y atención al usuario
            </p>
        </div>
        
        <div class="row mb-4">
            <div class="col-12 text-center mb-4">
            </div>
            
            <!-- CREAR NUEVO TICKET -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://lafabricadeinventos.tienda/wp-content/uploads/2021/09/App-Ticket-Electronico-11_resultado.webp" alt="Crear Ticket" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Crear Nuevo Ticket
                        </h5>
                        <p class="card-text text-muted">Reporta un problema con alguna aplicación de AUTOCOM.</p>
                        <a href="/app_ticket/crear" class="btn btn-primary">Crear Ticket</a>
                    </div>
                </div>
            </div>
            
            <!-- MIS TICKETS -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://id4e.com/wp-content/uploads/2015/02/content.jpg" alt="Mis Tickets" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-person-check-fill me-2 text-success"></i>Mis Tickets
                        </h5>
                        <p class="card-text text-muted">Consulta el estado y historial de tus tickets enviados.</p>
                        <a href="/app_ticket/mis-tickets" class="btn btn-success">Ver Mis Tickets</a>
                    </div>
                </div>
            </div>
            
            <!-- HISTORIAL -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://id4e.com/wp-content/uploads/2014/12/BLurb-Cuadrats3.png" alt="Estado Actual" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-clock-fill me-2 text-warning"></i>Historial Tickets
                        </h5>
                        <p class="card-text text-muted">Revisa el historial de tickets.</p>
                        <a href="/app_ticket/historial" class="btn btn-warning">Ver Historial</a>
                    </div>
                </div>
            </div>
                 
            <!-- LISTA DE TICKETS ACTIVOS -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://eu.zohocommercecdn.com/Banner%20Home%2019%20-12-.png?storefront_domain=www.moviik.com" alt="Tickets Activos" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-list-check me-2 text-danger"></i>Lista de Tickets Activos
                        </h5>
                        <p class="card-text text-muted">Administra todos los tickets pendientes y en proceso.</p>
                        <a href="/app_ticket/activos" class="btn btn-primary">Gestionar Tickets</a>
                    </div>
                </div>
            </div>
            
            <!-- Estadisticas -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-img-top product-img">
                        <img src="https://www.questionpro.com/blog/wp-content/uploads/2020/06/Portada-tipos-de-datos-estadisticos.jpg" alt="Métricas" style="display: block;">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">
                            <i class="bi bi-graph-up me-2 text-warning"></i>Estadisticas Generales
                        </h5>
                        <p class="card-text text-muted">Reportes y estadísticas del sistema de tickets.</p>
                        <a href="/app_ticket/estadisticas" class="btn btn-success">Ver Métricas</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-6 mx-auto text-center">
            </div>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>