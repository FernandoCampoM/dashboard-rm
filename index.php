<?php
session_start();
// Incluir configuración
require_once 'config.php';
// Ruta del archivo de configuración
require_once 'setup/config_functions.php';
//validar si existe una configuracion del backend guardada


$config = get_configBackend();
if (!$config) {
    header("Location: setup/setup-backend.php");
    exit;
} 



// 1. Verificar si el usuario ha iniciado sesión
// Se comprueba si la variable de sesión 'loggedin' está establecida y es verdadera
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no ha iniciado sesión, lo redirigimos a la página de login
    header('Location: authentication/login.php');
    exit; // Es crucial usar exit() después de una redirección para detener la ejecución del script
}

// 2. Si el usuario ha iniciado sesión, el script continúa
// A partir de aquí, puedes acceder a los datos de la sesión:
$userID = $_SESSION['UserID'];
$username = $_SESSION['Username'];


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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css"
        rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/datatables.css" />
    <link rel="stylesheet" type="text/css" href="css/movil.css" />
    <link rel="stylesheet" type="text/css" href="css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/maintenance.css" />
    <link rel="stylesheet" type="text/css" href="css/index.css" />
    

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
                RM Dashboard
            </a>
            <a class="message"> </a>
            <div class="col-md-6">
                <h5 class="navbar-brand">&nbsp;&nbsp;&nbsp;&nbsp; Hola,
                    <strong><?php echo htmlspecialchars($_SESSION['EmployeeName'] ?? 'Invitado/a'); ?></strong>, te
                    damos la Bienvenida!
                </h5>
            </div>
            <!-- Botón toggle para menú de usuario en móviles -->
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-user-circle"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle black-text" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" data-section="overview-section">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['EmployeeName'] ?? 'Invitado/a'); ?>
                            <br><?php
                            $accessText = '';
                            $accessLevel = $_SESSION['AccessLevel'] ?? null; // Obtener el nivel de acceso de la sesión, por defecto null
                            
                            // Usar un switch para determinar el texto según el nivel de acceso
                            switch ($accessLevel) {
                                case 1:
                                    $accessText = 'Administrador';
                                    break;
                                case 2:
                                    $accessText = 'Cajero';
                                    break;
                                case 3:
                                    $accessText = 'None';
                                    break;
                                default:
                                    $accessText = 'Desconocido'; // Texto por defecto si el valor no coincide o no está definido
                                    break;
                            }
                            echo htmlspecialchars($accessText);
                            // Mostrar el texto del nivel de acceso, sanitizando para seguridad
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Perfil</a></li>
                            <li><a id="btn-configuracion" class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="authentication/logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Salir</a></li>
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
            <div class="col-md-3 col-lg-2 d-md-block sidebar" id="sidebarTest" >
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
                            <a class="nav-link" href="#" id="maintenance-link" data-bs-toggle="collapse"
                                data-bs-target="#maintenance-collapse" aria-expanded="false"
                                aria-controls="maintenance-collapse" data-bs-toggle="tooltip" data-bs-placement="right"
                                title="Mantenimiento" data-section="clients-section">
                                <i class="fas fa-cogs"></i>
                                <span>Mantenimiento</span>
                            </a>
                            <div class="collapse" id="maintenance-collapse" >
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" id="clients-link" data-section="clients-section"
                                            data-bs-toggle="tooltip" data-bs-placement="right" title="Clientes">
                                            <i class="fas fa-users"></i>
                                            <span>Clientes</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#" id="products-maintenance-link"
                                            data-section="products-maintenance-section" data-bs-toggle="tooltip"
                                            data-bs-placement="right" title="Productos">
                                            <i class="fas fa-box"></i>
                                            <span>Productos</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    </ul>

                    <div class="text-center mt-4">
                        <button class="btn btn-primary" id="add-product-btn">Agregar Producto</button>
                    </div>

                    <hr class="my-3 bg-light opacity-25">

                    <!-- Información adicional para mostrar en el sidebar -->
                    <div class="company-info mt-4 px-3 text-white">
                        <div class="text-center mb-3">
                            <!--<img src="https://via.placeholder.com/100" alt="Company Logo" class="img-fluid rounded-circle" style="max-width: 80px;">-->
                            <h5 class="mt-3 text-white">
                                <?php echo $_SESSION['InfoCompany']['Name'] ?? 'Nombre de la Empresa'; ?>
                            </h5>
                        </div>
                        <div class="small">
                            <p class="mb-1"><i class="fas fa-clock me-2"></i> Actualizado: <span id="last-update-time">Hoy 15:30</span></p>
                            <p class="mb-0"><i class="fas fa-user me-2"></i> Usuario: <span id="current-user"><a>
                                        <?php echo htmlspecialchars($_SESSION['EmployeeName'] ?? 'Invitado/a'); ?>
                                        <br><?php
                                        $accessText = '';
                                        $accessLevel = $_SESSION['AccessLevel'] ?? null; // Obtener el nivel de acceso de la sesión, por defecto null
                                        
                                        // Usar un switch para determinar el texto según el nivel de acceso
                                        switch ($accessLevel) {
                                            case 1:
                                                $accessText = 'Administrador';
                                                break;
                                            case 2:
                                                $accessText = 'Cajero';
                                                break;
                                            case 3:
                                                $accessText = 'None';
                                                break;
                                            default:
                                                $accessText = 'Desconocido'; // Texto por defecto si el valor no coincide o no está definido
                                                break;
                                        }
                                        echo htmlspecialchars($accessText);
                                        // Mostrar el texto del nivel de acceso, sanitizando para seguridad
                                        ?>
                                    </a></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-4 ms-sm-auto col-lg-10 px-md-4 py-4 content">
                <!-- Date Range Filter -->
                <div class="date-filters">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0"><i class="fas fa-filter me-2"></i> <?php echo $_SESSION['InfoCompany']['Name'] ?? 'Nombre de la Empresa'; ?></h4>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text" onclick="document.getElementById('dateFrom').showPicker()"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="dateFrom">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text" onclick="document.getElementById('dateTo').showPicker()"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="dateTo">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" id="applyDateFilter">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="btn-toolbar mb-1 mb-md-0 d-flex justify-content-between">
                                    <div class="btn-group me-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary active"
                                            id="filterToday">
                                            <i class="fas fa-calendar-day me-1"></i> DIA
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="filterWeek">
                                            <i class="fas fa-calendar-week me-1"></i> SEMANA
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary "
                                            id="filterMonth">
                                            <i class="fas fa-calendar-alt me-1"></i> MES
                                        </button>
                                    </div>

                                    <div class="btn-group me-1 ">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="refreshOverview">
                                            <i class="fas fa-sync-alt me-1"></i> Actualizar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-file-export me-1"></i> Exportar
                                        </button>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Contenedor GRAFICAS-->
                    
                        <!-- Overview Section -->
                        <section id="overview-section" class="dashboard-section active row">
                            <div class="col-md-6 col-lg-7" >         
                            <!-- KPI Cards -->
                            <div class="row" id="kpi-cards">
                                <div class="col-md-6 col-lg-6   test ">
                                    <div class="card dashboard-card-imgBackground ">

                                        <div class="card-body ">
                                            <div class="d-flex  align-items-center mb-0">
                                                <div class="card-icon ms-3">
                                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                                </div>
                                                <h5 class="card-title mb-0">Ventas Totales</h5>
                                            </div>
                                            <div class="col m-0">
                                                <h2 class="widget-value text-center m-0" id="totalSales">$0.00</h2>
                                                <p class=" text-end me-5 mb-0" id="yesterdaySalesLabel">Ventas ayer:</p>
                                                <div class="row mb-0">
                                                    <p class="col  text-end me-4 mb-0" id="yesterdaySales"><strong>
                                                            <span class="trend-indicator trend-up" id="salesTrend">
                                                                <i class="col fas fa-long-arrow-alt-up"></i> </strong>
                                                        </span>$0.00
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6   test ">
                                    <div class="card dashboard-card-imgBackground ">

                                        <div class="card-body ">
                                            <div class="d-flex  align-items-center mb-0">
                                                <div class="card-icon ms-3">
                                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                                </div>
                                                <h5 class="card-title mb-0">Costos</h5>
                                            </div>
                                            <div class="row m-0">
                                                <h2 class="widget-value text-center m-0" id="totalCost">$0.00</h2>
                                            </div>
                                            <div class="row d-flex align-items-end mb-0">
                                                <p class="col text-end me-0 mb-0" id="soldItems">0</p>
                                                <p class="col-6 text-start  mb-0" id="soldItemsLabel">Articulos Vendidos
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6   test ">
                                    <div class="card dashboard-card-imgBackground ">

                                        <div class="card-body ">
                                            <div class="d-flex  align-items-center mb-0">
                                                <div class="card-icon ms-3">
                                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                                </div>
                                                <h5 class="card-title mb-0">Ganancia</h5>
                                            </div>
                                            <div class="col m-0">
                                                <h2 class="widget-value text-center m-0" id="totalProfit">$0.00</h2>
                                                <div class="row d-flex align-items-end mb-0">
                                                    <p class="col text-end ms-5 mb-0">Porcentaje:</p>
                                                    <span class="col ms-auto" id="profitMargin">0%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6   test ">
                                    <div class="card dashboard-card-imgBackground ">

                                        <div class="card-body ">
                                            <div class="d-flex  align-items-center mb-0">
                                                <div class="card-icon ms-3">
                                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                                </div>
                                                <h5 class="card-title mb-0">Impuestos</h5>
                                            </div>
                                            <div class="col m-0">
                                                <h2 class="widget-value text-center m-0" id="totalTax">$0.00</h2>


                                            </div>
                                            <div class="row d-flex align-items-end mb-0">
                                                <p class="col text-end ms-5 mb-0" id="stateTaxLabel">Estatal:</p>
                                                <p class="col  text-start  mb-0" id="stateTax">
                                                    $0.00</p>
                                            </div>

                                            <div class="row d-flex align-items-end mb-0">
                                                <p class="col text-end ms-5 mb-0" id="municipalTaxLabel">Municipal:</p>
                                                <p class="col  text-start  mb-0" id="municipalTax">
                                                    $0.00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-11 col-lg-11 mb-4 col">
                                    <div class="card dashboard-card-list">
                                        <div class="card-header d-flex align-items-center">
                                            <div class="card-icon">
                                                <i class="fas fa-folder fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0">Ventas por Departamento</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="departmentSalesChart"></canvas>
                                            </div>
                                            
                                            <div class="list-container" id="department-sales-list">
                                                
                                            </div>
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination justify-content-center mt-3">
                                                    <li id="prev-btn-li" class="page-item disabled">
                                                    <a class="page-link" href="#" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                    </li>
                                                    <li id="next-btn-li" class="page-item">
                                                    <a class="page-link" href="#" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                    </li>
                                                </ul>
                                                </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 mb-4 col">
                                    <div class="card dashboard-card-list">
                                        <div class="card-header d-flex align-items-center ">
                                            <div class="card-icon">
                                                <i class="fas fa-volume-up fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0">Ventas por Categoria</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="salesByCategoryTable" class="table table-hover table-striped ">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Categoria</th>
                                                            <th scope="col" class="text-end">Venta</th>
                                                            <th scope="col" class="text-end">Ganancia</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="salesByCategoryBody">
                                                        <tr>
                                                            <td>test category</td>
                                                            <td class="text-end">$ 85.54</td>
                                                            <td class="text-end">$ 15.55</td>
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 mb-4 col">
                                    <div class="card dashboard-card-list">
                                        <div class="card-header d-flex align-items-center ">
                                            <div class="card-icon">
                                                <i class="fas fa-pencil fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0">Inventario Bajo</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="lowInventoryTable" class="table table-hover table-striped ">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Código</th>
                                                            <th scope="col">Descripción</th>
                                                            <th scope="col" class="text-end">Cantidad</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="lowInventoryBody">
                                                        <tr>
                                                            <td>001</td>
                                                            <td>Papas Fritas</td>
                                                            <td class="text-end">10</td>
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 mb-4">
                                    <div class="card dashboard-card-list">
                                        <div class="card-header d-flex align-items-center">
                                            <div class="card-icon">
                                                <i class="fas fa-money-bill-transfer fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0">Métodos de Pago</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container">
                                                <canvas id="paymentMethodsChart"></canvas>
                                            </div>
                                            <div class="list-container" id="payment-methods-list">

                                            </div>
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination justify-content-center mt-3">
                                                    <li id="prev-btn-li-paymentMethods" class="page-item disabled">
                                                    <a class="page-link" href="#" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                    </li>
                                                    <li id="next-btn-li-paymentMethods" class="page-item">
                                                    <a class="page-link" href="#" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                    </li>
                                                </ul>
                                                </nav>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 mb-4">
                                    <div class="card dashboard-card">
                                        <div class="card-header d-flex align-items-center">
                                            <div class="card-icon">
                                                <i class="fas fa-boxes fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0">Valor Inventario</h5>
                                        </div>
                                        <div class="card-body">
                                            <h2 class="widget-value" id="inventoryValue">$0.000</h2>
                                            <!-- <div class="d-flex align-items-center mt-2">
                                                <span class="text-muted">Rotación</span>
                                                <span class="ms-auto" id="inventoryTurnover">0x</span>
                                            </div> -->
                                            <div class="chart-container">
                                                <canvas id="inventoryValueChart1"></canvas>
                                            </div>
                                            <div class="list-container" id="inventory-sumary-list">
                                                
                                            </div>
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination justify-content-center mt-3">
                                                    <li id="prev-btn-li-inventory" class="page-item disabled">
                                                    <a class="page-link" href="#" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                    </li>
                                                    <li id="next-btn-li-inventory" class="page-item">
                                                    <a class="page-link" href="#" aria-label="Next">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="kpi-cards2">
                                
                                
                            </div>
                            </div>
                            <!--- Contenedor ESTADISTICAS-->
                            <div id="contEstadisticas" class="col-md-4 col-lg-5" >
                                <h4 class="mb-0"><i class="fas fa-chart-column me-2"></i> Estadisticas</h4>
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 mb-4 col">
                                            <div class="card ">
                                                <div class="card-header d-flex align-items-center ">
                                                
                                                    <h5 class="card-title mb-0">Total de Transacciones</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col">
                                                            <strong><h2 style="color: #006ED3;">|</h2></strong>
                                                        </div>
                                                        <div class="col">
                                                            <strong><h3 class="text-end " style="color: #006ED3;" id="totalTransactions">0</h3></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-6 mb-4 col">
                                            <div class="card ">
                                                <div class="card-header d-flex align-items-center ">
                                                
                                                    <h5 class="card-title mb-0">Promedio de Venta Por Transacción</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col">
                                                            <strong><h2 style="color: #006ED3;">|</h2></strong>
                                                        </div>
                                                        <div class="col ">
                                                            <strong><h3 class="text-end" style="color: #006ED3;" id="averageSalePerTransaction">$0.00</h3></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 mb-4 col">
                                            <div class="card ">
                                                <div class="card-header d-flex align-items-center ">
                                                
                                                    <h5 class="card-title mb-0">Promedio de Productos por Venta</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col">
                                                            <strong><h2 style="color: #006ED3;">|</h2></strong>
                                                        </div>
                                                        <div class="col">
                                                            <strong><h3 class="text-end " style="color: #006ED3;" id="avgProductsPerSale">0</h3></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-6 mb-4 col">
                                            <div class="card ">
                                                <div class="card-header d-flex align-items-center ">
                                                
                                                    <h5 class="card-title mb-0">Promedio de Venta Por Hora</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col">
                                                            <strong><h2 style="color: #006ED3;">|</h2></strong>
                                                        </div>
                                                        <div class="col ">
                                                            <strong><h3 class="text-end" style="color: #006ED3;" id="avgSalesPerHour">$0.00</h3></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-6   test ">
                                            <div class="card ">
                                                <div class="card-header d-flex align-items-center ">
                                                
                                                   
                                                        <div class="card-icon ms-3">
                                                            <i class="fas fa-dollar-sign fa-2x"></i>
                                                        </div>
                                                        <h5 class="card-title mb-0">Nomina</h5>
                                                    
                                                </div>
                                                <div class="card-body ">
                                                    
                                                    <div class="row m-0">
                                                        <h2 class="widget-value text-center m-0" id="toltalNomina">$0.00</h2>
                                                    </div>
                                                    <div class="row d-flex align-items-end mb-0">
                                                        <p class="col text-end me-0 mb-0" id="numEmployees">0</p>
                                                        <p class="col-6 text-start  mb-0" id="numEmployeesLabel">Empleados
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-6 mb-4 col">
                                            <div class="card ">
                                                <div class="card-header d-flex align-items-center ">
                                                
                                                    <h5 class="card-title mb-0">Ganancia Promedio por Factura</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col">
                                                            <strong><h2 style="color: #006ED3;">|</h2></strong>
                                                        </div>
                                                        <div class="col ">
                                                            <strong><h3 class="text-end" style="color: #006ED3;" id="avgProfitPerTransaction">$0</h3></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 mb-4">
                                            <div class="card ">
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
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 mb-4">
                                            <div class="card ">
                                                <div class="card-header">
                                                    <div class="row">
                                                        <h5 class="col-md-5 card-title mb-0">Ventas Por Semana</h5>
                                                    <!-- <div class="col-md-6 d-flex justify-content-end btn-group me-2">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary active"
                                                            id="dailySalesFilterDay">
                                                            <i class="fas fa-calendar-day me-1"></i> DIA
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="dailySalesFilterWeek">
                                                            <i class="fas fa-calendar-week me-1"></i> SEMANA
                                                        </button>
                                                    </div> -->
                                                    </div>
                                                    
                                                </div>
                                                <div class="card-body">
                                                    <div class="chart-container">
                                                        <canvas id="dailySalesChart"></canvas>
                                                        
                                                    </div>
                                                    <p class="text-center">
                                                            <span style="color: #369FFF; font-weight: bold;">■</span> Venta más alta
                                                            <span style="color: #28a745; font-weight: bold; margin-left: 10px;">■</span>Venta de Hoy
                                                        </p>
                                                </div>
                                            </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="card ">
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
                            </div>

                        
                        </section>

                    
                    
                </div>
                                      



                <!-- Sales Section -->
                <section id="sales-section" class="dashboard-section d-none">
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
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
                    <!-- Sales Categories and Department Tables -->
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Ventas por Categoría</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="categorySalesTable" class="table table-striped table-hover"
                                            style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Categoría</th>
                                                    <th>Ventas</th>
                                                    <th>Ganancia</th>
                                                    <th>Margen</th>
                                                    <th>Facturas</th>
                                                    <th>Unidades Vendidas</th>
                                                    <th>Precio Promedio</th>
                                                    
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
                     <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Ventas por Departamento</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="departmentSalesTable" class="table table-striped table-hover"
                                            style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Departamento</th>
                                                    <th>Ventas</th>
                                                    <th>Ganancia</th>
                                                    <th>Margen</th>
                                                    <th>Cantidad de Facturas</th>
                                                    <th>Cantidad Vendida</th>
                                                    <th>Promedio de Precio</th>
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
                                        <table id="paymentMethodsTable" class="table table-striped table-hover"
                                            style="width:100%">
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
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
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
                                        <table id="topProductsTable" class="table table-striped table-hover"
                                            style="width:100%">
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
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
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
                                        <table id="inventoryValueTable" class="table table-striped table-hover"
                                            style="width:100%">
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
                                        <table id="lowLevelItemsTable" class="table table-striped table-hover"
                                            style="width:100%">
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
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
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
                                            <input type="text" class="form-control" id="clientNameFilter"
                                                placeholder="Buscar por nombre">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="clientCategoryFilter" class="form-label">Categoría</label>
                                            <select class="form-select" id="clientCategoryFilter">
                                                <option value="">Todas las categorías</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="clientCityFilter" class="form-label">Ciudad</label>
                                            <input type="text" class="form-control" id="clientCityFilter"
                                                placeholder="Filtrar por ciudad">
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
                                        <table id="clientsTable" class="table table-striped table-hover"
                                            style="width:100%">
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
                    <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="clientModalLabel">Añadir Cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="clientForm">

                                        <input type="hidden" class="form-control" id="clientId" readonly>
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
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="clientCreditLimit" class="form-label">Limite de Credito</label>
                                                <input type="text" class="form-control" id="clientCreditLimit">
                                            </div>
                                            
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="saveClientBtn">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Añadir después de la sección de clientes -->
                <section id="products-maintenance-section" class="dashboard-section d-none">
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h2><i class="fas fa-box me-2"></i>Gestión de Productos</h2>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="refreshProductsMaintenance">
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
                                            <input type="text" class="form-control" id="productCodeFilter"
                                                placeholder="Código de producto">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="productNameFilter" class="form-label">Descripción</label>
                                            <input type="text" class="form-control" id="productNameFilter"
                                                placeholder="Nombre o descripción">
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
                                            <button class="btn btn-primary" id="    ">
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
                                        <!-- El template (oculto por defecto) -->
