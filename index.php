<?php
// Incluir configuración
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard CEO - Supermercados</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/datatables.css" />
    <link rel="stylesheet" type="text/css" href="css/movil.css" />
    <link rel="stylesheet" type="text/css" href="css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/maintenance.css" />


    <style>
        :root {
            --primary-color: #0057b8;
            --secondary-color: #00a651;
            --accent-color: #ffc107;
            --text-color: #333;
            --light-bg: #f9f9f9;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), #083f7d);
            color: white;
            min-height: calc(100vh - 56px);
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 4px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .dashboard-card {
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            border: none;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-card .card-header {
            background: linear-gradient(45deg, var(--primary-color), #083f7d);
            color: white;
            border-bottom: none;
            font-weight: 600;
        }
        
        .card-icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .text-success {
            color: var(--secondary-color) !important;
        }
        
        .text-warning {
            color: var(--accent-color) !important;
        }
        
        .badge-success {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .badge-warning {
            background-color: var(--accent-color);
            color: white;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--primary-color);
            border-top: 5px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .widget-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .trend-indicator {
            margin-left: 10px;
            font-size: 0.9rem;
        }
        
        .trend-up {
            color: var(--secondary-color);
        }
        
        .trend-down {
            color: #dc3545;
        }
        
        #menu-toggle {
            cursor: pointer;
        }
        
        /* Custom styles for charts */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .content {
                width: 100%;
            }
            .content.active {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
        
        /* DataTables customization */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            color: white !important;
            border: 1px solid var(--primary-color) !important;
        }
        
        /* Dashboard theme customization */
        .custom-select:focus, .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 87, 184, 0.25);
        }
        
        .date-filters {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        /* DataTables Buttons styling */
        .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_info {
            padding-top: 15px;
        }

        /* Make sure buttons don't get too small on mobile */
        @media (max-width: 768px) {
            .dt-buttons {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                margin-bottom: 10px;
            }
            
            .dt-buttons .btn {
                margin-bottom: 5px;
            }
            
            .dataTables_filter {
                text-align: center;
            }
        }

        /* Estilos para los tooltips */
        .tooltip {
            --bs-tooltip-bg: var(--primary-color, #0057b8);
            --bs-tooltip-color: white;
            --bs-tooltip-opacity: 1;
            font-weight: 500;
        }

        .tooltip .tooltip-arrow::before {
            border-right-color: var(--primary-color, #0057b8) !important;
        }

        /* Mejora de espacio para tooltips */
        .sidebar.collapsed .nav-link {
            padding-right: 10px;
        }

        /* Estilos para submenús en el sidebar */
        .dropdown-menu-sidebar {
            background-color: rgba(0, 0, 0, 0.2);
            border: none;
            border-radius: 0;
            margin-top: 0;
            padding: 0;
            width: 100%;
            position: static !important;
            transform: none !important;
        }

        .dropdown-menu-sidebar .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            white-space: normal;
        }

        .dropdown-menu-sidebar .dropdown-item:hover, 
        .dropdown-menu-sidebar .dropdown-item:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            background-color: rgba(0, 0, 0, 0.2);
            border: none;
            border-radius: 0;
            left: 100%;
            margin-top: -1px;
            top: 0;
            display: none;
        }

        .dropdown-submenu:hover > .dropdown-menu {
            display: block;
        }

        .dropdown-submenu > a:after {
            content: "\f105";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Ajustes para sidebar colapsado */
        .sidebar.collapsed .dropdown-menu-sidebar {
            display: none;
        }

        .sidebar.collapsed .nav-item.dropdown:hover .dropdown-menu-sidebar {
            display: block;
            position: absolute !important;
            left: 100%;
            top: 0;
            width: 200px;
            margin-top: 0;
            background-color: #083f7d;
        }

        /* Ajustes para móviles */
        @media (max-width: 768px) {
            .dropdown-submenu .dropdown-menu {
                position: static !important;
                margin-left: 1rem;
            }
            
            .dropdown-submenu > a:after {
                transform: rotate(90deg);
                top: 0.8rem;
            }
            
            .dropdown-submenu.show > .dropdown-menu {
                display: block;
            }
        }

    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navbar -->
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- Botón toggle para sidebar en dispositivos no móviles -->
            <button class="btn btn-primary d-none d-lg-block" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Botón toggle para sidebar en móviles -->
            <button class="btn btn-primary d-lg-none me-2" id="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="#">
                <i class="fas fa-shopping-cart me-2"></i>
                SuperDashboard
            </a>
            
            <!-- Botón toggle para menú de usuario en móviles -->
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-user-circle"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> CEO
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Perfil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Salir</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar 
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">-->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">    
                <div class="position-sticky pt-3">
                    <!-- Estructura actualizada de enlaces del sidebar -->
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" id="overview-link" data-section="overview-section" 
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Panel General">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Panel General</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="sales-link" data-section="sales-section"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Ventas">
                                <i class="fas fa-chart-line"></i>
                                <span>Ventas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="products-link" data-section="products-section"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Productos">
                                <i class="fas fa-shopping-basket"></i>
                                <span>Productos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="inventory-link" data-section="inventory-section"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Inventario">
                                <i class="fas fa-boxes"></i>
                                <span>Inventario</span>
                            </a>
                        </li>

                        <!-- Añadir después de la sección de inventario en el sidebar -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="maintenance-link" data-bs-toggle="collapse" data-bs-target="#maintenance-collapse" aria-expanded="false" aria-controls="maintenance-collapse"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Mantenimiento">
                                <i class="fas fa-cogs"></i>
                                <span>Mantenimiento</span>
                            </a>
                            <div class="collapse" id="maintenance-collapse">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" id="clients-link" data-section="clients-section"
                                        data-bs-toggle="tooltip" data-bs-placement="right" title="Clientes">
                                            <i class="fas fa-users"></i>
                                            <span>Clientes</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" id="products-maintenance-link" data-section="products-maintenance-section"
                                        data-bs-toggle="tooltip" data-bs-placement="right" title="Productos">
                                            <i class="fas fa-box"></i>
                                            <span>Productos</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    </ul>
                    <!-- <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" id="overview-link" data-section="overview-section">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Panel General</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="sales-link" data-section="sales-section">
                                <i class="fas fa-chart-line"></i>
                                <span>Ventas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="products-link" data-section="products-section">
                                <i class="fas fa-shopping-basket"></i>
                                <span>Productos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="inventory-link" data-section="inventory-section">
                                <i class="fas fa-boxes"></i>
                                <span>Inventario</span>
                            </a>
                        </li>
                    </ul> -->
                    
                    <hr class="my-3 bg-light opacity-25">
                    
                    <!-- Información adicional para mostrar en el sidebar -->
                    <div class="company-info mt-4 px-3 text-white">
                        <div class="text-center mb-3">
                            <!--<img src="https://via.placeholder.com/100" alt="Company Logo" class="img-fluid rounded-circle" style="max-width: 80px;">
    --> <h5 class="mt-3 text-white">Supermercados S.A.</h5>
                        </div>
                        <div class="small">
                            <p class="mb-1"><i class="fas fa-clock me-2"></i> Actualizado: <span id="last-update-time">Hoy 15:30</span></p>
                            <p class="mb-0"><i class="fas fa-user me-2"></i> Usuario: <span id="current-user">Administrador</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 content">
                <!-- Date Range Filter -->
                <div class="date-filters">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0"><i class="fas fa-filter me-2"></i> Filtros</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="dateFrom">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="dateTo">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" id="applyDateFilter">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overview Section -->
                <section id="overview-section" class="dashboard-section active">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Panel General</h2>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshOverview">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-export me-1"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Cards -->
                    <div class="row" id="kpi-cards">
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header d-flex align-items-center">
                                    <div class="card-icon">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                    <h5 class="card-title mb-0">Ventas Totales</h5>
                                </div>
                                <div class="card-body">
                                    <h2 class="widget-value" id="totalSales">$0.00</h2>
                                    <div class="d-flex align-items-center mt-2">
                                        <span class="text-muted">vs periodo anterior</span>
                                        <span class="trend-indicator trend-up" id="salesTrend">
                                            <i class="fas fa-long-arrow-alt-up"></i> 0%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header d-flex align-items-center">
                                    <div class="card-icon">
                                        <i class="fas fa-receipt fa-2x"></i>
                                    </div>
                                    <h5 class="card-title mb-0">Transacciones</h5>
                                </div>
                                <div class="card-body">
                                    <h2 class="widget-value" id="transactionCount">0</h2>
                                    <div class="d-flex align-items-center mt-2">
                                        <span class="text-muted">Ticket Promedio</span>
                                        <span class="ms-auto" id="avgTicket">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header d-flex align-items-center">
                                    <div class="card-icon">
                                        <i class="fas fa-chart-pie fa-2x"></i>
                                    </div>
                                    <h5 class="card-title mb-0">Margen Bruto</h5>
                                </div>
                                <div class="card-body">
                                    <h2 class="widget-value" id="totalProfit">$0.00</h2>
                                    <div class="d-flex align-items-center mt-2">
                                        <span class="text-muted">Porcentaje</span>
                                        <span class="ms-auto" id="profitMargin">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header d-flex align-items-center">
                                    <div class="card-icon">
                                        <i class="fas fa-boxes fa-2x"></i>
                                    </div>
                                    <h5 class="card-title mb-0">Valor Inventario</h5>
                                </div>
                                <div class="card-body">
                                    <h2 class="widget-value" id="inventoryValue">$0.00</h2>
                                    <div class="d-flex align-items-center mt-2">
                                        <span class="text-muted">Rotación</span>
                                        <span class="ms-auto" id="inventoryTurnover">0x</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Tendencia de Ventas Mensuales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="monthlySalesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Ventas por Departamento</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="departmentSalesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Charts Row -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Ventas por Hora del Día</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="hourlyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Métodos de Pago</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="paymentMethodsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sales Section -->
                <section id="sales-section" class="dashboard-section d-none">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h2><i class="fas fa-chart-line me-2"></i>Análisis de Ventas</h2>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshSales">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-export me-1"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Trends -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Tendencia de Ventas Mensuales (Últimos 2 Años)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 400px;">
                                        <canvas id="salesTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Categories and Department Tables -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Ventas por Categoría</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="categorySalesTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Categoría</th>
                                                    <th>Ventas</th>
                                                    <th>Ganancia</th>
                                                    <th>Margen</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Ventas por Departamento</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="departmentSalesTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Departamento</th>
                                                    <th>Ventas</th>
                                                    <th>Ganancia</th>
                                                    <th>Margen</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Análisis de Métodos de Pago</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="paymentMethodsTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Total Ventas</th>
                                                    <th>Efectivo</th>
                                                    <th>Tarjeta Crédito</th>
                                                    <th>Tarjeta Débito</th>
                                                    <th>Cheque</th>
                                                    <th>ATH Móvil</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Products Section -->
                <section id="products-section" class="dashboard-section d-none">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h2><i class="fas fa-shopping-basket me-2"></i>Análisis de Productos</h2>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshProducts">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-export me-1"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Row -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label for="categoryFilter" class="form-label">Categoría</label>
                                            <select class="form-select" id="categoryFilter">
                                                <option value="">Todas las categorías</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="departmentFilter" class="form-label">Departamento</label>
                                            <select class="form-select" id="departmentFilter">
                                                <option value="">Todos los departamentos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="supplierFilter" class="form-label">Proveedor</label>
                                            <select class="form-select" id="supplierFilter">
                                                <option value="">Todos los proveedores</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button class="btn btn-primary w-100" id="applyProductFilters">
                                                Aplicar Filtros
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Productos Más Vendidos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="topProductsTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Departamento</th>
                                                    <th>Categoría</th>
                                                    <th>Unidades</th>
                                                    <th>Ventas</th>
                                                    <th>Ganancia</th>
                                                    <th>Margen</th>
                                                    <th>Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Performance Chart -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Top 10 Productos por Ventas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="topProductsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Top 10 Productos por Ganancia</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="topProfitChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Inventory Section -->
                <section id="inventory-section" class="dashboard-section d-none">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h2><i class="fas fa-boxes me-2"></i>Gestión de Inventario</h2>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshInventory">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-export me-1"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Value Summary -->
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Valor de Inventario por Departamento</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="inventoryValueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Resumen de Valor de Inventario</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="inventoryValueTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Departamento</th>
                                                    <th>Valor al Costo</th>
                                                    <th>Valor al Precio</th>
                                                    <th>Ganancia Potencial</th>
                                                    <th>% del Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded dynamically -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-active">
                                                    <th>TOTAL</th>
                                                    <th id="totalCostValue">$0.00</th>
                                                    <th id="totalPriceValue">$0.00</th>
                                                    <th id="totalPotentialProfit">$0.00</th>
                                                    <th>100%</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Level Items -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Productos con Bajo Nivel de Inventario</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="lowLevelItemsTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Producto</th>
                                                    <th>Stock Actual</th>
                                                    <th>Nivel Mínimo</th>
                                                    <th>Nivel Máximo</th>
                                                    <th>Precio</th>
                                                    <th>Costo</th>
                                                    <th>Categoría</th>
                                                    <th>Departamento</th>
                                                    <th>Proveedor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Añadir antes del footer en el main content -->
                <section id="clients-section" class="dashboard-section d-none">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h2><i class="fas fa-users me-2"></i>Gestión de Clientes</h2>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshClients">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                                <button type="button" class="btn btn-sm btn-success" id="addClientBtn">
                                    <i class="fas fa-plus me-1"></i> Nuevo Cliente
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Clientes -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label for="clientNameFilter" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="clientNameFilter" placeholder="Buscar por nombre">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="clientCategoryFilter" class="form-label">Categoría</label>
                                            <select class="form-select" id="clientCategoryFilter">
                                                <option value="">Todas las categorías</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="clientCityFilter" class="form-label">Ciudad</label>
                                            <input type="text" class="form-control" id="clientCityFilter" placeholder="Filtrar por ciudad">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12 text-end">
                                            <button class="btn btn-primary" id="applyClientFilters">
                                                <i class="fas fa-search me-1"></i> Buscar
                                            </button>
                                            <button class="btn btn-secondary ms-2" id="resetClientFilters">
                                                <i class="fas fa-undo me-1"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Clientes -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de Clientes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="clientsTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre</th>
                                                    <th>Apellido</th>
                                                    <th>Ciudad</th>
                                                    <th>Teléfono</th>
                                                    <th>Email</th>
                                                    <th>Categoría</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Los datos se cargarán dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para Añadir/Editar Cliente -->
                    <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="clientModalLabel">Añadir Cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="clientForm">
                                        <input type="hidden" id="clientId">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="clientName" class="form-label">Nombre</label>
                                                <input type="text" class="form-control" id="clientName" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clientLastName" class="form-label">Apellido</label>
                                                <input type="text" class="form-control" id="clientLastName">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="clientAddress1" class="form-label">Dirección 1</label>
                                                <input type="text" class="form-control" id="clientAddress1">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="clientAddress2" class="form-label">Dirección 2</label>
                                                <input type="text" class="form-control" id="clientAddress2">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="clientCity" class="form-label">Ciudad</label>
                                                <input type="text" class="form-control" id="clientCity">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="clientZipCode" class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" id="clientZipCode">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="clientCountry" class="form-label">País</label>
                                                <input type="text" class="form-control" id="clientCountry" value="PR">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="clientPhone" class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" id="clientPhone">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clientEmail" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="clientEmail">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="clientCategory" class="form-label">Categoría</label>
                                                <select class="form-select" id="clientCategory">
                                                    <option value="">Seleccionar categoría</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clientActive" class="form-label">Estado</label>
                                                <select class="form-select" id="clientActive">
                                                    <option value="S">Activo</option>
                                                    <option value="N">Inactivo</option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="saveClientBtn">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Añadir después de la sección de clientes -->
                <section id="products-maintenance-section" class="dashboard-section d-none">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h2><i class="fas fa-box me-2"></i>Gestión de Productos</h2>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshProductsMaintenance">
                                    <i class="fas fa-sync-alt me-1"></i> Actualizar
                                </button>
                                <button type="button" class="btn btn-sm btn-success" id="addProductBtn">
                                    <i class="fas fa-plus me-1"></i> Nuevo Producto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Productos -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label for="productCodeFilter" class="form-label">Código</label>
                                            <input type="text" class="form-control" id="productCodeFilter" placeholder="Código de producto">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="productNameFilter" class="form-label">Descripción</label>
                                            <input type="text" class="form-control" id="productNameFilter" placeholder="Nombre o descripción">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="productDepartmentFilter" class="form-label">Departamento</label>
                                            <select class="form-select" id="productDepartmentFilter">
                                                <option value="">Todos los departamentos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="productCategoryFilter" class="form-label">Categoría</label>
                                            <select class="form-select" id="productCategoryFilter">
                                                <option value="">Todas las categorías</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12 text-end">
                                            <button class="btn btn-primary" id="applyProductFilters">
                                                <i class="fas fa-search me-1"></i> Buscar
                                            </button>
                                            <button class="btn btn-secondary ms-2" id="resetProductFilters">
                                                <i class="fas fa-undo me-1"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Productos -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de Productos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="productsMaintenanceTable" class="table table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Descripción</th>
                                                    <th>Código de Barras</th>
                                                    <th>Precio</th>
                                                    <th>Costo</th>
                                                    <th>Stock</th>
                                                    <th>Departamento</th>
                                                    <th>Categoría</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Los datos se cargarán dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para Añadir/Editar Producto -->
                    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="productModalLabel">Añadir Producto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="productForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="productCode" class="form-label">Código</label>
                                                <input type="text" class="form-control" id="productCode" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="productBarcode" class="form-label">Código de Barras</label>
                                                <input type="text" class="form-control" id="productBarcode">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="productDescription" class="form-label">Descripción</label>
                                                <input type="text" class="form-control" id="productDescription" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="productCost" class="form-label">Costo</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" class="form-control" id="productCost" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="productPrice" class="form-label">Precio</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" class="form-control" id="productPrice" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="productDepartment" class="form-label">Departamento</label>
                                                <select class="form-select" id="productDepartment" required>
                                                    <option value="">Seleccionar departamento</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="productCategory" class="form-label">Categoría</label>
                                                <select class="form-select" id="productCategory" required>
                                                    <option value="">Seleccionar categoría</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="productSupplier" class="form-label">Proveedor</label>
                                                <input type="text" class="form-control" id="productSupplier">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="productLocation" class="form-label">Ubicación</label>
                                                <input type="text" class="form-control" id="productLocation">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="productReorderPoint" class="form-label">Punto de Reorden</label>
                                                <input type="number" class="form-control" id="productReorderPoint">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="productReorderQty" class="form-label">Cantidad de Reorden</label>
                                                <input type="number" class="form-control" id="productReorderQty">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="productActive" class="form-label">Estado</label>
                                                <select class="form-select" id="productActive">
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="productIsFood">
                                                    <label class="form-check-label" for="productIsFood">
                                                        Es Alimento
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="productIsWic">
                                                    <label class="form-check-label" for="productIsWic">
                                                        Es WIC
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="productIsTouch">
                                                    <label class="form-check-label" for="productIsTouch">
                                                        Es Touch
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="productIsSerie">
                                                    <label class="form-check-label" for="productIsSerie">
                                                        Es Serie
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="saveProductBtn">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>


                <!-- Footer -->
                <footer class="pt-4 my-md-5 pt-md-5 border-top">
                    <div class="row">
                        <div class="col-12 col-md text-center">
                            <small class="d-block mb-3 text-muted">© SuperDashboard 2025</small>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
    
    <!-- DataTables Extensions JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global variables
        let currentDateFrom = moment().subtract(30, 'days').format('YYYY-MM-DD');
        let currentDateTo = moment().format('YYYY-MM-DD');
        let charts = {};
        let tables = {};

        // Initialize datepickers with current values
        document.getElementById('dateFrom').value = currentDateFrom;
        document.getElementById('dateTo').value = currentDateTo;
        
        // Show/Hide loading overlay
        function toggleLoading(show = true) {
            const loader = document.getElementById('loadingOverlay');
            if (show) {
                loader.style.display = 'flex';
            } else {
                loader.style.display = 'none';
            }
        }
        
        // Format currency
        function formatCurrency(value) {
            return numeral(value).format('$0,0.00');
        }
        
        // Format percentage
        function formatPercentage(value) {
            return numeral(value / 100).format('0.0%');
        }
        
        // Format number with commas
        function formatNumber(value) {
            return numeral(value).format('0,0');
        }

        // Safely convert to locale string
        function safeToLocaleString(value) {
            if (value === null || value === undefined || isNaN(value)) {
                return '0';
            }
            return formatNumber(value);
        }
        
        // Switch between dashboard sections
        function switchSection(sectionId) {
    document.querySelectorAll('.dashboard-section').forEach(section => {
        section.classList.add('d-none');
    });
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.remove('d-none');
    }
    
    const targetLink = document.querySelector(`[data-section="${sectionId}"]`);
    if (targetLink) {
        targetLink.classList.add('active');
    }
}
        
        // Navigation event listeners
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.getAttribute('data-section');
                switchSection(sectionId);
            });
        });
        
        // Apply date filter
        document.getElementById('applyDateFilter').addEventListener('click', function() {
            const newDateFrom = document.getElementById('dateFrom').value;
            const newDateTo = document.getElementById('dateTo').value;
            
            if (newDateFrom && newDateTo) {
                currentDateFrom = newDateFrom;
                currentDateTo = newDateTo;
                
                // Refresh all data
                loadAllData();
            } else {
                alert('Por favor seleccione un rango de fechas válido.');
            }
        });
        
        // Refresh buttons event listeners
        document.getElementById('refreshOverview').addEventListener('click', function() {
            loadOverviewData();
        });
        
        document.getElementById('refreshSales').addEventListener('click', function() {
            loadSalesData();
        });
        
        document.getElementById('refreshProducts').addEventListener('click', function() {
            loadProductsData();
        });
        
        document.getElementById('refreshInventory').addEventListener('click', function() {
            loadInventoryData();
        });
        
        // Apply product filters
        document.getElementById('applyProductFilters').addEventListener('click', function() {
            loadTopProducts();
        });

        // Función mejorada para crear DataTables con estilo mejorado - versión corregida
        function createDataTable(tableId, data, columns, order = [[0, 'desc']]) {
            console.log(`Creando DataTable para '${tableId}' con ${data.length} filas`);
            
            // Verificar que los datos son válidos
            if (!Array.isArray(data)) {
                console.error(`Los datos para la tabla '${tableId}' no son un array válido`);
                return null;
            }
            
            try {
                // Obtener el contenedor padre de la tabla actual
                const tableElement = document.getElementById(tableId);
                if (!tableElement) {
                    console.error(`Tabla con ID '${tableId}' no encontrada en el DOM`);
                    return null;
                }
                
                const parentElement = tableElement.parentNode;
                if (!parentElement) {
                    console.error(`No se pudo encontrar el elemento padre para la tabla '${tableId}'`);
                    return null;
                }
                
                // ENFOQUE RADICAL: Eliminar completamente la tabla existente
                // y crear una nueva desde cero
                console.log(`Eliminando tabla existente '${tableId}'`);
                
                // 1. Crear una nueva tabla con el mismo ID
                const newTable = document.createElement('table');
                newTable.id = tableId;
                newTable.className = 'table table-striped table-hover';
                newTable.style.width = '100%';
                
                // 2. Crear estructura básica de la tabla
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                
                // Añadir encabezados de columna
                columns.forEach(col => {
                    const th = document.createElement('th');
                    th.textContent = col.title;
                    headerRow.appendChild(th);
                });
                
                thead.appendChild(headerRow);
                newTable.appendChild(thead);
                
                // Añadir tbody vacío
                const tbody = document.createElement('tbody');
                newTable.appendChild(tbody);
                
                // 3. Reemplazar la tabla antigua con la nueva
                parentElement.innerHTML = ''; // Limpiar todo el contenido
                parentElement.appendChild(newTable);
                
                console.log(`Nueva tabla '${tableId}' creada y añadida al DOM`);
                
                // 4. Inicializar DataTables con opciones mejoradas
                console.log(`Inicializando DataTable para '${tableId}' con opciones mejoradas`);
                
                // Usar jQuery para seleccionar la nueva tabla
                const $newTable = $(`#${tableId}`);
                
                // Inicializar con opciones mejoradas
                const dataTableInstance = $newTable.DataTable({
                    data: data,
                    columns: columns,
                    order: order,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
                    // Modificar el DOM para tener búsqueda junto a los botones
                    dom: '<"row mb-3"<"col-md-6"B><"col-md-6 d-flex justify-content-end"f>>rt<"row mt-3"<"col-md-5"i><"col-md-7"p>>',
                    buttons: [
                        {
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-sm btn-success me-2'
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                            className: 'btn btn-sm btn-danger me-2',
                            // Configuración para PDF con vista previa
                            title: function() {
                                return 'Reporte - ' + tableId;
                            },
                            // Orientation fija a landscape para tablas con muchas columnas
                            orientation: 'landscape',
                            // Usar exportOptions para controlar qué datos se exportan
                            exportOptions: {
                                columns: ':visible',
                                format: {
                                    header: function (data, columnIdx) {
                                        return columns[columnIdx].title || data;
                                    },
                                    body: function (data, row, column, node) {
                                        // Asegurar que los datos sean strings
                                        return data !== null ? String(data) : '';
                                    }
                                }
                            },
                            customize: function(doc) {
                                // Determinar cantidad de columnas
                                let colCount = 0;
                                if (doc.content[1] && doc.content[1].table && doc.content[1].table.body && doc.content[1].table.body[0]) {
                                    colCount = doc.content[1].table.body[0].length;
                                }
                                
                                // Ajustar orientación según número de columnas
                                const hasManyColumns = colCount > 5;
                                if (!hasManyColumns) {
                                    doc.pageOrientation = 'portrait';
                                }
                                
                                // Personalizar PDF - tamaño de fuente adaptativo
                                doc.defaultStyle.fontSize = hasManyColumns ? 8 : 10;
                                doc.styles.tableHeader.fontSize = hasManyColumns ? 9 : 11;
                                doc.styles.tableHeader.fillColor = '#4CAF50';
                                doc.styles.tableHeader.color = '#FFFFFF';
                                
                                // Configurar alineación para los encabezados
                                doc.styles.tableHeader.alignment = 'center';
                                
                                // Ajustar márgenes según orientación
                                doc.pageMargins = hasManyColumns ? [10, 20, 10, 20] : [20, 25, 20, 25];
                                
                                // Definir anchos específicos para cada columna
                                if (doc.content[1] && doc.content[1].table) {
                                    // Crear un array de anchos
                                    let columnWidths = [];
                                    
                                    // Si el número de columnas es conocido, asignar anchos específicos
                                    if (colCount > 0) {
                                        for (let i = 0; i < colCount; i++) {
                                            // Calcular el ancho basado en el tipo de columna
                                            const columnTitle = (columns[i]?.title || '').toLowerCase();
                                            
                                            if (hasManyColumns) {
                                                // Para muchas columnas, optimizar el espacio
                                                if (columnTitle.includes('producto') || columnTitle.includes('descripción')) {
                                                    columnWidths.push('auto');
                                                } else if (columnTitle.includes('código')) {
                                                    columnWidths.push('10%');
                                                } else if (columnTitle.includes('venta') || columnTitle.includes('ganancia') || 
                                                          columnTitle.includes('precio') || columnTitle.includes('costo')) {
                                                    columnWidths.push('12%');
                                                } else if (columnTitle.includes('unidades') || columnTitle.includes('stock') || 
                                                          columnTitle.includes('cantidad')) {
                                                    columnWidths.push('8%');
                                                } else {
                                                    columnWidths.push('*');
                                                }
                                            } else {
                                                // Para pocas columnas, distribución más equitativa
                                                if (columnTitle.includes('producto') || columnTitle.includes('descripción')) {
                                                    columnWidths.push('30%'); // Dar más espacio para textos
                                                } else if (columnTitle.includes('código')) {
                                                    columnWidths.push('15%');
                                                } else {
                                                    columnWidths.push('*');
                                                }
                                            }
                                        }
                                        
                                        doc.content[1].table.widths = columnWidths;
                                    } else {
                                        // Si no podemos determinar el número exacto, usar auto para todos
                                        doc.content[1].table.widths = Array(colCount).fill('auto');
                                    }
                                }
                                
                                // Ajustar el estilo de las celdas para que el texto se ajuste
                                doc.styles.tableBodyEven.fontSize = doc.defaultStyle.fontSize;
                                doc.styles.tableBodyOdd.fontSize = doc.defaultStyle.fontSize;
                                
                                // Alineación personalizada para cada columna
                                if (doc.content[1] && doc.content[1].table && doc.content[1].table.body) {
                                    // Alineación de encabezados
                                    if (doc.content[1].table.body[0]) {
                                        for (let i = 0; i < doc.content[1].table.body[0].length; i++) {
                                            // Asegurarse de que el encabezado exista
                                            if (doc.content[1].table.body[0][i]) {
                                                // Centrar todos los encabezados
                                                doc.content[1].table.body[0][i].alignment = 'center';
                                                
                                                // Aplicar estilo a celdas para evitar que se corten
                                                doc.content[1].table.body[0][i].noWrap = false;
                                            }
                                        }
                                    }
                                    
                                    // Alineación de contenido
                                    for (let row = 1; row < doc.content[1].table.body.length; row++) {
                                        for (let col = 0; col < doc.content[1].table.body[row].length; col++) {
                                            const cell = doc.content[1].table.body[row][col];
                                            
                                            // Asegurarse de que la celda exista
                                            if (cell) {
                                                // Permitir ajuste de texto
                                                cell.noWrap = false;
                                                
                                                // Determinar tipo de columna para alineación
                                                const columnTitle = (columns[col]?.title || '').toLowerCase();
                                                
                                                if (columnTitle.includes('código')) {
                                                    // Códigos centrados
                                                    cell.alignment = 'center';
                                                } else if (columnTitle.includes('venta') || 
                                                         columnTitle.includes('ganancia') || 
                                                         columnTitle.includes('precio') || 
                                                         columnTitle.includes('costo') ||
                                                         columnTitle.includes('unidades') ||
                                                         columnTitle.includes('stock') ||
                                                         columnTitle.includes('cantidad')) {
                                                    // Valores numéricos a la derecha
                                                    cell.alignment = 'right';
                                                } else {
                                                    // Texto normal a la izquierda
                                                    cell.alignment = 'left';
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                // Mejorar la definición de la tabla
                                if (doc.content[1] && doc.content[1].table) {
                                    // Añadir bordes a la tabla
                                    doc.content[1].layout = {
                                        hLineWidth: function(i, node) { return 0.5; },
                                        vLineWidth: function(i, node) { return 0.5; },
                                        hLineColor: function(i, node) { return '#aaa'; },
                                        vLineColor: function(i, node) { return '#aaa'; },
                                        paddingLeft: function(i, node) { return 4; },
                                        paddingRight: function(i, node) { return 4; },
                                        paddingTop: function(i, node) { return 3; },
                                        paddingBottom: function(i, node) { return 3; }
                                    };
                                }
                                
                                // Log para depuración
                                console.log('PDF generado con ' + colCount + ' columnas en orientación ' + doc.pageOrientation);
                            },
                            // Usar el método de abrir en una nueva ventana directamente
                            action: function(e, dt, button, config) {
                                // Prevenir comportamiento por defecto
                                e.preventDefault();
                                
                                // Usar el método nativo de pdfMake para abrir en ventana
                                $.fn.dataTable.ext.buttons.pdfHtml5.action.call(
                                    this, 
                                    e, 
                                    dt, 
                                    button, 
                                    $.extend(true, {}, config, {
                                        download: 'open' // Esto hará que se abra en una nueva ventana
                                    })
                                );
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-1"></i> Imprimir',
                            className: 'btn btn-sm btn-info me-2'
                        },
                        // Añadir botón para mostrar/ocultar columnas
                        {
                            extend: 'colvis',
                            text: '<i class="fas fa-columns me-1"></i> Columnas',
                            className: 'btn btn-sm btn-primary',
                            postfixButtons: ['colvisRestore'],
                            columns: ':not(:first-child)' // Opcional: evitar ocultar la primera columna
                        }
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Buscar...",
                        lengthMenu: "Mostrar _MENU_ registros",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        infoEmpty: "Mostrando 0 a 0 de 0 registros",
                        infoFiltered: "(filtrado de _MAX_ registros totales)",
                        zeroRecords: "No se encontraron registros coincidentes",
                        emptyTable: "No hay datos disponibles en la tabla",
                        paginate: {
                            first: '<i class="fas fa-angle-double-left"></i>',
                            previous: '<i class="fas fa-angle-left"></i>',
                            next: '<i class="fas fa-angle-right"></i>',
                            last: '<i class="fas fa-angle-double-right"></i>'
                        },
                        // Añadir traducciones para colvis (mostrar/ocultar columnas)
                        buttons: {
                            colvis: "Mostrar/Ocultar",
                            colvisRestore: "Restaurar columnas"
                        }
                    },
                    pagingType: "full_numbers",
                    // Estilo personalizado para componentes
                    initComplete: function() {
                        // Agregar clases adicionales a los elementos de DataTables
                        
                        // Mejorar la caja de búsqueda
                        $('.dataTables_filter input').addClass('form-control form-control-sm');
                        $('.dataTables_filter input').css({
                            'min-width': '250px',
                            'display': 'inline-block'
                        });
                        
                        // Mejorar el selector de registros por página
                        $('.dataTables_length select').addClass('form-select form-select-sm');
                        $('.dataTables_length select').css({
                            'padding-right': '25px',
                            'background-position': 'right 0.5rem center'
                        });
                        
                        // Mejorar la paginación
                        $('.dataTables_paginate').addClass('pagination-container');
                        $('.dataTables_paginate .paginate_button').addClass('btn btn-sm');
                        
                        // Estilizar los botones de paginación
                        $('.dataTables_paginate .paginate_button:not(.disabled)').css({
                            'border-radius': '4px',
                            'margin': '0 2px',
                            'border': '1px solid #dee2e6',
                            'background-color': '#fff',
                            'cursor': 'pointer',
                            'color': '#0d6efd',
                            'padding': '0.25rem 0.5rem',
                            'font-size': '0.875rem'
                        });
                        
                        // Estilo para el botón de página actual
                        $('.dataTables_paginate .paginate_button.current').css({
                            'background-color': '#0d6efd',
                            'color': '#fff',
                            'border-color': '#0d6efd',
                            'font-weight': 'bold'
                        });
                        
                        // Estilo para botones deshabilitados
                        $('.dataTables_paginate .paginate_button.disabled').css({
                            'color': '#6c757d',
                            'cursor': 'not-allowed',
                            'background-color': '#fff',
                            'border-color': '#dee2e6'
                        });
                        
                        // Efectos hover para botones de paginación
                        $('.dataTables_paginate .paginate_button:not(.current):not(.disabled)').hover(
                            function() {
                                $(this).css({
                                    'background-color': '#f8f9fa',
                                    'color': '#0a58ca'
                                });
                            },
                            function() {
                                $(this).css({
                                    'background-color': '#fff',
                                    'color': '#0d6efd'
                                });
                            }
                        );
                        
                        // Alinear información y paginación
                        $('.dataTables_info').css({
                            'padding-top': '0.5rem',
                            'margin-bottom': '0'
                        });
                        
                        // Ajustar el container de la información
                        $('.dataTables_info').parent().addClass('d-flex align-items-center');
                        
                        // Ajustar espaciado general
                        $('.dataTables_wrapper').css({
                            'padding': '0',
                            'margin-bottom': '1rem'
                        });
                        
                        // Mejorar la apariencia de los botones de acción
                        $('.dt-buttons .btn').css({
                            'box-shadow': 'none'
                        });
                        
                        // Aplicar estilos a la tabla en sí
                        $(this.api().table().node()).addClass('table-bordered');
                        
                        // Mejorar los encabezados de las columnas
                        $(this.api().table().header()).css({
                            'background-color': '#f8f9fa',
                            'font-weight': 'bold'
                        });
                        
                        // Ajustar los botones de columnas visibles
                        $('.buttons-colvis').css({
                            'position': 'relative'
                        });
                        
                        // Añadir animaciones a las filas cuando se filtran
                        this.api().on('draw', function() {
                            $('.dataTable tbody tr').css({
                                'transition': 'background-color 0.3s'
                            });
                        });
                        
                        // Mejorar interacción al hover en filas
                        $('.dataTable tbody tr').hover(
                            function() {
                                $(this).css({
                                    'background-color': 'rgba(13, 110, 253, 0.05)'
                                });
                            },
                            function() {
                                $(this).css({
                                    'background-color': ''
                                });
                            }
                        );
                        
                        // Ajustar la apariencia del menú de columnas visibles
                        $('.dt-button-collection').css({
                            'border-radius': '4px',
                            'border': '1px solid rgba(0,0,0,.15)',
                            'padding': '0.5rem 0',
                            'box-shadow': '0 .5rem 1rem rgba(0,0,0,.175)',
                            'background-color': '#fff'
                        });
                        
                        // Mejorar los botones del menú de columnas
                        $('.dt-button-collection .dt-button').css({
                            'display': 'block',
                            'padding': '0.25rem 1.5rem',
                            'clear': 'both',
                            'font-weight': '400',
                            'color': '#212529',
                            'text-align': 'inherit',
                            'white-space': 'nowrap',
                            'background-color': 'transparent',
                            'border': '0'
                        });
                        
                        // Hover para los botones del menú de columnas
                        $('.dt-button-collection .dt-button').hover(
                            function() {
                                $(this).css({
                                    'color': '#16181b',
                                    'background-color': '#f8f9fa'
                                });
                            },
                            function() {
                                $(this).css({
                                    'color': '#212529',
                                    'background-color': 'transparent'
                                });
                            }
                        );
                        
                        // Mejorar el aspecto del botón de restaurar columnas
                        $('.dt-button-collection .buttons-colvisRestore').css({
                            'font-weight': 'bold',
                            'border-top': '1px solid #e9ecef',
                            'margin-top': '0.25rem'
                        });
                    }
                });
                
                console.log(`DataTable para '${tableId}' inicializada correctamente con estilos mejorados`);
                return dataTableInstance;
                
            } catch (error) {
                console.error(`Error al crear DataTable para '${tableId}':`, error);
                
                // Como último recurso, mostrar los datos en formato HTML básico
                try {
                    const tableElement = document.getElementById(tableId);
                    if (tableElement) {
                        // Limpiar la tabla
                        tableElement.innerHTML = '';
                        
                        // Crear encabezados
                        const thead = document.createElement('thead');
                        const headerRow = document.createElement('tr');
                        
                        columns.forEach(col => {
                            const th = document.createElement('th');
                            th.textContent = col.title;
                            headerRow.appendChild(th);
                        });
                        
                        thead.appendChild(headerRow);
                        tableElement.appendChild(thead);
                        
                        // Crear cuerpo de la tabla
                        const tbody = document.createElement('tbody');
                        
                        data.forEach(rowData => {
                            const row = document.createElement('tr');
                            
                            rowData.forEach(cellData => {
                                const cell = document.createElement('td');
                                cell.innerHTML = cellData;
                                row.appendChild(cell);
                            });
                            
                            tbody.appendChild(row);
                        });
                        
                        tableElement.appendChild(tbody);
                        
                        console.log(`Datos mostrados en formato HTML básico para '${tableId}'`);
                    }
                } catch (fallbackError) {
                    console.error(`Error al mostrar datos básicos para '${tableId}':`, fallbackError);
                }
                
                return null;
            }
        }
        
        // Load company information
        async function loadCompanyInfo() {
            try {
                const response = await fetch('api_proxy.php?endpoint=InfoCompany');
                const data = await response.json();
                
                if (data && data.Name) {
                    document.getElementById('companyName').textContent = data.Name;
                }
            } catch (error) {
                console.error('Error loading company info:', error);
            }
        }
        
        // Load sales totals
        async function loadSalesTotals() {
            try {
                toggleLoading(true);
                
                const response = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
                const data = await response.json();
                
                if (data && data[0]) {
                    const salesData = data[0];
                    // Update KPI cards
                    document.getElementById('totalSales').textContent = formatCurrency(salesData.TotalSales);
                    document.getElementById('transactionCount').textContent = formatNumber(salesData.TransactionCount);
                    document.getElementById('avgTicket').textContent = formatCurrency(salesData.AverageTicketAmount);
                    
                    // Calculate profit
                    const totalProfit = salesData.TotalSales - salesData.TotalCost;
                    document.getElementById('totalProfit').textContent = formatCurrency(totalProfit);
                    
                    // Calculate and display profit margin
                    const profitMargin = (salesData.TotalSales > 0) ? ((totalProfit / salesData.TotalSales) * 100) : 0;
                    document.getElementById('profitMargin').textContent = numeral(profitMargin / 100).format('0.0%');
                    
                    // Compare with previous period to show trend
                    // This would typically require another API call to get previous period data
                    // For demo purposes, we'll use a random value between -10% and +20%
                    const randomTrend = (Math.random() * 30) - 10;
                    const trendElement = document.getElementById('salesTrend');
                    
                    if (randomTrend >= 0) {
                        trendElement.innerHTML = `<i class="fas fa-long-arrow-alt-up"></i> ${randomTrend.toFixed(1)}%`;
                        trendElement.classList.remove('trend-down');
                        trendElement.classList.add('trend-up');
                    } else {
                        trendElement.innerHTML = `<i class="fas fa-long-arrow-alt-down"></i> ${Math.abs(randomTrend).toFixed(1)}%`;
                        trendElement.classList.remove('trend-up');
                        trendElement.classList.add('trend-down');
                    }
                }
            } catch (error) {
                console.error('Error loading sales totals:', error);
            } finally {
                toggleLoading(false);
            }
        }

        // Load monthly sales trend
        /**
         * Función corregida para cargar la tendencia de ventas mensuales
         * Esta función asegura que las ganancias se calculen correctamente
         */
        async function loadMonthlySalesTrend() {
            try {
                // Solicitar datos de los últimos 2 años para asegurar que tenemos suficientes datos
                const twoYearsAgo = moment().subtract(2, 'years').format('YYYY-MM-DD');
                const today = moment().format('YYYY-MM-DD');
                
                console.log(`Solicitando datos de ventas desde ${twoYearsAgo} hasta ${today}`);
                const response = await fetch(`api_proxy.php?endpoint=SaleTrendByMonth&DateFrom=${twoYearsAgo}&DateTo=${today}`);
                
                // Comprobar la respuesta HTTP
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Datos de tendencia recibidos:', data);
                
                if (data && data.length > 0) {
                    // Ordenar los datos por fecha para asegurar que están en orden cronológico
                    const formattedData = data
                        .map(item => {
                            // Asegurarse de que Year y Month son números
                            const year = parseInt(item.Year) || new Date().getFullYear();
                            const month = parseInt(item.Month) || 1;
                            
                            // Crear una fecha válida para ordenar
                            const date = moment(`${year}-${month.toString().padStart(2, '0')}-01`);
                            const monthName = date.format('MMM YYYY');
                            
                            // CORRECCIÓN: Asegurarse de que TotalCost existe y es un número
                            const totalSales = parseFloat(item.TotalSales) || 0;
                            const totalCost = parseFloat(item.TotalCost) || 0;
                            
                            // CORRECCIÓN: Calcular la ganancia real restando el costo de las ventas
                            const totalProfit = totalSales - totalCost;
                            
                            // Depuración para ver los valores
                            console.log(`Mes: ${monthName}, Ventas: ${totalSales}, Costo: ${totalCost}, Ganancia: ${totalProfit}`);
                            
                            return {
                                date: date,
                                monthYear: monthName,
                                TotalSales: totalSales,
                                TotalCost: totalCost,
                                TotalProfit: totalProfit // Ganancia real
                            };
                        })
                        .sort((a, b) => a.date.valueOf() - b.date.valueOf()); // Ordenar por fecha
                    
                    console.log('Datos formateados:', formattedData);
                    
                    // Extraer los datos para la gráfica
                    const labels = formattedData.map(item => item.monthYear);
                    const salesData = formattedData.map(item => item.TotalSales);
                    const profitData = formattedData.map(item => item.TotalProfit); // Usar la ganancia correcta
                    
                    // Verificar que los datos de ventas y ganancia son diferentes
                    const dataIsDifferent = salesData.some((value, index) => value !== profitData[index]);
                    if (!dataIsDifferent) {
                        console.warn('ADVERTENCIA: Los datos de ventas y ganancia son idénticos. Es posible que los costos no estén siendo reportados correctamente por la API.');
                    }
                    
                    // Destroy existing chart if it exists
                    if (charts.monthlySalesChart) {
                        charts.monthlySalesChart.destroy();
                    }
                    
                    // Create the chart
                    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
                    charts.monthlySalesChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Ventas',
                                    data: salesData,
                                    borderColor: '#0057b8',
                                    backgroundColor: 'rgba(0, 87, 184, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4
                                },
                                {
                                    label: 'Ganancia',
                                    data: profitData,
                                    borderColor: '#00a651',
                                    backgroundColor: 'rgba(0, 166, 81, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += formatCurrency(context.parsed.y);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return formatCurrency(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // También actualizar el gráfico de tendencia de ventas en la sección de ventas
                    if (charts.salesTrendChart) {
                        charts.salesTrendChart.destroy();
                    }
                    
                    const ctxTrend = document.getElementById('salesTrendChart').getContext('2d');
                    charts.salesTrendChart = new Chart(ctxTrend, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Ventas',
                                    data: salesData,
                                    borderColor: '#0057b8',
                                    backgroundColor: 'rgba(0, 87, 184, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4
                                },
                                {
                                    label: 'Ganancia',
                                    data: profitData, // Usar la ganancia correcta
                                    borderColor: '#00a651',
                                    backgroundColor: 'rgba(0, 166, 81, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += formatCurrency(context.parsed.y);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return formatCurrency(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.warn('No se recibieron datos para la tendencia de ventas mensuales');
                    // Mostrar mensaje de error en los contenedores de gráficos
                    ['monthlySalesChart', 'salesTrendChart'].forEach(chartId => {
                        const chartElement = document.getElementById(chartId);
                        if (chartElement && chartElement.parentNode) {
                            chartElement.parentNode.innerHTML = '<div class="text-center p-5 text-muted">No hay datos de ventas disponibles para mostrar la tendencia</div>';
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading monthly sales trend:', error);
                // Mostrar mensaje de error en los contenedores de gráficos
                ['monthlySalesChart', 'salesTrendChart'].forEach(chartId => {
                    const chartElement = document.getElementById(chartId);
                    if (chartElement && chartElement.parentNode) {
                        chartElement.parentNode.innerHTML = `<div class="text-center p-5 text-danger">Error al cargar datos de tendencia: ${error.message}</div>`;
                    }
                });
            }
        }

        /**
         * Función auxiliar para obtener y depurar la estructura de los datos de la API
         * Puedes usar esta función para revisar qué campos están disponibles en los datos
         */
        function debugSalesTrendData() {
            // Solicitar datos de los últimos 2 años
            const twoYearsAgo = moment().subtract(2, 'years').format('YYYY-MM-DD');
            const today = moment().format('YYYY-MM-DD');
            
            fetch(`api_proxy.php?endpoint=SaleTrendByMonth&DateFrom=${twoYearsAgo}&DateTo=${today}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        // Verificar la estructura del primer elemento
                        const firstItem = data[0];
                        console.log('Estructura de datos de ventas mensuales:', firstItem);
                        
                        // Ver qué campos están disponibles
                        console.log('Campos disponibles:', Object.keys(firstItem));
                        
                        // Verificar específicamente los campos de ventas y costos
                        console.log('TotalSales:', firstItem.TotalSales);
                        console.log('TotalCost:', firstItem.TotalCost);
                        
                        // Verificar si hay otros campos que puedan contener información de costos
                        for (const key in firstItem) {
                            if (key.toLowerCase().includes('cost') || key.toLowerCase().includes('costo')) {
                                console.log(`Campo de costo encontrado - ${key}:`, firstItem[key]);
                            }
                        }
                        
                        // Verificar si en todos los elementos los valores de ventas y costos son iguales
                        const allEqual = data.every(item => 
                            parseFloat(item.TotalSales) === parseFloat(item.TotalCost || 0)
                        );
                        
                        console.log('¿Todos los valores de ventas y costos son iguales?', allEqual);
                        
                        // Si todos son iguales, puede ser un problema con la API
                        if (allEqual) {
                            console.warn('ADVERTENCIA: La API parece no estar devolviendo valores de costo correctos');
                        }
                    }
                })
                .catch(error => console.error('Error al depurar datos:', error));
        }

        // Load all data
        async function loadAllData() {
            toggleLoading(true);
            
            try {
                // Load overview section data
                await loadCompanyInfo();
                await loadSalesTotals();
                await loadMonthlySalesTrend();
                await loadSalesByDepartment();
                await loadSalesByHour();
                await loadSalesByMethod();
                
                // Load sales section data
                await loadSalesByCategory();
                
                // Load products section data
                await loadTopProducts();
                
                // Load inventory section data
                await loadInventoryValue();
                await loadLowLevelItems();
            } catch (error) {
                console.error('Error loading all data:', error);
            } finally {
                toggleLoading(false);
            }
        }
        
        // Load overview data only
        async function loadOverviewData() {
            toggleLoading(true);
            
            try {
                await loadSalesTotals();
                await loadMonthlySalesTrend();
                await loadSalesByDepartment();
                await loadSalesByHour();
                await loadSalesByMethod();
            } catch (error) {
                console.error('Error loading overview data:', error);
            } finally {
                toggleLoading(false);
            }
        }
        
        // Load sales data only
        async function loadSalesData() {
            toggleLoading(true);
            
            try {
                await loadMonthlySalesTrend();
                await loadSalesByDepartment();
                await loadSalesByCategory();
                await loadSalesByMethod();
            } catch (error) {
                console.error('Error loading sales data:', error);
            } finally {
                toggleLoading(false);
            }
        }
        
        // Load products data only
        async function loadProductsData() {
            toggleLoading(true);
            
            try {
                await loadTopProducts();
            } catch (error) {
                console.error('Error loading products data:', error);
            } finally {
                toggleLoading(false);
            }
        }
        
        // Load inventory data only
        async function loadInventoryData() {
            toggleLoading(true);
            
            try {
                await loadInventoryValue();
                await loadLowLevelItems();
            } catch (error) {
                console.error('Error loading inventory data:', error);
            } finally {
                toggleLoading(false);
            }
        }
        
        // Initialize the dashboard - ¡ESTA FUNCIÓN SE MODIFICÓ!
        async function initDashboard() {
            try {
                // Cargar todos los datos
                await loadAllData();
            } catch (error) {
                console.error('Error initializing dashboard:', error);
            } finally {
                toggleLoading(false);
            }
        }
        
        // Initialize the dashboard when the page loads
        document.addEventListener('DOMContentLoaded', initDashboard);
        // Load sales by department
        async function loadSalesByDepartment() {
            try {
                const response = await fetch(`api_proxy.php?endpoint=SalesByDepartment&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
                const data = await response.json();
                
                if (data && data.length > 0) {
                    // Prepare data for the chart
                    const labels = data.map(item => item.Department || 'Sin Departamento');
                    const salesData = data.map(item => item.TotalSales || 0);
                    
                    // Destroy existing chart if it exists
                    if (charts.departmentSalesChart) {
                        charts.departmentSalesChart.destroy();
                    }
                    
                    // Create the chart
                    const ctx = document.getElementById('departmentSalesChart').getContext('2d');
                    charts.departmentSalesChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: salesData,
                                backgroundColor: [
                                    '#0057b8',
                                    '#00a651',
                                    '#ffc107',
                                    '#dc3545',
                                    '#6f42c1',
                                    '#fd7e14',
                                    '#20c997',
                                    '#6c757d'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 15
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = Math.round((context.parsed * 100) / total);
                                            return `${context.label}: ${formatCurrency(context.parsed)} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Update the department sales table
                    const tableData = data.map(item => [
                        item.Department || 'Sin Departamento',
                        formatCurrency(item.TotalSales || 0),
                        formatCurrency((item.TotalSales || 0) - (item.TotalCost || 0)),
                        `${(((item.TotalSales || 0) - (item.TotalCost || 0)) / (item.TotalSales || 1) * 100).toFixed(1)}%`
                    ]);
                    
                    const departmentColumns = [
                        { title: "Departamento", data: 0 },
                        { title: "Ventas", data: 1 },
                        { title: "Ganancia", data: 2 },
                        { title: "Margen", data: 3 }
                    ];
                    tables.departmentSalesTable = createDataTable('departmentSalesTable', tableData, departmentColumns, [[1, 'desc']]);
                }
            } catch (error) {
                console.error('Error loading sales by department:', error);
            }
        }

        // Load sales by hour
        async function loadSalesByHour() {
            try {
                console.log('Solicitando datos de ventas por hora...');
                const response = await fetch(`api_proxy.php?endpoint=SalesByHour&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
                const data = await response.json();
                
                console.log('Datos de ventas por hora recibidos:', data);
                
                if (data && data.length > 0) {
                    // Preparar arrays para todas las horas (0-23)
                    const hours = [...Array(24).keys()].map(hour => `${hour}:00`);
                    const salesByHour = new Array(24).fill(0);
                    const transactionsByHour = new Array(24).fill(0);
                    
                    // Procesar los datos recibidos
                    data.forEach(item => {
                        // Verificar si el campo es HourOfDay o Hour
                        const hourField = item.hasOwnProperty('HourOfDay') ? 'HourOfDay' : 'Hour';
                        const hour = parseInt(item[hourField]);
                        
                        console.log(`Procesando hora ${hour}: ${item.TransactionCount} transacciones, $${item.TotalSales} en ventas`);
                        
                        if (!isNaN(hour) && hour >= 0 && hour < 24) {
                            salesByHour[hour] = parseFloat(item.TotalSales) || 0;
                            transactionsByHour[hour] = parseInt(item.TransactionCount) || 0;
                        }
                    });
                    
                    console.log('Datos procesados para la gráfica:');
                    console.log('Ventas por hora:', salesByHour);
                    console.log('Transacciones por hora:', transactionsByHour);
                    
                    // Destroy existing chart if it exists
                    if (charts.hourlyChart) {
                        charts.hourlyChart.destroy();
                    }
                    
                    // Create the chart
                    const ctx = document.getElementById('hourlyChart').getContext('2d');
                    charts.hourlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: hours,
                            datasets: [
                                {
                                    label: 'Ventas',
                                    data: salesByHour,
                                    backgroundColor: 'rgba(0, 87, 184, 0.7)',
                                    borderColor: '#0057b8',
                                    borderWidth: 1,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Transacciones',
                                    data: transactionsByHour,
                                    type: 'line',
                                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                                    borderColor: '#ffc107',
                                    borderWidth: 2,
                                    yAxisID: 'y1',
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return formatCurrency(value);
                                        }
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    beginAtZero: true,
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                if (context.dataset.label === 'Ventas') {
                                                    label += formatCurrency(context.parsed.y);
                                                } else {
                                                    label += formatNumber(context.parsed.y);
                                                }
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    console.warn('No se recibieron datos para ventas por hora');
                    // Mostrar mensaje de error en el contenedor del gráfico
                    const chartElement = document.getElementById('hourlyChart');
                    if (chartElement && chartElement.parentNode) {
                        chartElement.parentNode.innerHTML = '<div class="text-center p-5 text-muted">No hay datos de ventas por hora disponibles</div>';
                    }
                }
            } catch (error) {
                console.error('Error loading sales by hour:', error);
                // Mostrar mensaje de error en el contenedor del gráfico
                const chartElement = document.getElementById('hourlyChart');
                if (chartElement && chartElement.parentNode) {
                    chartElement.parentNode.innerHTML = `<div class="text-center p-5 text-danger">Error al cargar datos de ventas por hora: ${error.message}</div>`;
                }
            }
        }

        // Load sales by payment method
        async function loadSalesByMethod() {
            try {
                const response = await fetch(`api_proxy.php?endpoint=SalesByMethod&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
                const data = await response.json();
                
                if (data && data.length > 0) {
                    console.log('Payment method data:', data); // Debug log
                    
                    // Prepare data for the chart with CORRECT field names from API
                    let methods = [
                        { name: 'Efectivo', key: 'CashPayments', value: 0, color: '#4CAF50' },
                        { name: 'Tarjeta Crédito', key: 'CreditCardPayments', value: 0, color: '#2196F3' },
                        { name: 'Tarjeta Débito', key: 'DebitCardPayments', value: 0, color: '#FF9800' },
                        { name: 'Cheque', key: 'CheckPayments', value: 0, color: '#9C27B0' },
                        { name: 'ATH Móvil', key: 'AthMovilPayments', value: 0, color: '#F44336' }
                    ];

                    // Aggregate the data
                    data.forEach(item => {
                        methods.forEach(method => {
                            const value = parseFloat(item[method.key] || 0);
                            method.value += value;
                        });
                    });

                    // Filter out zero values to avoid empty segments in the chart
                    methods = methods.filter(method => method.value > 0);
                    
                    console.log('Processed payment methods:', methods); // Debug log

                    // Prepare arrays for chart
                    const labels = methods.map(m => m.name);
                    const paymentData = methods.map(m => m.value);
                    const colors = methods.map(m => m.color);

                    const total = paymentData.reduce((a, b) => a + b, 0);
                    const percentages = paymentData.map(value => ((value / total) * 100).toFixed(1));
                    
                    // Destroy existing chart if it exists
                    if (charts.paymentMethodsChart) {
                        charts.paymentMethodsChart.destroy();
                    }
                    
                    // Create the chart
                    const ctx = document.getElementById('paymentMethodsChart').getContext('2d');
                    charts.paymentMethodsChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: paymentData,
                                backgroundColor: colors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 15
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const index = context.dataIndex;
                                            return `${context.label}: ${formatCurrency(context.parsed)} (${percentages[index]}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // Update payment methods table
                    const tableData = data.map(item => [
                        moment(item.SaleDate).format('DD/MM/YYYY'),
                        formatCurrency(item.TotalSales || 0),
                        formatCurrency(item.CashPayments || 0),
                        formatCurrency(item.CreditCardPayments || 0),
                        formatCurrency(item.DebitCardPayments || 0),
                        formatCurrency(item.CheckPayments || 0),
                        formatCurrency(item.AthMovilPayments || 0)
                    ]);
                    
                    const paymentMethodColumns = [
                        { title: "Fecha", data: 0 },
                        { title: "Total Ventas", data: 1 },
                        { title: "Efectivo", data: 2 },
                        { title: "Tarjeta Crédito", data: 3 },
                        { title: "Tarjeta Débito", data: 4 },
                        { title: "Cheque", data: 5 },
                        { title: "ATH Móvil", data: 6 }
                    ];
                    tables.paymentMethodsTable = createDataTable('paymentMethodsTable', tableData, paymentMethodColumns, [[0, 'desc']]);
                }
            } catch (error) {
                console.error('Error loading sales by payment method:', error);
            }
        }

        // Load inventory value
        // Load inventory value
        async function loadInventoryValue() {
            try {
                const response = await fetch(`api_proxy.php?endpoint=InventoryValue`);
                const data = await response.json();
                
                if (data && data.length > 0) {
                    console.log('Raw inventory data:', data); // Debug log
                    
                    // Calculate total inventory value
                    let totalCostValue = 0;
                    let totalPriceValue = 0;
                    
                    // Extract data for chart and table
                    const departmentNames = [];
                    const departmentValues = [];
                    const tableData = [];
                    
                    // Process inventory data
                    data.forEach(item => {
                        // Skip "GRAND TOTAL" entries for the chart (case insensitive check)
                        if (item.Department && !item.Department.toUpperCase().includes('GRAND TOTAL')) {
                            departmentNames.push(item.Department);
                            // Use absolute value to handle negative values
                            const value = Math.abs(parseFloat(item.TotalInventoryValue) || 0);
                            departmentValues.push(value);
                        }
                        
                        // Include all entries for the table, including Grand Total
                        if (item.Department) {
                            // Use absolute value for cost
                            const costValue = Math.abs(parseFloat(item.TotalInventoryValue) || 0);
                            const priceValue = costValue * 1.3; // Estimado si no hay valor de precio
                            const potentialProfit = priceValue - costValue;
                            
                            // Only add to totals if not Grand Total (case insensitive check)
                            if (!item.Department.toUpperCase().includes('GRAND TOTAL')) {
                                totalCostValue += costValue;
                                totalPriceValue += priceValue;
                            }
                            
                            tableData.push([
                                item.Department,
                                formatCurrency(costValue),
                                formatCurrency(priceValue),
                                formatCurrency(potentialProfit),
                                '0%' // Will calculate after getting total
                            ]);
                        }
                    });
                    
                    // Calculate percentages of total
                    tableData.forEach(row => {
                        // Skip percentage calculation for Grand Total (case insensitive check)
                        if (!row[0].toUpperCase().includes('GRAND TOTAL')) {
                            const costValue = parseFloat(row[1].replace(/[^0-9.-]+/g, ''));
                            const percentage = ((costValue / totalCostValue) * 100).toFixed(1);
                            row[4] = `${percentage}%`;
                        } else {
                            row[4] = '100%';
                        }
                    });
                    
                    // Update inventory value chart - verificar si el elemento existe
                    const chartElement = document.getElementById('inventoryValueChart');
                    if (chartElement) {
                        if (charts.inventoryValueChart) {
                            charts.inventoryValueChart.destroy();
                        }
                        
                        // Colors for the chart
                        const colors = [
                            '#4CAF50', // Verde
                            '#2196F3', // Azul
                            '#FF9800', // Naranja
                            '#9C27B0', // Púrpura
                            '#F44336', // Rojo
                            '#00BCD4', // Cian
                            '#795548', // Marrón
                            '#607D8B'  // Gris azulado
                        ];
                        
                        // Only create chart if we have data
                        if (departmentNames.length > 0 && departmentValues.some(v => v > 0)) {
                            const ctx = chartElement.getContext('2d');
                            charts.inventoryValueChart = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: departmentNames,
                                    datasets: [{
                                        data: departmentValues,
                                        backgroundColor: colors.slice(0, departmentNames.length),
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'right',
                                            labels: {
                                                boxWidth: 15
                                            }
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    const value = context.parsed;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percentage = ((value / total) * 100).toFixed(1);
                                                    return `${context.label}: ${formatCurrency(value)} (${percentage}%)`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        } else {
                            console.warn('No valid inventory data for chart');
                            // Display a message in the chart container
                            if (chartElement.parentNode) {
                                chartElement.parentNode.innerHTML = '<div class="text-center p-5 text-muted">No hay datos de inventario disponibles</div>';
                            }
                        }
                    } else {
                        console.warn("Elemento inventoryValueChart no encontrado");
                    }
                    
                    // Update inventory value table
                    const inventoryValueColumns = [
                        { title: "Departamento", data: 0 },
                        { title: "Valor al Costo", data: 1 },
                        { title: "Valor al Precio", data: 2 },
                        { title: "Ganancia Potencial", data: 3 },
                        { title: "% del Total", data: 4 }
                    ];
                    tables.inventoryValueTable = createDataTable('inventoryValueTable', tableData, inventoryValueColumns, [[1, 'desc']]);
                    
                    // Update totals in table footer - verificando que los elementos existan
                    const totalCostValueElement = document.getElementById('totalCostValue');
                    const totalPriceValueElement = document.getElementById('totalPriceValue');
                    const totalPotentialProfitElement = document.getElementById('totalPotentialProfit');
                    
                    if (totalCostValueElement) {
                        totalCostValueElement.textContent = formatCurrency(totalCostValue);
                    } else {
                        console.warn("Elemento totalCostValue no encontrado");
                    }
                    
                    if (totalPriceValueElement) {
                        totalPriceValueElement.textContent = formatCurrency(totalPriceValue);
                    } else {
                        console.warn("Elemento totalPriceValue no encontrado");
                    }
                    
                    if (totalPotentialProfitElement) {
                        totalPotentialProfitElement.textContent = formatCurrency(totalPriceValue - totalCostValue);
                    } else {
                        console.warn("Elemento totalPotentialProfit no encontrado");
                    }
                    
                    // Update inventory value in overview section
                    const inventoryValueElement = document.getElementById('inventoryValue');
                    if (inventoryValueElement) {
                        inventoryValueElement.textContent = formatCurrency(totalCostValue);
                    } else {
                        console.warn("Elemento inventoryValue no encontrado");
                    }
                    
                    // Calculate inventory turnover (this would typically come from API)
                    // For demo, we'll use a random value between 4 and 12
                    const inventoryTurnoverElement = document.getElementById('inventoryTurnover');
                    if (inventoryTurnoverElement) {
                        const inventoryTurnover = (4 + Math.random() * 8).toFixed(1);
                        inventoryTurnoverElement.textContent = `${inventoryTurnover}x`;
                    } else {
                        console.warn("Elemento inventoryTurnover no encontrado");
                    }
                }
            } catch (error) {
                console.error('Error loading inventory value:', error);
            }
        }

        // Load sales by category
        async function loadSalesByCategory() {
            try {
                const response = await fetch(`api_proxy.php?endpoint=SalesByCategory&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
                const data = await response.json();
                
                if (data && data.length > 0) {
                    // Update the category sales table
                    const tableData = data.map(item => [
                        item.Category || 'Sin Categoría',
                        formatCurrency(item.TotalSales || 0),
                        formatCurrency((item.TotalSales || 0) - (item.TotalCost || 0)),
                        `${(((item.TotalSales || 0) - (item.TotalCost || 0)) / (item.TotalSales || 1) * 100).toFixed(1)}%`
                    ]);
                    
                    const categoryColumns = [
                        { title: "Categoría", data: 0 },
                        { title: "Ventas", data: 1 },
                        { title: "Ganancia", data: 2 },
                        { title: "Margen", data: 3 }
                    ];
                    tables.categorySalesTable = createDataTable('categorySalesTable', tableData, categoryColumns, [[1, 'desc']]);
                }
            } catch (error) {
                console.error('Error loading sales by category:', error);
            }
        }

        // Load low level items
        async function loadLowLevelItems() {
            try {
                const response = await fetch(`api_proxy.php?endpoint=LowLevelItems`);
                const data = await response.json();
                
                if (data && data.length > 0) {
                    // Update low level items table
                    const tableData = data.map(item => [
                        item.ProductCode || '',
                        item.ProductName || '',
                        safeToLocaleString(item.CurrentStock),
                        safeToLocaleString(item.MinimumLevel),
                        safeToLocaleString(item.MaximumLevel),
                        formatCurrency(item.Price || 0),
                        formatCurrency(item.Cost || 0),
                        item.Category || '',
                        item.Department || '',
                        item.PrimarySupplier || ''
                    ]);
                    
                    const lowLevelItemsColumns = [
                        { title: "Código", data: 0 },
                        { title: "Producto", data: 1 },
                        { title: "Stock Actual", data: 2 },
                        { title: "Nivel Mínimo", data: 3 },
                        { title: "Nivel Máximo", data: 4 },
                        { title: "Precio", data: 5 },
                        { title: "Costo", data: 6 },
                        { title: "Categoría", data: 7 },
                        { title: "Departamento", data: 8 },
                        { title: "Proveedor", data: 9 }
                    ];
                    tables.lowLevelItemsTable = createDataTable('lowLevelItemsTable', tableData, lowLevelItemsColumns, [[2, 'asc']]);

                    // Add row highlighting for low stock items
                    $('#lowLevelItemsTable').on('draw.dt', function() {
                        $('#lowLevelItemsTable tbody tr').each(function() {
                            const $row = $(this);
                            const currentStock = parseFloat($row.find('td:eq(2)').text().replace(/,/g, ''));
                            const minLevel = parseFloat($row.find('td:eq(3)').text().replace(/,/g, ''));
                            
                            if (currentStock < minLevel) {
                                $row.addClass('table-danger');
                            } else if (currentStock < minLevel * 1.5) {
                                $row.addClass('table-warning');
                            }
                        });
                    });
                }
            } catch (error) {
                console.error('Error loading low level items:', error);
            }
        }

        // Load top selling products
        async function loadTopProducts() {
            try {
                toggleLoading(true);
                
                // Get filter values
                const category = document.getElementById('categoryFilter')?.value || '';
                const department = document.getElementById('departmentFilter')?.value || '';
                const supplier = document.getElementById('supplierFilter')?.value || '';
                
                // Build query parameters
                let queryParams = `DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`;
                if (category) queryParams += `&Category=${encodeURIComponent(category)}`;
                if (department) queryParams += `&Department=${encodeURIComponent(department)}`;
                if (supplier) queryParams += `&Supplier=${encodeURIComponent(supplier)}`;
                
                console.log('Fetching top products data...');
                const response = await fetch(`api_proxy.php?endpoint=TopSellProducts&${queryParams}`);
                const data = await response.json();
                
                // Verificar que la tabla existe antes de intentar inicializarla
                const tableElement = document.getElementById('topProductsTable');
                if (!tableElement) {
                    console.error("Tabla 'topProductsTable' no encontrada en el DOM");
                    return;
                }
                
                // Verificar que la tabla tiene la estructura correcta
                const thead = tableElement.querySelector('thead');
                const tbody = tableElement.querySelector('tbody');
                
                if (!thead || !tbody) {
                    console.error("La tabla 'topProductsTable' no tiene la estructura correcta (thead o tbody faltante)");
                    // Intentar corregir la estructura
                    if (!thead) {
                        const newThead = document.createElement('thead');
                        newThead.innerHTML = `
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Departamento</th>
                                <th>Categoría</th>
                                <th>Unidades</th>
                                <th>Ventas</th>
                                <th>Ganancia</th>
                                <th>Precio Prom.</th>
                                <th>Margen</th>
                                <th>Stock</th>
                            </tr>
                        `;
                        tableElement.prepend(newThead);
                    }
                    if (!tbody) {
                        const newTbody = document.createElement('tbody');
                        tableElement.append(newTbody);
                    }
                }
                
                if (data && Array.isArray(data) && data.length > 0) {
                    console.log(`Procesando ${data.length} productos para la tabla...`);
                    // Update top products table
                    const tableData = data.map(item => [
                        item.ProductCode || '',
                        item.ProductName || '',
                        item.Department || '',
                        item.Category || '',
                        safeToLocaleString(item.TotalQuantitySold),
                        formatCurrency(item.TotalSales || 0),
                        formatCurrency((item.TotalSales || 0) - (item.TotalCost || 0)),
                        formatCurrency(item.AveragePrice || 0),
                        `${(((item.TotalSales || 0) - (item.TotalCost || 0)) / (item.TotalSales || 1) * 100).toFixed(1)}%`,
                        safeToLocaleString(item.CurrentStock)
                    ]);
                    
                    const topProductsColumns = [
                        { title: "Código", data: 0 },
                        { title: "Producto", data: 1 },
                        { title: "Departamento", data: 2 },
                        { title: "Categoría", data: 3 },
                        { title: "Unidades", data: 4 },
                        { title: "Ventas", data: 5 },
                        { title: "Ganancia", data: 6 },
                        { title: "Precio Prom.", data: 7 },
                        { title: "Margen", data: 8 },
                        { title: "Stock", data: 9 }
                    ];
                    
                    // Inicializar la tabla con manejo de errores
                    console.log('Inicializando tabla de productos más vendidos...');
                    tables.topProductsTable = createDataTable('topProductsTable', tableData, topProductsColumns, [[5, 'desc']]);
                    
                    if (!tables.topProductsTable) {
                        console.error("No se pudo inicializar la tabla 'topProductsTable'");
                        // Mostrar los datos de forma básica como último recurso
                        const tbody = tableElement.querySelector('tbody');
                        if (tbody) {
                            tbody.innerHTML = '';
                            tableData.forEach(row => {
                                const tr = document.createElement('tr');
                                row.forEach(cell => {
                                    const td = document.createElement('td');
                                    td.innerHTML = cell;
                                    tr.appendChild(td);
                                });
                                tbody.appendChild(tr);
                            });
                        }
                    }
                    
                    // Prepare data for the charts - ensure we have at least some data
                    if (data.length > 0) {
                        // Sort data for top sales products
                        const topSalesProducts = [...data]
                            .sort((a, b) => (parseFloat(b.TotalSales) || 0) - (parseFloat(a.TotalSales) || 0))
                            .slice(0, 10);
                        
                        // Sort data for top profit products
                        const topProfitProducts = [...data]
                            .sort((a, b) => {
                                const profitA = (parseFloat(a.TotalSales) || 0) - (parseFloat(a.TotalCost) || 0);
                                const profitB = (parseFloat(b.TotalSales) || 0) - (parseFloat(b.TotalCost) || 0);
                                return profitB - profitA;
                            })
                            .slice(0, 10);
                        
                        // Update sales chart - check if element exists first
                        const salesChartElement = document.getElementById('topProductsChart');
                        if (salesChartElement) {
                            try {
                                if (charts.topProductsChart) {
                                    charts.topProductsChart.destroy();
                                }
                                
                                const ctxSales = salesChartElement.getContext('2d');
                                charts.topProductsChart = new Chart(ctxSales, {
                                    type: 'bar',
                                    data: {
                                        labels: topSalesProducts.map(item => {
                                            const name = item.ProductName || 'Sin nombre';
                                            return name.length > 20 ? name.substring(0, 18) + '...' : name;
                                        }),
                                        datasets: [{
                                            label: 'Ventas',
                                            data: topSalesProducts.map(item => parseFloat(item.TotalSales) || 0),
                                            backgroundColor: 'rgba(0, 87, 184, 0.7)',
                                            borderColor: '#0057b8',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        indexAxis: 'y',
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return `Ventas: ${formatCurrency(context.parsed.x)}`;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            x: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return formatCurrency(value);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            } catch (chartError) {
                                console.error("Error al crear el gráfico de ventas:", chartError);
                                if (salesChartElement.parentNode) {
                                    salesChartElement.parentNode.innerHTML = `<div class="text-center p-5 text-danger">Error al crear el gráfico: ${chartError.message}</div>`;
                                }
                            }
                        } else {
                            console.error("Elemento 'topProductsChart' no encontrado en el DOM");
                        }
                        
                        // Update profit chart - check if element exists first
                        const profitChartElement = document.getElementById('topProfitChart');
                        if (profitChartElement) {
                            try {
                                if (charts.topProfitChart) {
                                    charts.topProfitChart.destroy();
                                }
                                
                                const ctxProfit = profitChartElement.getContext('2d');
                                charts.topProfitChart = new Chart(ctxProfit, {
                                    type: 'bar',
                                    data: {
                                        labels: topProfitProducts.map(item => {
                                            const name = item.ProductName || 'Sin nombre';
                                            return name.length > 20 ? name.substring(0, 18) + '...' : name;
                                        }),
                                        datasets: [{
                                            label: 'Ganancia',
                                            data: topProfitProducts.map(item => 
                                                (parseFloat(item.TotalSales) || 0) - (parseFloat(item.TotalCost) || 0)
                                            ),
                                            backgroundColor: 'rgba(0, 166, 81, 0.7)',
                                            borderColor: '#00a651',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        indexAxis: 'y',
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return `Ganancia: ${formatCurrency(context.parsed.x)}`;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            x: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return formatCurrency(value);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            } catch (chartError) {
                                console.error("Error al crear el gráfico de ganancias:", chartError);
                                if (profitChartElement.parentNode) {
                                    profitChartElement.parentNode.innerHTML = `<div class="text-center p-5 text-danger">Error al crear el gráfico: ${chartError.message}</div>`;
                                }
                            }
                        } else {
                            console.error("Elemento 'topProfitChart' no encontrado en el DOM");
                        }
                    } else {
                        console.warn('No product data available for charts');
                        // Safely display a message if no data
                        ['topProductsChart', 'topProfitChart'].forEach(chartId => {
                            const chartElement = document.getElementById(chartId);
                            if (chartElement && chartElement.parentNode) {
                                chartElement.parentNode.innerHTML = '<div class="text-center p-5 text-muted">No hay datos de productos disponibles</div>';
                            }
                        });
                    }
                } else {
                    console.warn('No product data returned from API');
                    // Clear existing charts if no data
                    if (charts.topProductsChart) {
                        charts.topProductsChart.destroy();
                        charts.topProductsChart = null;
                    }
                    if (charts.topProfitChart) {
                        charts.topProfitChart.destroy();
                        charts.topProfitChart = null;
                    }
                    
                    // Safely display a message if no data
                    ['topProductsChart', 'topProfitChart'].forEach(chartId => {
                        const chartElement = document.getElementById(chartId);
                        if (chartElement && chartElement.parentNode) {
                            chartElement.parentNode.innerHTML = '<div class="text-center p-5 text-muted">No hay datos de productos disponibles</div>';
                        }
                    });
                    
                    // Clear the table
                    if ($.fn.DataTable.isDataTable('#topProductsTable')) {
                        $('#topProductsTable').DataTable().clear().draw();
                    } else {
                        // Si la tabla no está inicializada, mostrar un mensaje
                        const tableElement = document.getElementById('topProductsTable');
                        if (tableElement) {
                            const tbody = tableElement.querySelector('tbody');
                            if (tbody) {
                                tbody.innerHTML = '<tr><td colspan="10" class="text-center">No hay datos disponibles</td></tr>';
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading top products:', error);
                // Safely display error message
                ['topProductsChart', 'topProfitChart'].forEach(chartId => {
                    const chartElement = document.getElementById(chartId);
                    if (chartElement && chartElement.parentNode) {
                        chartElement.parentNode.innerHTML = `<div class="text-center p-5 text-danger">Error al cargar datos: ${error.message}</div>`;
                    }
                });
                
                // Mostrar mensaje de error en la tabla
                const tableElement = document.getElementById('topProductsTable');
                if (tableElement) {
                    const tbody = tableElement.querySelector('tbody');
                    if (tbody) {
                        tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error al cargar datos: ${error.message}</td></tr>`;
                    }
                }
            } finally {
                toggleLoading(false);
            }
        }



    </script>
    <script>

            /**
         * Esta función se encarga de manejar el evento resize de los gráficos de Chart.js y DataTables
         * cuando cambia el estado del sidebar.
         * Se debe incluir al final del archivo principal de JavaScript.
         */

        // Función para optimizar los contenedores y elementos después de cambios en el sidebar
        function optimizeDashboardLayout() {
            // Identificar si estamos en modo expandido
            const isExpanded = document.querySelector('.content').classList.contains('expanded');
            
            // 1. Redimensionar todos los gráficos de Chart.js
            function resizeCharts() {
                if (typeof charts === 'undefined') return;
                
                Object.values(charts).forEach(chart => {
                    if (!chart) return;
                    
                    try {
                        // Para Chart.js, asegurar que usa el ancho completo del contenedor
                        if (chart.canvas) {
                            // Forzar recálculo del tamaño del contenedor
                            const parent = chart.canvas.parentNode;
                            if (parent) {
                                parent.style.height = isExpanded ? '320px' : '300px';
                                
                                // Reajustar el canvas
                                const rect = parent.getBoundingClientRect();
                                chart.canvas.style.width = rect.width + 'px';
                                chart.canvas.style.maxWidth = '100%';
                                
                                // Actualizar dimensiones y renderizar
                                chart.resize();
                                chart.update('none'); // Usar 'none' para mejor rendimiento
                            }
                        }
                    } catch (error) {
                        console.warn('Error al redimensionar gráfico:', error);
                    }
                });
            }
            
            // 2. Redimensionar todas las tablas DataTables
            function resizeTables() {
                if (typeof tables === 'undefined' || typeof $.fn.DataTable === 'undefined') return;
                
                Object.values(tables).forEach(table => {
                    if (!table || !table.columns) return;
                    
                    try {
                        // Ajustar solo el ancho de las columnas sin redibujar toda la tabla
                        table.columns.adjust().draw(false);
                        
                        // También ajustar la altura de la tabla si es necesario
                        const tableNode = table.table().node();
                        if (tableNode) {
                            const wrapper = $(tableNode).closest('.dataTables_wrapper');
                            if (wrapper.length) {
                                wrapper.css('width', '100%');
                            }
                        }
                    } catch (error) {
                        console.warn('Error al redimensionar tabla:', error);
                    }
                });
            }
            
            // 3. Ajustar KPI cards para mejor visualización
            function optimizeKPICards() {
                const kpiCards = document.querySelectorAll('#kpi-cards .dashboard-card');
                kpiCards.forEach(card => {
                    // Añadir clase para indicar el estado expandido/contraído
                    if (isExpanded) {
                        card.classList.add('layout-expanded');
                    } else {
                        card.classList.remove('layout-expanded');
                    }
                });
            }
            
            // 4. Ejecutar todos los ajustes con un pequeño retraso para permitir que terminen las transiciones CSS
            setTimeout(() => {
                resizeCharts();
                resizeTables();
                optimizeKPICards();
                
                // Log para confirmar que se ejecutó la optimización
                console.log(`Dashboard layout optimizado. Modo ${isExpanded ? 'expandido' : 'normal'}`);
            }, 350); // 350ms para dar tiempo a que terminen las transiciones CSS
        }

        // Asignar la función a eventos clave
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Al cambiar el estado del sidebar
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', optimizeDashboardLayout);
            }
            
            // 2. Al cambiar entre secciones del dashboard
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    // Pequeño retraso para asegurar que la sección ya esté visible
                    setTimeout(optimizeDashboardLayout, 200);
                });
            });
            
            // 3. Al cambiar el tamaño de la ventana
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(optimizeDashboardLayout, 250);
            });
            
            // 4. Al cargar inicialmente la página
            setTimeout(optimizeDashboardLayout, 500);
        });   

    </script>    

<script>
// Script simple para manejar cambios del sidebar
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');
    const menuToggle = document.getElementById('menu-toggle');
    
    // Estado inicial - verificar localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
        menuToggle.innerHTML = '<i class="fas fa-expand"></i>';
    }
    
    // Toggle del sidebar
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        
        // Actualizar icono
        if (sidebar.classList.contains('collapsed')) {
            menuToggle.innerHTML = '<i class="fas fa-expand"></i>';
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            localStorage.setItem('sidebarCollapsed', 'false');
        }
        
        // Forzar redimensionamiento de los charts después de la transición
        setTimeout(function() {
            for (const chartId in charts) {
                if (charts[chartId] && typeof charts[chartId].update === 'function') {
                    charts[chartId].update();
                }
            }
            
            // Ajustar tablas DataTables
            for (const tableId in tables) {
                if (tables[tableId] && tables[tableId].columns) {
                    tables[tableId].columns.adjust().draw(false);
                }
            }
        }, 350);
    });
});
</script>

<script>
// Mejorar comportamiento mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    
    // Crear overlay para móvil si no existe
    if (!document.querySelector('.sidebar-overlay')) {
        const overlay = document.createElement('div');
        overlay.classList.add('sidebar-overlay');
        document.body.appendChild(overlay);
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            this.classList.remove('active');
        });
    }
    
    const overlay = document.querySelector('.sidebar-overlay');
    
    // Estado inicial - verificar localStorage
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        content.classList.add('expanded');
        
        // Actualizar iconos de ambos botones
        document.querySelectorAll('#menu-toggle, #mobile-menu-toggle').forEach(btn => {
            btn.innerHTML = '<i class="fas fa-expand"></i>';
        });
    }
    
    // Toggle del sidebar para desktop
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleSidebar);
    }
    
    // Toggle del sidebar para móvil
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            console.log("Mobile menu toggle clicked");
            
            // En móvil simplemente mostramos/ocultamos el sidebar
            sidebar.classList.toggle('active');
            
            // Mostrar/ocultar overlay
            if (overlay) {
                overlay.classList.toggle('active');
            }
            
            // Para debugging
            console.log("Sidebar active:", sidebar.classList.contains('active'));
            console.log("Overlay active:", overlay.classList.contains('active'));
        });
    }
    
    function toggleSidebar() {
        const isCurrentlyCollapsed = sidebar.classList.contains('collapsed');
        
        // Guardar preferencia
        localStorage.setItem('sidebarCollapsed', !isCurrentlyCollapsed);
        
        // Toggle classes
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        
        // Actualizar iconos de ambos botones
        const newIcon = isCurrentlyCollapsed ? '<i class="fas fa-bars"></i>' : '<i class="fas fa-expand"></i>';
        document.querySelectorAll('#menu-toggle, #mobile-menu-toggle').forEach(btn => {
            btn.innerHTML = newIcon;
        });
        
        // Redimensionar elementos después de la transición
        setTimeout(function() {
            for (const chartId in charts) {
                if (charts[chartId] && typeof charts[chartId].update === 'function') {
                    charts[chartId].update();
                }
            }
            
            // Ajustar tablas DataTables
            for (const tableId in tables) {
                if (tables[tableId] && tables[tableId].columns) {
                    tables[tableId].columns.adjust().draw(false);
                }
            }
        }, 350);
    }
    
    // Cerrar sidebar en móviles al hacer clic en un enlace
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) { // Bootstrap lg breakpoint
                sidebar.classList.remove('active');
                if (overlay) {
                    overlay.classList.remove('active');
                }
            }
        });
    });
});
</script>