<template id="modalActualizarProducto">
  <div class="modal-content" style="width: 200px;">
    <input type="text" style="padding: 0; margin: 0; height: 35px; width: 200px; border: 1px solid #a59f9fff; border-radius: 4px; 
                    box-sizing: border-box; font-size: 14px;"  id="datoInput" placeholder="">
    <div class="actions" style="box-shadow: 0 4px 8px rgba(0, 0, 0,0.3); background: #fff;
                border-radius: 4px; height: 30px; width: 200px; 
                display: flex; justify-content: center; align-items: center; gap: 10px;">
      <button class="close-btn"  style="border: none; background: transparent; 
                     height: 25px; width: 25px; display: flex; 
                     justify-content: center; align-items: center; cursor: pointer;">
        <span style="color: red; font-size: 18px;">✖</span></button>
      <button class="save-btn custom-icon-success" style=" padding-left: 10px; border: none; background: transparent; 
                     height: 25px; width: 25px; display: flex; 
                     justify-content: center; align-items: center; cursor: pointer;">
        <span style="color: green; font-size: 18px;">✔</span></button>
    </div>
  </div>
</template>

                    <!-- Tabla de Productos -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Listado de Productos</h5>
                                    
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="productsMaintenanceTable" class="table table-striped table-hover"
                                            style="width:100%">
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
                    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="productModalLabel">Añadir Producto</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
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
                                                <input type="text" class="form-control" id="productDescription"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="productCost" class="form-label">Costo</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" class="form-control"
                                                        id="productCost" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="productPrice" class="form-label">Precio</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" class="form-control"
                                                        id="productPrice" required>
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
                                                <label for="productReorderPoint" class="form-label">Punto de
                                                    Reorden</label>
                                                <input type="number" class="form-control" id="productReorderPoint">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="productReorderQty" class="form-label">Cantidad de
                                                    Reorden</label>
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
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>
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
<!-- Modal editar backend config Bootstrap -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="configModalLabel">Configuración Backend</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <div class="login-container">
            <h2>Configuración de Backend</h2>
            <div class="card card-config p-4">
                <div class="form-group mb-3">
                    <label for="backend-ip" class="form-label text-start d-block">IP del backend:</label>
                    <input type="text" id="backend-ip" class="form-control" placeholder="192.168.0.10" 
                           value="<?= htmlspecialchars($config['backend_ip'] ?? '') ?>">
                </div>
                <div class="form-group mb-3">
                    <label for="backend-port" class="form-label text-start d-block">Puerto del backend:</label>
                    <input type="text" id="backend-port" class="form-control" placeholder="3000" 
                           value="<?= htmlspecialchars($config['backend_port'] ?? '') ?>">
                </div>
                <button id="save-config" class="btn btn-primary w-100 mt-3">Guardar</button>
            </div>
        </div>

      </div>
    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        let currentDateFrom = moment().format('YYYY-MM-DD');
        let currentDateTo = moment().format('YYYY-MM-DD');
        let charts = {};
        let tables = {};

        // Initialize datepickers with current values
        document.getElementById('dateFrom').value = currentDateFrom;
        document.getElementById('dateTo').value = currentDateTo;

        //VARIABLES PARA DEPARTMENTS LIST
        const departmentList = document.getElementById('department-sales-list');
        const prevBtnLi = document.getElementById('prev-btn-li');
        const nextBtnLi = document.getElementById('next-btn-li');
        const prevBtn = prevBtnLi.querySelector('.page-link');
        const nextBtn = nextBtnLi.querySelector('.page-link');
        const itemsPerPage = 5;
        let currentPage = 1;
        let currentDataDepartments = [];
        // Constantes para los elementos del DOM de la lista de métodos de pago
const paymentMethodsList = document.getElementById('payment-methods-list');
const prevBtnLiPaymentMethods = document.getElementById('prev-btn-li-paymentMethods');
const nextBtnLiPaymentMethods = document.getElementById('next-btn-li-paymentMethods');
const prevBtnPaymentMethods = prevBtnLiPaymentMethods.querySelector('.page-link');
const nextBtnPaymentMethods = nextBtnLiPaymentMethods.querySelector('.page-link');
// Variables de estado para la paginación
const itemsPerPagePaymentMethods = 5;
let currentPagePaymentMethods = 1;
let currentPaymentMethodsData = [];
// Constantes para los elementos del DOM del resumen de Inventario
const inventoryList = document.getElementById('inventory-sumary-list');
const prevBtnLiInventory = document.getElementById('prev-btn-li-inventory');
const nextBtnLiInventory = document.getElementById('next-btn-li-inventory');
const prevBtnInventory = prevBtnLiInventory.querySelector('.page-link');
const nextBtnInventory = nextBtnLiInventory.querySelector('.page-link');
// Variables de estado para la paginación
const itemsPerPageInventory = 5;
let currentPageInventory = 1;
let currentInventoryData = [];
         const _backgroundColor = [
    '#0057b8', // Azul
    '#00a651', // Verde
    '#ffc107', // Amarillo
    '#dc3545', // Rojo
    '#6f42c1', // Morado
    '#fd7e14', // Naranja
    '#20c997', // Verde agua
    '#6c757d', // Gris

    '#ff66b2', // Rosa fuerte
    '#17a2b8', // Azul turquesa
    '#8bc34a', // Verde lima
    '#ff5722', // Naranja rojizo
    '#4caf50', // Verde estándar
    '#673ab7', // Púrpura oscuro
    '#3f51b5', // Azul índigo
    '#e91e63', // Rosa intenso
    '#795548', // Marrón
    '#9e9e9e', // Gris claro
    '#607d8b', // Azul grisáceo
    '#cddc39'  // Amarillo verdoso
];



        /**
 * Renderiza los ítems de la lista para la página actual.
 * @param {Array} dataToRender - El subconjunto de datos a mostrar en la página.
 */