<script>
// Script para inicializar tooltips solo cuando el sidebar está contraído
document.addEventListener('DOMContentLoaded', function() {
    // Función para inicializar o reinicializar los tooltips
    function updateTooltips() {
        // Primero destruimos los tooltips existentes
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            const tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
            if (tooltip) {
                tooltip.dispose();
            }
        });
        
        // Solo inicializamos los tooltips si el sidebar está contraído
        if (document.querySelector('.sidebar').classList.contains('collapsed')) {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(tooltipTriggerEl => {
                new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover',
                    container: 'body'
                });
            });
            console.log('Tooltips inicializados - Sidebar contraído');
        } else {
            console.log('Tooltips desactivados - Sidebar expandido');
        }
    }
    
    // Activar tooltips al cargar la página
    updateTooltips();
    
    // Actualizar tooltips cuando cambia el estado del sidebar
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            // Esperar a que termine la transición
            setTimeout(updateTooltips, 350);
        });
    }
    
    // Actualizar tooltips cuando cambia el tamaño de la ventana
    window.addEventListener('resize', function() {
        clearTimeout(window.resizeTimer);
        window.resizeTimer = setTimeout(updateTooltips, 300);
    });
    
    // Ocultar tooltip al hacer clic en un enlace del menu
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            const tooltip = bootstrap.Tooltip.getInstance(this);
            if (tooltip) {
                tooltip.hide();
            }
        });
    });
});
</script>

    <script src="js/clients.js"></script>
    <script src="js/maintenance.js"></script>
    <script src="../js/sidebar.js"></script>
    <?php include 'scripts.php'; ?>
</body>
</html>