function renderListItems(dataToRender) {
    departmentList.innerHTML = '';

    dataToRender.forEach((item, index) => {
        const listItem = document.createElement('div');
        listItem.className = 'list-item d-flex justify-content-between align-items-center mb-2';
        
        const indexToData = currentDataDepartments.findIndex(department => department.DepartmentID === item.DepartmentID);
       
        // Usa el índice y el operador módulo para ciclar a través de los colores sin eliminarlos
        const color = _backgroundColor[indexToData % _backgroundColor.length];
        
        listItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="color-box me-2" style="background-color: ${color};width: 16px;
    height: 16px;
    margin-right: 5px;
    border: 1px solid #ccc;"></div>
                <span class="category fw-bold">${item.Department || 'Sin Departamento'}</span>
            </div>
            <span class="value">${formatCurrencyP(item.TotalSales || 0)}</span>
        `;
        departmentList.appendChild(listItem);
    });
}
        /**
 * Actualiza el estado (habilitado/deshabilitado) de los controles de paginación.
 * @param {number} totalPages - El número total de páginas.
 */
function updateNavigationControls(totalPages) {
    if (currentPage === 1) {
        prevBtnLi.classList.add('disabled');
    } else {
        prevBtnLi.classList.remove('disabled');
    }

    if (currentPage === totalPages) {
        nextBtnLi.classList.add('disabled');
    } else {
        nextBtnLi.classList.remove('disabled');
    }
}

/**
 * Maneja la paginación para los ítems de venta.
 * @param {Array} newData - El conjunto completo de datos a paginar.
 */
function setupPagination(newData) {
    currentDataDepartments = newData;
    currentPage = 1; // Reinicia la página a 1 cuando los datos cambian
    
    const totalPages = Math.ceil(currentDataDepartments.length / itemsPerPage);

    function displayCurrentPage() {
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const itemsToDisplay = currentDataDepartments.slice(start, end);
        renderListItems(itemsToDisplay);
        updateNavigationControls(totalPages);
    }
    
    // Limpia los listeners para evitar duplicados
    prevBtn.removeEventListener('click', handlePrevClick);
    nextBtn.removeEventListener('click', handleNextClick);

    function handlePrevClick(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            displayCurrentPage();
        }
    }

    function handleNextClick(e) {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            displayCurrentPage();
        }
    }

    prevBtn.addEventListener('click', handlePrevClick);
    nextBtn.addEventListener('click', handleNextClick);

    // Muestra la primera página al inicializar
    displayCurrentPage();
}
/**
 * Renderiza los ítems de la lista de métodos de pago para la página actual.
 * @param {Array} dataToRender - El subconjunto de datos a mostrar en la página.
 */
function renderPaymentMethodItems(dataToRender) {
    paymentMethodsList.innerHTML = '';
    
    dataToRender.forEach((item, index) => {
        const listItem = document.createElement('div');
        listItem.className = 'list-item d-flex justify-content-between align-items-center mb-2';
        
        // Usa el índice y el operador módulo para ciclar a través de los colores
        
        
        listItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="color-box me-2" style="background-color: ${item.color};width: 16px;
    height: 16px;
    margin-right: 5px;
    border: 1px solid #ccc;"></div>
                <span class="category fw-bold">${item.name || 'Sin Método'}</span>
            </div>
            <span class="value">${formatCurrencyP(item.value || 0)}</span>
        `;
        paymentMethodsList.appendChild(listItem);
    });
}
/**
 * Actualiza el estado (habilitado/deshabilitado) de los controles de paginación para los métodos de pago.
 * @param {number} totalPages - El número total de páginas.
 */
function updatePaymentMethodNavigation(totalPages) {
    if (currentPagePaymentMethods === 1) {
        prevBtnLiPaymentMethods.classList.add('disabled');
    } else {
        prevBtnLiPaymentMethods.classList.remove('disabled');
    }

    if (currentPagePaymentMethods === totalPages) {
        nextBtnLiPaymentMethods.classList.add('disabled');
    } else {
        nextBtnLiPaymentMethods.classList.remove('disabled');
    }
}

/**
 * Configura y gestiona la paginación para la lista de métodos de pago.
 * @param {Array} newData - El conjunto completo de datos a paginar.
 */
function setupPaymentMethodPagination(newData) {
    currentPaymentMethodsData = newData;
    currentPagePaymentMethods = 1; // Reinicia la página a 1 cuando los datos cambian
    
    const totalPages = Math.ceil(currentPaymentMethodsData.length / itemsPerPagePaymentMethods);

    function displayCurrentPaymentMethodPage() {
        const start = (currentPagePaymentMethods - 1) * itemsPerPagePaymentMethods;
        const end = start + itemsPerPagePaymentMethods;
        const itemsToDisplay = currentPaymentMethodsData.slice(start, end);
        renderPaymentMethodItems(itemsToDisplay);
        updatePaymentMethodNavigation(totalPages);
    }
    
    // Limpia los listeners antiguos para evitar duplicados
    prevBtnPaymentMethods.removeEventListener('click', handlePrevClickPaymentMethods);
    nextBtnPaymentMethods.removeEventListener('click', handleNextClickPaymentMethods);

    function handlePrevClickPaymentMethods(e) {
        e.preventDefault();
        if (currentPagePaymentMethods > 1) {
            currentPagePaymentMethods--;
            displayCurrentPaymentMethodPage();
        }
    }

    function handleNextClickPaymentMethods(e) {
        e.preventDefault();
        if (currentPagePaymentMethods < totalPages) {
            currentPagePaymentMethods++;
            displayCurrentPaymentMethodPage();
        }
    }

    // Agrega los nuevos listeners
    prevBtnPaymentMethods.addEventListener('click', handlePrevClickPaymentMethods);
    nextBtnPaymentMethods.addEventListener('click', handleNextClickPaymentMethods);

    // Muestra la primera página al inicializar
    displayCurrentPaymentMethodPage();
}
/**
 * Renderiza los ítems de la lista de métodos de pago para la página actual.
 * @param {Array} dataToRender - El subconjunto de datos a mostrar en la página.
 */
function renderInventoryItems(dataToRender) {
   inventoryList.innerHTML = '';

    dataToRender.forEach((item, index) => {
        const listItem = document.createElement('div');
        listItem.className = 'list-item d-flex justify-content-between align-items-center mb-2';
        
        // Usa el índice y el operador módulo para ciclar a través de los colores
        
        
        listItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="color-box me-2" style="background-color: ${item[5]};width: 16px;
    height: 16px;
    margin-right: 5px;
    border: 1px solid #ccc;"></div>
                <span class="category fw-bold">${item[0] || 'Sin Método'}</span>
            </div>
            <span class="value">${item[1] || 0}</span>
        `;
        inventoryList.appendChild(listItem);
    });
}
/**
 * Actualiza el estado (habilitado/deshabilitado) de los controles de paginación para los métodos de pago.
 * @param {number} totalPages - El número total de páginas.
 */
function updateInventoryNavigation(totalPages) {
    if (currentPageInventory === 1) {
        prevBtnLiInventory.classList.add('disabled');
    } else {
        prevBtnLiInventory.classList.remove('disabled');
    }

    if (currentPageInventory === totalPages) {
        nextBtnLiInventory.classList.add('disabled');
    } else {
        nextBtnLiInventory.classList.remove('disabled');
    }
}

/**
 * Configura y gestiona la paginación para la lista de métodos de pago.
 * @param {Array} newData - El conjunto completo de datos a paginar.
 */
function setupInventoryPagination(newData) {
    currentInventoryData = newData;
    currentPageInventory = 1; // Reinicia la página a 1 cuando los datos cambian

    const totalPages = Math.ceil(currentInventoryData.length / itemsPerPageInventory);

    function displayCurrentInventoryPage() {
        const start = (currentPageInventory - 1) * itemsPerPageInventory;
        const end = start + itemsPerPageInventory;
        const itemsToDisplay = currentInventoryData.slice(start, end);
        renderInventoryItems(itemsToDisplay);
        updateInventoryNavigation(totalPages);
    }
    
    // Limpia los listeners antiguos para evitar duplicados
    prevBtnInventory.removeEventListener('click', handlePrevClickInventory);
    nextBtnInventory.removeEventListener('click', handleNextClickInventory);

    function handlePrevClickInventory(e) {
        e.preventDefault();
        if (currentPageInventory > 1) {
            currentPageInventory--;
            displayCurrentInventoryPage();
        }
    }

    function handleNextClickInventory(e) {
        e.preventDefault();
        if (currentPageInventory < totalPages) {
            currentPageInventory++;
            displayCurrentInventoryPage();
        }
    }

    // Agrega los nuevos listeners
    prevBtnInventory.addEventListener('click', handlePrevClickInventory);
    nextBtnInventory.addEventListener('click', handleNextClickInventory);

    // Muestra la primera página al inicializar
    displayCurrentInventoryPage();
}
document.getElementById("save-config").addEventListener("click", function () {
    const backend_ip = document.getElementById("backend-ip").value.trim();
    const backend_port = document.getElementById("backend-port").value.trim();

    if (backend_ip && backend_port) {
        const config = { backend_ip, backend_port };

        fetch("setup/save_config.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(config)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") {
                alert("Configuración guardada correctamente.");
                bootstrap.Modal.getInstance(document.getElementById('configModal')).hide();
                window.location.href='authentication/logout.php'; // Recarga la página para aplicar cambios
            } else {
                alert("Error al guardar la configuración en el servidor.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Ocurrió un error de red al intentar guardar la configuración.");
        });
    } else {
        alert("Por favor ingresa IP y puerto.");
    }
});
document.getElementById("btn-configuracion").addEventListener("click", function (e) {
    e.preventDefault(); // Evita recargar la página

    // Usar la API de Bootstrap para abrir el modal
    var configModal = new bootstrap.Modal(document.getElementById('configModal'));
    configModal.show();
});


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
        function formatCurrencyP(value) {
           const n = Number(value) || 0;
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    });
    return formatter.format(n);
        }

        // Format percentage
        function formatPercentage(value) {
            numeral.locale('en');
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
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const sectionId = this.getAttribute('data-section');
                switchSection(sectionId);
            });
        });

        // Apply date filter
        document.getElementById('applyDateFilter').addEventListener('click', function () {
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
        // Helper para formatear una fecha en el formato YYYY/MM/DD
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // getMonth() es 0-indexado
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // **NUEVA FUNCIÓN:** Actualiza los campos de input de fecha en el HTML
        function updateDateInputs() {
            const fromInput = document.getElementById('dateFrom');
            const toInput = document.getElementById('dateTo');

            if (fromInput) {
                fromInput.value = currentDateFrom;
            }
            if (toInput) {
                toInput.value = currentDateTo;
            }
        }

        // **NUEVA FUNCIÓN:** Gestiona el estado 'active' de los botones de filtro
        function setActiveFilterButton(activeButtonId) {
            // Obtén todos los botones de filtro por sus IDs
            const filterButtons = [
                document.getElementById('filterToday'),
                document.getElementById('filterWeek'),
                document.getElementById('filterMonth')
                // Agrega aquí IDs de otros botones de filtro si los tienes, por ejemplo, 'filterYear'
            ];

            filterButtons.forEach(button => {
                if (button) { // Asegúrate de que el botón existe
                    if (button.id === activeButtonId) {
                        button.classList.add('active'); // Añade la clase 'active' al botón clickeado/activo
                    } else {
                        button.classList.remove('active'); // Remueve la clase 'active' de los demás
                    }
                }
            });
        }

        // Función para cargar datos del DÍA (fecha de hoy)
        function loadTodayData() {
            setActiveFilterButton('filterToday'); // Establece 'DIA' como el botón activo
            const today = new Date();
            currentDateFrom = formatDate(today);
            currentDateTo = formatDate(today);
            updateDateInputs(); // Actualiza los campos de fecha en el HTML
            loadAllData();
            // Aquí puedes llamar a tu función principal para cargar los datos con estas fechas
            // Por ejemplo: loadYourActualDataFunction(currentDateFrom, currentDateTo);
        }

        // Función para cargar datos de la SEMANA (desde el lunes de esta semana hasta hoy)
        function loadWeekData() {
            setActiveFilterButton('filterWeek'); // Establece 'SEMANA' como el botón activo
            const today = new Date();
            const dayOfWeek = today.getDay(); // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado

            // Calcula cuántos días retroceder para llegar al lunes de esta semana
            const diffToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;

            const firstDayOfWeek = new Date(today);
            firstDayOfWeek.setDate(today.getDate() - diffToMonday);

            currentDateFrom = formatDate(firstDayOfWeek);
            currentDateTo = formatDate(today); // La fecha de fin es siempre hoy
            updateDateInputs(); // Actualiza los campos de fecha en el HTML
            loadAllData();
            // Aquí puedes llamar a tu función principal para cargar los datos
            // Por ejemplo: loadYourActualDataFunction(currentDateFrom, currentDateTo);
        }

        // Función para cargar datos del MES (desde el primer día de este mes hasta hoy)
        function loadMonthData() {
            setActiveFilterButton('filterMonth'); // Establece 'MES' como el botón activo
            const today = new Date();
            // Obtiene el primer día del mes actual
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

            currentDateFrom = formatDate(firstDayOfMonth);
            currentDateTo = formatDate(today); // La fecha de fin es siempre hoy
            updateDateInputs(); // Actualiza los campos de fecha en el HTML
            loadAllData();
            // Aquí puedes llamar a tu función principal para cargar los datos
            // Por ejemplo: loadYourActualDataFunction(currentDateFrom, currentDateTo);
        }


        // Función mejorada para crear DataTables con estilo mejorado - versión corregida
        function createDataTable(tableId, data, columns, order = [[0, 'desc']]) {

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
                            title: function () {
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
                            customize: function (doc) {
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
                                        hLineWidth: function (i, node) { return 0.5; },
                                        vLineWidth: function (i, node) { return 0.5; },
                                        hLineColor: function (i, node) { return '#aaa'; },
                                        vLineColor: function (i, node) { return '#aaa'; },
                                        paddingLeft: function (i, node) { return 4; },
                                        paddingRight: function (i, node) { return 4; },
                                        paddingTop: function (i, node) { return 3; },
                                        paddingBottom: function (i, node) { return 3; }
                                    };
                                }

                            },
                            // Usar el método de abrir en una nueva ventana directamente
                            action: function (e, dt, button, config) {
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
                    initComplete: function () {
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
                            function () {
                                $(this).css({
                                    'background-color': '#f8f9fa',
                                    'color': '#0a58ca'
                                });
                            },
                            function () {
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
                        this.api().on('draw', function () {
                            $('.dataTable tbody tr').css({
                                'transition': 'background-color 0.3s'
                            });
                        });

                        // Mejorar interacción al hover en filas
                        $('.dataTable tbody tr').hover(
                            function () {
                                $(this).css({
                                    'background-color': 'rgba(13, 110, 253, 0.05)'
                                });
                            },
                            function () {
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
                            function () {
                                $(this).css({
                                    'color': '#16181b',
                                    'background-color': '#f8f9fa'
                                });
                            },
                            function () {
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
                // Crear un objeto moment para "hoy" al inicio del día
                const todayMoment = moment().startOf('day');
                // Crear un objeto moment para la fecha "hasta" al inicio del día
                const dateToMoment = moment(currentDateTo).startOf('day');
                const dateFromMoment = moment(currentDateFrom).startOf('day');
                if (dateToMoment.isSame(todayMoment, 'day') && dateFromMoment.isSame(todayMoment, 'day')) {
                    const yesterdayMoment = moment().subtract(1, 'days').format('YYYY-MM-DD');
                    const response = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${yesterdayMoment}&DateTo=${yesterdayMoment}`);
                    const yesterdayData = await response.json();
                    if (yesterdayData && yesterdayData[0] && data && data[0]) {
                        // Asegurarse de que el elemento existe antes de actualizarlo
                        const trendElement = document.getElementById('salesTrend');
                        const yesterdaySalesElement = document.getElementById('yesterdaySales');
                        const yesterdaySales = yesterdayData[0].TotalSales;
                        const todaySales = data[0].TotalSales;
                        if (trendElement) {
                            const porcentajeCambio = ((todaySales - yesterdaySales) / yesterdaySales) * 100;
                            const salesChange = todaySales - yesterdaySales;
                            if (porcentajeCambio >= 0) {
                                yesterdaySalesElement.innerHTML = `<span class="trend-indicator trend-up" id="salesTrend">
                                                        <i class="col fas fa-long-arrow-alt-up"></i> </strong>
                                                        </span>${formatCurrencyP(yesterdaySales)}`;

                            } else {
                                yesterdaySalesElement.innerHTML = `<span class="trend-indicator trend-down" id="salesTrend">
                                                        <i class="col fas fa-long-arrow-alt-down"></i> </strong>
                                                        </span>${formatCurrencyP(yesterdaySales)}`;
                            }
                            //yesterdaySalesElement.textContent = formatCurrencyP(yesterdaySales);
                        } else {
                            console.warn("Could not retrieve sales data for today or yesterday to calculate trend.");
                            // If data isn't available, also hide the elements
                            const trendElement = document.getElementById('salesTrend');
                            const yesterdaySalesElement = document.getElementById('yesterdaySales');
                            const yesterdaySalesLabelElement = document.getElementById('yesterdaySalesLabel');

                            if (trendElement) trendElement.style.display = 'none';
                            if (yesterdaySalesElement) yesterdaySalesElement.style.display = 'none';
                            if (yesterdaySalesLabelElement) yesterdaySalesLabelElement.style.display = 'none';
                        }
                    } else {
                        console.warn("Could not retrieve sales data for today or yesterday to calculate trend.");
                        // If data isn't available, also hide the elements
                        const trendElement = document.getElementById('salesTrend');
                        const yesterdaySalesElement = document.getElementById('yesterdaySales');
                        const yesterdaySalesLabelElement = document.getElementById('yesterdaySalesLabel');

                        if (trendElement) trendElement.style.display = 'none';
                        if (yesterdaySalesElement) yesterdaySalesElement.style.display = 'none';
                        if (yesterdaySalesLabelElement) yesterdaySalesLabelElement.style.display = 'none';
                    }
                } else {

                    // If data isn't available, also hide the elements
                    const trendElement = document.getElementById('salesTrend');
                    const yesterdaySalesElement = document.getElementById('yesterdaySales');
                    const yesterdaySalesLabelElement = document.getElementById('yesterdaySalesLabel');

                    if (trendElement) trendElement.style.display = 'none';
                    if (yesterdaySalesElement) yesterdaySalesElement.style.display = 'none';
                    if (yesterdaySalesLabelElement) yesterdaySalesLabelElement.style.display = 'none';
                }

                if (data && data[0]) {
                    const salesData = data[0];
                    // Update KPI cards
                    const totalProfit = salesData.TotalSales - salesData.TotalCost;
                   
                    document.getElementById('totalProfit').textContent = formatCurrencyP(totalProfit);
                    // Update KPI cards with sales data
                    document.getElementById('totalCost').textContent = formatCurrencyP(salesData.TotalCost);
                    document.getElementById('stateTax').textContent = formatCurrencyP(salesData.TotalStateTax);
                    document.getElementById('municipalTax').textContent = formatCurrencyP(salesData.TotalCityTax);
                    document.getElementById('totalTax').textContent = formatCurrencyP(salesData.TotalStateTax + salesData.TotalCityTax);
                    document.getElementById('totalSales').textContent = formatCurrencyP(salesData.TotalSales);
                    
                    document.getElementById('totalCost').textContent = formatCurrencyP(salesData.TotalCost);
                    //TODO: document.getElementById('soldItemsLabel').textContent = formatCurrencyP(salesData.);

             

                    
                    // Calculate and display profit margin
                    const profitMargin = (salesData.TotalSales > 0) ? ((totalProfit / salesData.TotalSales) * 100) : 0;
                    document.getElementById('profitMargin').textContent = numeral(profitMargin / 100).format('0.0%');
                    
                    //DATOS PARA LA SECCION DE ESTADISTICAS
                    document.getElementById('totalTransactions').textContent = formatNumber(salesData.TransactionCount);
                    
                    document.getElementById('averageSalePerTransaction').textContent = formatCurrencyP(salesData.AverageTicketAmount);
                    document.getElementById('avgProfitPerTransaction').textContent = formatCurrencyP(salesData.AverageProfitPerTransaction);

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
                const response = await fetch(`api_proxy.php?endpoint=SaleTrendByMonth&DateFrom=${twoYearsAgo}&DateTo=${today}`);

                // Comprobar la respuesta HTTP
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }

                const data = await response.json();

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
                            const totalProfit = parseFloat(item.TotalProfit) || 0;

                            // CORRECCIÓN: Calcular la ganancia real restando el costo de las ventas
                            


                            return {
                                date: date,
                                monthYear: monthName,
                                TotalSales: totalSales,
                                TotalProfit: totalProfit // Ganancia real
                            };
                        })
                        .sort((a, b) => a.date.valueOf() - b.date.valueOf()); // Ordenar por fecha


                    // Extraer los datos para la gráfica
                    const labels = formattedData.map(item => item.monthYear);
                    const salesData = formattedData.map(item => item.TotalSales);
                    const profitData = formattedData.map(item => item.TotalProfit); // Usar la ganancia correcta

                    // Verificar que los datos de ventas y ganancia son diferentes
                    const dataIsDifferent = salesData.some((value, index) => value !== profitData[index]);
                    if (!dataIsDifferent) {
                        console.warn('ADVERTENCIA: Los datos de ventas y ganancia son idénticos. Es posible que los costos no estén siendo reportados correctamente por la API.');
                    }

                    

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
                                        label: function (context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += formatCurrencyP(context.parsed.y);
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
                                        callback: function (value) {
                                            return formatCurrencyP(value);
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


                        // Verificar si hay otros campos que puedan contener información de costos
                        for (const key in firstItem) {
                            if (key.toLowerCase().includes('cost') || key.toLowerCase().includes('costo')) {
                               
                            }
                        }

                        // Verificar si en todos los elementos los valores de ventas y costos son iguales
                        const allEqual = data.every(item =>
                            parseFloat(item.TotalSales) === parseFloat(item.TotalCost || 0)
                        );


                        // Si todos son iguales, puede ser un problema con la API
                        if (allEqual) {
                            console.warn('ADVERTENCIA: La API parece no estar devolviendo valores de costo correctos');
                        }
                    }
                })
                .catch(error => console.error('Error al depurar datos:', error));
        }

        async function loadSalesPerDayDataForChart() {
            const today = new Date();
            const dayOfWeek = today.getDay(); // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado

            // Calcula cuántos días retroceder para llegar al lunes de esta semana
            // Si es domingo (0), retrocede 6 días para llegar al lunes anterior.
            // Si es otro día, retrocede (día_actual - 1) días para llegar al lunes de la semana actual.
            const diffToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;

            const lunes = new Date(today);
            lunes.setDate(today.getDate() - diffToMonday);

            // --- CAMBIOS PARA OBTENER EL ÚLTIMO DÍA DE LA SEMANA (DOMINGO) ---
            const domingo = new Date(lunes); // Empieza desde el lunes de la semana
            domingo.setDate(lunes.getDate() + 6); // Suma 6 días para llegar al domingo
            const martes = new Date(lunes);
            martes.setDate(lunes.getDate() + 1);
            const miercoles = new Date(lunes);
            miercoles.setDate(lunes.getDate() + 2);
            const jueves = new Date(lunes);
            jueves.setDate(lunes.getDate() + 3);
            const viernes = new Date(lunes);
            viernes.setDate(lunes.getDate() + 4);
            const sabado = new Date(lunes);
            sabado.setDate(lunes.getDate() + 5);
            const responseLunes = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${formatDate(lunes)}&DateTo=${formatDate(lunes)}`);
            const dataLunes = await responseLunes.json();
            const responseMartes = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${formatDate(martes)}&DateTo=${formatDate(martes)}`);
            const dataMartes = await responseMartes.json();
            const responseMiercoles = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${formatDate(miercoles)}&DateTo=${formatDate(miercoles)}`);
            const dataMiercoles = await responseMiercoles.json();
            const responseJueves = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${formatDate(jueves)}&DateTo=${formatDate(jueves)}`);
            const dataJueves = await responseJueves.json();
            const responseViernes = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${formatDate(viernes)}&DateTo=${formatDate(viernes)}`);
            const dataViernes = await responseViernes.json();
            const responseSabado = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${formatDate(sabado)}&DateTo=${formatDate(sabado)}`);
            const dataSabado = await responseSabado.json();
            const responseDomingo = await fetch(`api_proxy.php?endpoint=SalesTotals&DateFrom=${formatDate(domingo)}&DateTo=${formatDate(domingo)}`);
            const dataDomingo = await responseDomingo.json();
            if(charts.dailySalesChart) {
                charts.dailySalesChart.destroy();
            }
            const salesDataForChart = [
                dataLunes[0]?.TotalSales || 0,
                dataMartes[0]?.TotalSales || 0,
                dataMiercoles[0]?.TotalSales || 0,
                dataJueves[0]?.TotalSales || 0,
                dataViernes[0]?.TotalSales || 0,
                dataSabado[0]?.TotalSales || 0,
                dataDomingo[0]?.TotalSales || 0
            ]; // Ejemplo de datos


        const maxSales = Math.max(...salesDataForChart);

        // 2. Encontrar el índice (posición) de ese valor máximo
        const indexOfMaxSales = salesDataForChart.indexOf(maxSales);
        // Ajustar el índice del día actual para que Lunes sea 0 y Domingo sea 6
        const indexOfToday = dayOfWeek
        // 3. Definir tus colores base y el color para el valor máximo
        const baseColor = '#D7ECFF'; // Color para la mayoría de las barras
        const highlightColor = '#369FFF'; // Color para la barra más alta (ej. un rojo-naranja)
        const highlightTodayColor = '#28a745'; // Color para la barra de hoy
        // Puedes tener más opciones de colores si quieres:
        // const highlightColor = '#28a745'; // Un verde vibrante
        // const highlightColor = '#dc3545'; // Un rojo de peligro


        // 4. Crear el array de backgroundColors dinámicamente
        const backgroundColors = salesDataForChart.map((sales, index) => {
            if (index === indexOfMaxSales) {
                return highlightColor;
            }
            if (index === indexOfToday) {
                return highlightTodayColor;
            }
            return baseColor;
        });
            //llenar el grafico de ventas por dia dailySalesChart
            const dailySalesData = {
                labels: [
                    'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'
                ],
                datasets: [{
                    label: 'Ventas Diarias',
                    data: salesDataForChart,
                    backgroundColor: backgroundColors,
                    borderColor: '#000000',
                    borderWidth: 1,
                    borderRborderRadius: 10
                }]
            };
            charts.dailySalesChart = new Chart(document.getElementById('dailySalesChart'), {
                type: 'bar',
                data: dailySalesData,
                options: {
                    responsive:true,
                     maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        async function calculateAvgSalesEstisticsPerHour() {
            const response = await fetch(`api_proxy.php?endpoint=SalesByHour&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
    const dataArray = await response.json();
   

    if (dataArray && dataArray.length !== 0) {
         // Sumar todos los 'TotalSales'
    const totalSalesSum = dataArray.reduce((sum, item) => sum + item.TotalSales, 0);

    // Contar el número de ítems
    const numberOfItems = dataArray.length;

    // Calcular el promedio
    const averageSalesPerHour = totalSalesSum / numberOfItems;


        document.getElementById('avgSalesPerHour').textContent = formatCurrencyP(averageSalesPerHour);
        // Sumar todos los 'TotalSales'
    const totalItemsSold = dataArray.reduce((sum, item) => sum + item.TotalItemsSold, 0);
    const totalTransactios = dataArray.reduce((sum, item) => sum + item.TransactionCount, 0);

    // Calcular el promedio
    const avgProductsPerHour = totalItemsSold / totalTransactios;

        document.getElementById('avgProductsPerSale').textContent = formatNumber(avgProductsPerHour);
    }
    const response1 = await fetch(`api_proxy.php?endpoint=GetEmployees`);
    const dataArray1 = await response1.json();
    if (dataArray1 && dataArray1.length !== 0) {
        document.getElementById('numEmployees').textContent = formatNumber(dataArray1.length);
    }
}

        async function loadTopCategory() {
    const response = await fetch(`api_proxy.php?endpoint=SalesByCategory&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
    const data = await response.json(); // 'data' es el array de objetos con tus categorías

    
    if (data && data.length > 0) {
        // Obtenemos la instancia de DataTables si ya existe
        // o la inicializamos por primera vez.
        let table = $('#salesByCategoryTable').DataTable();

        // 2. Destruir la instancia existente si ya fue inicializada
        // (La opción "destroy: true" en la inicialización lo haría si volvieras a llamar a .DataTable())
        // Pero para ser más explícito y controlar la actualización de datos:
        if ($.fn.DataTable.isDataTable('#salesByCategoryTable')) {
             table.destroy(); // Destruye la instancia anterior
        }
        
        // 3. Reinicializar DataTables con los NUEVOS datos
        $('#salesByCategoryTable').DataTable({
            "data": data, // <-- Pasa tus datos directamente aquí
            "pageLength": 5,
            "lengthMenu": [[5, 10, 20, 50, -1], [5, 10, 20, 50, "Todos"]],
            "searching": false,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
            },
            "dom": '<"row"<"col-sm-12"tr>>' +
                   '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"i>>' +
                   '<"row"<"col-sm-12"p>>',
            "destroy": true, // Importante: Destruye la instancia anterior si existe
            "columns": [ // <-- ¡Esto es crucial! Define tus columnas y sus fuentes de datos
                { "data": "CategoryName", "defaultContent": "Sin Categoría" },
                { "data": "TotalSales", "render": function(data, type, row) { return formatCurrencyP(data || 0); }, "className": "text-end" },
                { "data": "TotalProfit", "render": function(data, type, row) { return formatCurrencyP(data || 0); }, "className": "text-end" }
            ]
        });
        
    } else {
        // Manejar el caso donde no hay datos
        if ($.fn.DataTable.isDataTable('#salesByCategoryTable')) {
            $('#salesByCategoryTable').DataTable().clear().draw(); // Limpiar la tabla si no hay datos
        } else {
            // Si no hay datos y la tabla no ha sido inicializada, puedes inicializarla vacía
            $('#salesByCategoryTable').DataTable({
                "data": [], // Inicializa con un array vacío
                "pageLength": 5,
                "lengthMenu": [[5, 10, 20, 50, -1], [5, 10, 20, 50, "Todos"]],
                "searching": false,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
                },
                "dom": '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"i>>' +
                       '<"row"<"col-sm-12"p>>',
                "destroy": true,
                 "columns": [
                    { "data": "CategoryName", "defaultContent": "Sin Categoría" },
                    { "data": "TotalSales", "render": function(data, type, row) { return formatCurrencyP(data || 0); }, "className": "text-end" },
                    { "data": "TotalProfit", "render": function(data, type, row) { return formatCurrencyP(data || 0); }, "className": "text-end" }
                ]
            });
        }
    }
}
async function loadLowInventory() {
    const response = await fetch(`api_proxy.php?endpoint=LowLevelItems`); // El endpoint LowLevelItems parece no necesitar fechas en este caso
    const data = await response.json(); // 'data' es el array de objetos con tus ítems de bajo inventario

    if (data && data.length > 0) {
        // Reinicializar DataTables con los NUEVOS datos
        $('#lowInventoryTable').DataTable({
            "data": data, // <-- Pasa tus datos directamente aquí
            "pageLength": 5, // Mostrar 5 elementos por página
            "lengthMenu": [[5, 10, 20, 50, -1], [5, 10, 20, 50, "Todos"]], // Opciones de cuántos elementos mostrar
            "searching": false, // Puedes cambiar a true si quieres habilitar la búsqueda
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json" // Idioma español
            },
            "dom": '<"row"<"col-sm-12"tr>>' +       // La tabla misma
                   '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"i>>' + // Selector e información
                   '<"row"<"col-sm-12"p>>',          // Paginación
            "destroy": true, // Importante: Destruye la instancia anterior si existe
            "columns": [ // <-- Define tus columnas y sus fuentes de datos
                { "data": "ProductCode", "defaultContent": "" },
                { "data": "ProductName", "defaultContent": "" },
                { "data": "CurrentStock", "className": "text-end", "defaultContent": "0" } // Asegura que la cantidad se alinee a la derecha
            ]
        });
    } else {
        // Manejar el caso donde no hay datos
        if ($.fn.DataTable.isDataTable('#lowInventoryTable')) {
            $('#lowInventoryTable').DataTable().clear().draw(); // Limpiar la tabla si no hay datos
        } else {
            // Si no hay datos y la tabla no ha sido inicializada, puedes inicializarla vacía
            $('#lowInventoryTable').DataTable({
                "data": [], // Inicializa con un array vacío
                "pageLength": 5,
                "lengthMenu": [[5, 10, 20, 50, -1], [5, 10, 20, 50, "Todos"]],
                "searching": false,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json"
                },
                "dom": '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"i>>' +
                       '<"row"<"col-sm-12"p>>',
                "destroy": true,
                "columns": [
                    { "data": "ProductCode", "defaultContent": "" },
                    { "data": "ProductName", "defaultContent": "" },
                    { "data": "CurrentStock", "className": "text-end", "defaultContent": "0" }
                ]
            });
        }
    }
}
        // Load all data
        async function loadAllData() {
            toggleLoading(true);

            try {
                const lastUpdateTime = document.getElementById('last-update-time');
                lastUpdateTime.textContent = `Hoy ${new Date().toLocaleTimeString()}`;
                // Load overview section data
                await loadCompanyInfo();
                await loadSalesTotals();
                await loadTopCategory();
                await loadLowInventory()
                await calculateAvgSalesEstisticsPerHour();
                await loadSalesPerDayDataForChart();
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
                 const lastUpdateTime = document.getElementById('last-update-time');
                lastUpdateTime.textContent = `Hoy ${new Date().toLocaleTimeString()}`;
                await loadSalesTotals();
                await loadTopCategory();
                await loadLowInventory()
                await calculateAvgSalesEstisticsPerHour();
                await loadSalesPerDayDataForChart();
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
            try{
                
                const sidebarTest = document.getElementById('sidebarTest');
                sidebarTest.style.position = 'static';
            }catch(error){
                console.error('Error al fijar la posición de la barra lateral:', error);
            }
            
            try {

                // Cargar todos los datos
                await loadAllData();
                document.getElementById('filterToday').addEventListener('click', function () {
                    loadTodayData();
                });
                document.getElementById('filterWeek').addEventListener('click', function () {
                    loadWeekData();
                });
                document.getElementById('filterMonth').addEventListener('click', function () {
                    loadMonthData();
                });
                // Refresh buttons event listeners
                document.getElementById('refreshOverview').addEventListener('click', function () {
                    loadOverviewData();
                });

                document.getElementById('refreshSales').addEventListener('click', function () {
                    loadSalesData();
                });

                document.getElementById('refreshProducts').addEventListener('click', function () {
                    loadProductsData();
                });

                document.getElementById('refreshInventory').addEventListener('click', function () {
                    loadInventoryData();
                });

                // Apply product filters
                document.getElementById('applyProductFilters').addEventListener('click', function () {
                    loadTopProducts();
                });
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
                                backgroundColor: _backgroundColor,
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
                                        label: function (context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = Math.round((context.parsed * 100) / total);
                                            return `${context.label}: ${formatCurrencyP(context.parsed)} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    currentDataDepartments = data;
                    setupPagination(data);

                    // Update the department sales table
                    const tableData = data.map(item => [
                        item.Department || 'Sin Departamento',
                        formatCurrencyP(item.TotalSales || 0),
                        formatCurrencyP(item.TotalProfit || 0),
                        formatPercentage(item.ProfitMarginPercentage || 0),
                        formatNumber(item.InvoiceCount || 0),
                        formatNumber(item.QuantitySold || 0),
                        formatCurrencyP(item.AveragePrice || 0)
                    ]);

                    const departmentColumns = [
                        { title: "Departamento", data: 0 },
                        { title: "Ventas", data: 1 },
                        { title: "Ganancia", data: 2 },
                        { title: "Margen", data: 3 },
                        { title: "Facturas", data: 4 },
                        { title: "Unidades Vendidas", data: 5 },
                        { title: "Precio Promedio", data: 6 }
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
                const response = await fetch(`api_proxy.php?endpoint=SalesByHour&DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`);
                const data = await response.json();

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

                        if (!isNaN(hour) && hour >= 0 && hour < 24) {
                            salesByHour[hour] = parseFloat(item.TotalSales) || 0;
                            transactionsByHour[hour] = parseInt(item.TransactionCount) || 0;
                        }
                    });


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
                                        callback: function (value) {
                                            return formatCurrencyP(value);
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
                                        label: function (context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                if (context.dataset.label === 'Ventas') {
                                                    label += formatCurrencyP(context.parsed.y);
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
                                        label: function (context) {
                                            const index = context.dataIndex;
                                            return `${context.label}: ${formatCurrencyP(context.parsed)} (${percentages[index]}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    currentPaymentMethodsData = methods;
                    setupPaymentMethodPagination(methods);
                    // Update payment methods table
                    const tableData = data.map(item => [
                        moment(item.SaleDate).format('DD/MM/YYYY'),
                        formatCurrencyP(item.TotalSales || 0),
                        formatCurrencyP(item.CashPayments || 0),
                        formatCurrencyP(item.CreditCardPayments || 0),
                        formatCurrencyP(item.DebitCardPayments || 0),
                        formatCurrencyP(item.CheckPayments || 0),
                        formatCurrencyP(item.AthMovilPayments || 0)
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

                    // Calculate total inventory value
                    let totalCostValue = 0;
                    let totalPriceValue = 0;

                    // Extract data for chart and table
                    const departmentNames = [];
                    const departmentValues = [];
                    const tableData = [];

                    // Process inventory data
                    let indexInventory = 0;
                    data.forEach(item => {
                        // Skip "GRAND TOTAL" entries for the chart (case insensitive check)
                        if (item.Department && !item.Department.toUpperCase().includes('GRAND TOTAL')) {
                            const value = Math.abs(parseFloat(item.TotalInventoryValue) || 0);
                            
                            departmentNames.push(item.Department);
                            // Use absolute value to handle negative values
                            
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
                                formatCurrencyP(costValue),
                                formatCurrencyP(priceValue),
                                formatCurrencyP(potentialProfit),
                                '0%', // Will calculate after getting total
                                _backgroundColor[indexInventory++ % _backgroundColor.length], // Unique index for each row
                            ]);
                        }
                    });
                    // Update inventory value in overview section
                    const inventoryValueElement = document.getElementById('inventoryValue');
                    if (inventoryValueElement) {
                        inventoryValueElement.textContent = formatCurrencyP(totalCostValue);
                        
                    } else {
                        console.warn("Elemento inventoryValue no encontrado");
                    }

                    // Calculate inventory turnover (this would typically come from API)
                    // For demo, we'll use a random value between 4 and 12
                    //Elemento de Rotacion del Inventario
                   /*  const inventoryTurnoverElement = document.getElementById('inventoryTurnover');
                    if (inventoryTurnoverElement) {
                        const inventoryTurnover = (4 + Math.random() * 8).toFixed(1);
                        inventoryTurnoverElement.textContent = `${inventoryTurnover}x`;
                    } else {
                        console.warn("Elemento inventoryTurnover no encontrado");
                    } */
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
                    const inventoryValueChart = document.getElementById('inventoryValueChart1');
                    if (chartElement && inventoryValueChart) {
                        if (charts.inventoryValueChart && charts.inventoryValueChart1) {
                            charts.inventoryValueChart.destroy();
                            charts.inventoryValueChart1.destroy();
                        }

                        // Colors for the chart
                        const colors = _backgroundColor;

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
                                                label: function (context) {
                                                    const value = context.parsed;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percentage = ((value / total) * 100).toFixed(1);
                                                    return `${context.label}: ${formatCurrencyP(value)} (${percentage}%)`;
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
                        let InventoryNamesTemp = [];
                        let InventoryValuesTemp = [];
                        departmentValues.forEach((value, index) => {
                            if(value > 0){
                                InventoryNamesTemp.push(departmentNames[index]);
                                InventoryValuesTemp.push(value);
                            }
                        })
                        // Only create chart if we have data
                        if (InventoryNamesTemp.length > 0 && InventoryValuesTemp.some(v => v > 0)) {
                            const ctx = inventoryValueChart.getContext('2d');
                            charts.inventoryValueChart1 = new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: InventoryNamesTemp,
                                    datasets: [{
                                        data: InventoryValuesTemp,
                                        backgroundColor: _backgroundColor.slice(0, InventoryNamesTemp.length),
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
                                                label: function (context) {
                                                    const value = context.parsed;
                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    const percentage = ((value / total) * 100).toFixed(1);
                                                    return `${context.label}: ${formatCurrencyP(value)} (${percentage}%)`;
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
                    
                    currentInventoryData = [];
                    tableData.forEach(row => {
                        if(row[1]!=="$0.00"){
                            currentInventoryData.push(row);
                        }
                    })
                    setupInventoryPagination(currentInventoryData);
                    
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

                    

                    if (totalPriceValueElement) {
                        totalPriceValueElement.textContent = formatCurrencyP(totalPriceValue);
                    } else {
                        console.warn("Elemento totalPriceValue no encontrado");
                    }

                    if (totalPotentialProfitElement) {
                        totalPotentialProfitElement.textContent = formatCurrencyP(totalPriceValue - totalCostValue);
                    } else {
                        console.warn("Elemento totalPotentialProfit no encontrado");
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
                        item.CategoryName || 'Sin Categoría',
                        formatCurrencyP(item.TotalSales || 0),
                        formatCurrencyP(item.TotalProfit || 0),
                        formatPercentage(item.ProfitMarginPercentage || 0),
                        formatNumber(item.InvoiceCount || 0),
                        formatNumber(item.QuantitySold || 0),
                        formatCurrencyP(item.AveragePrice || 0)
                    ]);

                    const categoryColumns = [
                        { title: "Categoría", data: 0 },
                        { title: "Ventas", data: 1 },
                        { title: "Ganancia", data: 2 },
                        { title: "Margen", data: 3 },
                        { title: "Facturas", data: 4 },
                        { title: "Unidades Vendidas", data: 5 },
                        { title: "Precio Promedio", data: 6 }
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
                        formatCurrencyP(item.Price || 0),
                        formatCurrencyP(item.Cost || 0),
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
                    $('#lowLevelItemsTable').on('draw.dt', function () {
                        $('#lowLevelItemsTable tbody tr').each(function () {
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
        // Función para llenar los filtros dinámicamente
function llenarFiltros(productos) {
    const categoryFilterObj = document.getElementById('categoryFilter');
const departmentFilterObj = document.getElementById('departmentFilter');
  const categorias = [...new Set(productos.map(p => p.Category))];
  const departamentos = [...new Set(productos.map(p => p.Department))];
  const proveedores = [...new Set(productos.map(p => p.ProviderName))];

  // Llenar categorías
  categorias.forEach(cat => {
    const option = document.createElement('option');
    option.value = cat;
    option.textContent = cat;
    categoryFilterObj.appendChild(option);
  });

  // Llenar departamentos
  departamentos.forEach(dep => {
    const option = document.createElement('option');
    option.value = dep;
    option.textContent = dep;
    departmentFilterObj.appendChild(option);
  });

  
}
        // Load top selling products
        async function loadTopProducts() {
            try {
                toggleLoading(true);
                const categoryFilterObj = document.getElementById('categoryFilter');
                const departmentFilterObj = document.getElementById('departmentFilter');
                // Get filter values
                const category = document.getElementById('categoryFilter')?.value || '';
                const department = document.getElementById('departmentFilter')?.value || '';

                // Build query parameters
                let queryParams = `DateFrom=${currentDateFrom}&DateTo=${currentDateTo}`;
                if (category) queryParams += `&Category=${encodeURIComponent(category)}`;
                if (department) queryParams += `&Department=${encodeURIComponent(department)}`;

                const response = await fetch(`api_proxy.php?endpoint=TopSellProducts&${queryParams}`);
                const data = await response.json();
                llenarFiltros(data);

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
                    // Update top products table
                    const tableData = data.map(item => [
                        item.ProductCode || '',
                        item.ProductName || '',
                        item.Department || '',
                        item.Category || '',
                        safeToLocaleString(item.TotalQuantitySold),
                        formatCurrencyP(item.TotalSales || 0),
                        formatCurrencyP((item.TotalSales || 0) - (item.TotalCost || 0)),
                        formatCurrencyP(item.AveragePrice || 0),
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
                                                    label: function (context) {
                                                        return `Ventas: ${formatCurrencyP(context.parsed.x)}`;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            x: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function (value) {
                                                        return formatCurrencyP(value);
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
                                                    label: function (context) {
                                                        return `Ganancia: ${formatCurrencyP(context.parsed.x)}`;
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            x: {
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function (value) {
                                                        return formatCurrencyP(value);
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
            }, 350); // 350ms para dar tiempo a que terminen las transiciones CSS
        }

        // Asignar la función a eventos clave
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Al cambiar el estado del sidebar
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', optimizeDashboardLayout);
            }

            // 2. Al cambiar entre secciones del dashboard
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function () {
                    // Pequeño retraso para asegurar que la sección ya esté visible
                    setTimeout(optimizeDashboardLayout, 200);
                });
            });

            // 3. Al cambiar el tamaño de la ventana
            let resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(optimizeDashboardLayout, 250);
            });

            // 4. Al cargar inicialmente la página
            setTimeout(optimizeDashboardLayout, 500);
        });

    </script>

    <script>
        // Script simple para manejar cambios del sidebar
        document.addEventListener('DOMContentLoaded', function () {
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
            menuToggle.addEventListener('click', function () {
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
                setTimeout(function () {
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
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');

            // Crear overlay para móvil si no existe
            if (!document.querySelector('.sidebar-overlay')) {
                const overlay = document.createElement('div');
                overlay.classList.add('sidebar-overlay');
                document.body.appendChild(overlay);

                overlay.addEventListener('click', function () {
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
                mobileMenuToggle.addEventListener('click', function (e) {
                    e.preventDefault();

                    // En móvil simplemente mostramos/ocultamos el sidebar
                    sidebar.classList.toggle('active');

                    // Mostrar/ocultar overlay
                    if (overlay) {
                        overlay.classList.toggle('active');
                    }
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
                setTimeout(function () {
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
                link.addEventListener('click', function () {
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
        document.addEventListener('DOMContentLoaded', function () {
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
                } else {
                }
            }

            // Activar tooltips al cargar la página
            updateTooltips();

            // Actualizar tooltips cuando cambia el estado del sidebar
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function () {
                    // Esperar a que termine la transición
                    setTimeout(updateTooltips, 350);
                });
            }

            // Actualizar tooltips cuando cambia el tamaño de la ventana
            window.addEventListener('resize', function () {
                clearTimeout(window.resizeTimer);
                window.resizeTimer = setTimeout(updateTooltips, 300);
            });

            // Ocultar tooltip al hacer clic en un enlace del menu
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', function () {
                    const tooltip = bootstrap.Tooltip.getInstance(this);
                    if (tooltip) {
                        tooltip.hide();
                    }
                });
            });
        });
        
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/clients.js"></script>
    <script src="js/maintenance.js"></script>
    <script src="../js/sidebar.js"></script>
    <?php include 'scripts.php'; ?>
</body>

</html>