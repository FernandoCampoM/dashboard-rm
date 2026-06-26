<?php
session_start();
require_once 'config.php';
require_once 'setup/config_functions.php';

$config = get_configBackend();
if (!$config) {
    header("Location: setup/setup-backend.php");
    exit;
}
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: authentication/login.php');
    exit;
}

$userID       = $_SESSION['UserID']       ?? '';
$username     = $_SESSION['Username']     ?? '';
$employeeName = $_SESSION['EmployeeName'] ?? $username;
$accessLevel  = $_SESSION['AccessLevel']  ?? 0;
$companyName  = $_SESSION['InfoCompany']['Name'] ?? 'RetailManager';
$accessLabel  = $accessLevel == 1 ? 'Administrador' : 'Usuario';
$userInitials = strtoupper(substr($employeeName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($companyName) ?> — Dashboard</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <!-- DataTables 1.13 Bootstrap 5 CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <!-- Schedule CSS (preserved) -->
  <link rel="stylesheet" href="css/schedule.css">
  <!-- New design system -->
  <link rel="stylesheet" href="css/theme.css">
</head>
<body>

<!-- Loader -->
<div id="rm-loader" class="rm-loader"><div class="rm-loader__spinner"></div></div>

<!-- Mobile overlay -->
<div id="rm-sidebar-overlay" class="rm-sidebar-overlay"></div>

<!-- ═══════════════════ SIDEBAR ═══════════════════ -->
<aside id="rm-sidebar" class="rm-sidebar">
  <div class="rm-sidebar__brand">
    <div class="rm-sidebar__icon"><i class="fas fa-store"></i></div>
    <div>
      <div class="rm-sidebar__title"><?= htmlspecialchars($companyName) ?></div>
      <div class="rm-sidebar__subtitle">RetailManager Dashboard</div>
    </div>
  </div>

  <nav class="rm-sidebar__nav">
    <div class="rm-nav-section">Principal</div>
    <a class="rm-nav-item rm-nav-item--active" data-section="home-section">
      <i class="fas fa-gauge-high"></i><span>Pulso del Negocio</span>
    </a>
    <a class="rm-nav-item" data-section="sales-section">
      <i class="fas fa-chart-line"></i><span>Análisis de Ventas</span>
    </a>

    <div class="rm-nav-section">Operaciones</div>
    <a class="rm-nav-item" data-section="inventory-section">
      <i class="fas fa-boxes-stacked"></i><span>Inventario</span>
    </a>
    <a class="rm-nav-item" data-section="products-section">
      <i class="fas fa-tag"></i><span>Productos</span>
    </a>
    <a class="rm-nav-item" data-section="clients-section">
      <i class="fas fa-users"></i><span>Clientes</span>
    </a>
    <a class="rm-nav-item" data-section="reports-section">
      <i class="fas fa-file-chart-column"></i><span>Reportes</span>
    </a>
    <a class="rm-nav-item" id="horario-link" data-section="schedule-section">
      <i class="fas fa-calendar-alt"></i><span>Horario</span>
    </a>
  </nav>

  <div class="rm-sidebar__footer">
    <div class="rm-user-avatar"><?= htmlspecialchars($userInitials) ?></div>
    <div class="rm-user-info">
      <div class="rm-user-name"><?= htmlspecialchars($employeeName) ?></div>
      <div class="rm-user-role"><?= htmlspecialchars($accessLabel) ?></div>
    </div>
    <a href="authentication/logout.php" class="rm-logout-btn" title="Cerrar sesión"><i class="fas fa-right-from-bracket"></i></a>
  </div>
</aside>

<!-- ═══════════════════ TOPBAR ═══════════════════ -->
<header id="rm-topbar" class="rm-topbar">
  <button id="rm-sidebar-toggle" class="rm-topbar__toggle" title="Toggle menú">
    <i class="fas fa-bars"></i>
  </button>
  <span class="rm-topbar__title" id="topbar-section-title">Pulso del Negocio</span>
  <div class="rm-topbar__right">
    <span class="rm-topbar__date" id="topbar-date"></span>
    <span class="rm-topbar__company d-none d-md-inline"><?= htmlspecialchars($companyName) ?></span>
    <a href="setup/setup-backend.php?edit" class="btn btn-sm btn-outline-secondary" title="Configuración del servidor">
      <i class="fas fa-cog"></i>
    </a>
    <a href="authentication/logout.php" class="btn btn-sm btn-outline-danger">
      <i class="fas fa-right-from-bracket me-1"></i><span class="d-none d-sm-inline">Salir</span>
    </a>
  </div>
  <span id="rm-user-id" style="display:none"><?= htmlspecialchars($userID) ?></span>
</header>

<!-- ═══════════════════ MAIN ═══════════════════ -->
<main id="rm-main" class="rm-main">

  <!-- ─── HOME SECTION ──────────────────────────────────────── -->
  <section id="home-section" class="rm-section rm-section--active">
    <div class="rm-page-header d-flex align-items-center justify-content-between">
      <div>
        <h4>Pulso del Negocio</h4>
        <p>Resumen de hoy comparado con la misma semana pasada</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <small class="text-muted" id="home-cache-ts"></small>
        <button class="btn btn-sm btn-outline-secondary" id="home-refresh-btn">
          <i class="fas fa-sync-alt me-1"></i>Actualizar
        </button>
      </div>
    </div>

    <!-- KPI row -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4 col-xl">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-cash-register"></i></div>
          <div class="rm-kpi-label">Ventas de Hoy</div>
          <div class="rm-kpi-value" id="kpi-sales-value">—</div>
          <div class="rm-kpi-delta" id="kpi-sales-delta"></div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-chart-line"></i></div>
          <div class="rm-kpi-label">Ganancia Bruta</div>
          <div class="rm-kpi-value" id="kpi-profit-value">—</div>
          <div class="rm-kpi-delta" id="kpi-profit-delta"></div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">Margen Bruto</div>
          <div class="rm-kpi-value" id="kpi-margin-value">—</div>
          <div class="rm-kpi-delta" id="kpi-margin-delta"></div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-receipt"></i></div>
          <div class="rm-kpi-label">Ticket Promedio</div>
          <div class="rm-kpi-value" id="kpi-ticket-value">—</div>
          <div class="rm-kpi-delta" id="kpi-ticket-delta"></div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-danger-soft"><i class="fas fa-shopping-cart"></i></div>
          <div class="rm-kpi-label">Transacciones</div>
          <div class="rm-kpi-value" id="kpi-txcount-value">—</div>
          <div class="rm-kpi-delta" id="kpi-txcount-delta"></div>
        </div>
      </div>
    </div>

    <!-- Trend + Payment donut -->
    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-chart-area me-2 text-primary"></i>Ventas y Ganancia — Últimos 30 Días</div>
          <div class="card-body"><div class="rm-chart-wrap"><canvas id="home-trend-chart"></canvas></div></div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-credit-card me-2 text-primary"></i>Métodos de Pago (Hoy)</div>
          <div class="card-body d-flex flex-column align-items-center justify-content-center">
            <div class="rm-chart-wrap w-100" style="height:200px;"><canvas id="home-payment-donut"></canvas></div>
            <div id="home-payment-legend" class="mt-2 w-100 text-center" style="font-size:.78rem;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top 5 + Low stock -->
    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-trophy me-2 text-warning"></i>Top 5 Productos de Hoy</div>
          <div class="card-body p-0">
            <table class="table rm-table mb-0">
              <thead><tr><th>Producto</th><th class="text-end">Unidades</th><th class="text-end">Ventas</th><th class="text-end">Margen</th></tr></thead>
              <tbody id="home-top5-body"><tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card rm-card h-100">
          <div class="rm-card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Stock Bajo</span>
            <span class="badge bg-danger rounded-pill" id="home-lowstock-badge">0</span>
          </div>
          <div class="card-body p-0">
            <ul class="list-unstyled rm-alert-list mb-0" id="home-lowstock-list">
              <li class="text-center text-muted py-3">Cargando...</li>
            </ul>
          </div>
          <div class="card-footer bg-transparent border-top text-end py-2">
            <a href="#" class="rm-link" data-section="inventory-section">Ver todo el inventario →</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Hourly chart -->
    <div class="row g-3">
      <div class="col-12">
        <div class="card rm-card">
          <div class="rm-card-header"><i class="fas fa-clock me-2 text-primary"></i>Ventas por Hora (Hoy)</div>
          <div class="card-body"><div class="rm-chart-wrap" style="height:240px;"><canvas id="home-hourly-chart"></canvas></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── SALES SECTION ─────────────────────────────────────── -->
  <section id="sales-section" class="rm-section">
    <div class="rm-page-header"><h4>Análisis de Ventas</h4><p>Selecciona un período para ver el rendimiento de tu negocio</p></div>

    <!-- Period toolbar -->
    <div class="d-flex align-items-center gap-2 flex-wrap mb-4">
      <div class="btn-group">
        <button class="btn btn-outline-secondary rm-period-btn active" data-period="today">Hoy</button>
        <button class="btn btn-outline-secondary rm-period-btn" data-period="week">Esta Semana</button>
        <button class="btn btn-outline-secondary rm-period-btn" data-period="month">Este Mes</button>
        <button class="btn btn-outline-secondary rm-period-btn" data-period="custom">Personalizado</button>
      </div>
      <div id="sales-custom-range" class="d-none d-flex align-items-center gap-2">
        <input type="date" id="sales-date-from" class="form-control form-control-sm" style="width:150px;">
        <span class="text-muted small">a</span>
        <input type="date" id="sales-date-to" class="form-control form-control-sm" style="width:150px;">
        <button class="btn btn-sm btn-primary" id="sales-apply-btn">Aplicar</button>
      </div>
      <small class="text-muted ms-2" id="sales-cache-ts"></small>
      <button class="btn btn-sm btn-outline-secondary ms-2" id="sales-refresh-btn"><i class="fas fa-sync-alt"></i></button>
    </div>

    <!-- 6 KPI cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Ventas</div>
          <div class="rm-kpi-value" id="s-kpi-sales">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-chart-line"></i></div>
          <div class="rm-kpi-label">Ganancia</div>
          <div class="rm-kpi-value" id="s-kpi-profit">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">Margen</div>
          <div class="rm-kpi-value" id="s-kpi-margin">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-receipt"></i></div>
          <div class="rm-kpi-label">Ticket Prom.</div>
          <div class="rm-kpi-value" id="s-kpi-ticket">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-danger-soft"><i class="fas fa-tag"></i></div>
          <div class="rm-kpi-label">Descuentos</div>
          <div class="rm-kpi-value" id="s-kpi-discounts">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-shopping-cart"></i></div>
          <div class="rm-kpi-label">Transacciones</div>
          <div class="rm-kpi-value" id="s-kpi-txcount">—</div>
        </div>
      </div>
    </div>

    <!-- Charts row 1 -->
    <div class="row g-3 mb-4">
      <div class="col-lg-7">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-chart-line me-2 text-primary"></i>Tendencia Mensual (12 meses)</div>
          <div class="card-body"><div class="rm-chart-wrap"><canvas id="sales-trend-chart"></canvas></div></div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-building me-2 text-primary"></i>Ventas por Departamento</div>
          <div class="card-body"><div class="rm-chart-wrap"><canvas id="sales-dept-chart"></canvas></div></div>
        </div>
      </div>
    </div>

    <!-- Charts row 2 -->
    <div class="row g-3 mb-4">
      <div class="col-lg-6">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-layer-group me-2 text-primary"></i>Top 10 Categorías (Ventas vs Ganancia)</div>
          <div class="card-body"><div class="rm-chart-wrap"><canvas id="sales-category-chart"></canvas></div></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-credit-card me-2 text-primary"></i>Métodos de Pago por Día</div>
          <div class="card-body"><div class="rm-chart-wrap"><canvas id="sales-payment-chart"></canvas></div></div>
        </div>
      </div>
    </div>

    <!-- Top products table -->
    <div class="row g-3">
      <div class="col-12">
        <div class="card rm-card">
          <div class="rm-card-header"><i class="fas fa-trophy me-2 text-warning"></i>Top 20 Productos Más Vendidos</div>
          <div class="card-body">
            <table id="sales-top-products-table" class="table rm-datatable w-100">
              <thead><tr>
                <th>Código</th><th>Producto</th><th>Departamento</th><th>Categoría</th>
                <th class="text-end">Unidades</th><th class="text-end">Ventas</th>
                <th class="text-end">Ganancia</th><th class="text-end">Margen</th>
              </tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── INVENTORY SECTION ─────────────────────────────────── -->
  <section id="inventory-section" class="rm-section">
    <div class="rm-page-header d-flex align-items-center justify-content-between">
      <div><h4>Inventario</h4><p>Estado actual de tu inventario — lo que tienes y lo que necesitas</p></div>
      <button class="btn btn-sm btn-outline-secondary" id="inv-refresh-btn"><i class="fas fa-sync-alt me-1"></i>Actualizar</button>
    </div>

    <!-- 4 KPI cards -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-cubes"></i></div>
          <div class="rm-kpi-label">Total SKUs</div>
          <div class="rm-kpi-value" id="inv-kpi-skus">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Valor al Detal</div>
          <div class="rm-kpi-value" id="inv-kpi-retail">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-triangle-exclamation"></i></div>
          <div class="rm-kpi-label">Artículos en Riesgo</div>
          <div class="rm-kpi-value" id="inv-kpi-cost">—</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">% SKUs en Riesgo</div>
          <div class="rm-kpi-value" id="inv-kpi-margin">—</div>
        </div>
      </div>
    </div>

    <!-- Dept chart + Risk gauge -->
    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-building me-2 text-primary"></i>Valor de Inventario por Departamento</div>
          <div class="card-body"><div class="rm-chart-wrap"><canvas id="inv-dept-chart"></canvas></div></div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card rm-card h-100">
          <div class="rm-card-header"><i class="fas fa-triangle-exclamation me-2 text-danger"></i>Productos en Riesgo</div>
          <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
            <div class="rm-gauge-wrap w-100" style="max-width:200px;">
              <canvas id="inv-risk-gauge" height="110"></canvas>
              <div class="rm-gauge-center">
                <div class="rm-gauge-value" id="inv-risk-pct">—</div>
                <div class="rm-gauge-label">de SKUs en riesgo</div>
              </div>
            </div>
            <div class="mt-3 text-center">
              <span class="badge bg-danger me-2" id="inv-critical-count">0 críticos</span>
              <span class="badge bg-warning text-dark" id="inv-warning-count">0 advertencia</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Low stock table -->
    <div class="row g-3 mb-4">
      <div class="col-12">
        <div class="card rm-card">
          <div class="rm-card-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-arrow-down me-2 text-danger"></i>Productos con Stock Bajo</span>
            <span class="badge bg-secondary" id="inv-lowstock-count">0</span>
          </div>
          <div class="card-body">
            <table id="inv-lowstock-table" class="table rm-datatable w-100">
              <thead><tr>
                <th>Código</th><th>Producto</th><th>Departamento</th><th>Categoría</th>
                <th class="text-end">Stock Actual</th><th class="text-end">Mínimo</th>
                <th class="text-end">Máximo</th><th class="text-end">Reorden Sug.</th><th>Proveedor</th>
              </tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Slow movers table -->
    <div class="row g-3">
      <div class="col-12">
        <div class="card rm-card">
          <div class="rm-card-header"><i class="fas fa-snooze me-2 text-muted"></i>Productos de Baja Rotación</div>
          <div class="card-body">
            <table id="inv-slow-table" class="table rm-datatable w-100">
              <thead><tr>
                <th>Código</th><th>Producto</th><th>Departamento</th><th>Categoría</th>
                <th class="text-end">Unidades Vend.</th><th class="text-end">Ventas</th>
                <th class="text-end">Margen</th><th class="text-end">Stock Actual</th>
              </tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── PRODUCTS SECTION ──────────────────────────────────── -->
  <section id="products-section" class="rm-section">
    <div class="rm-page-header"><h4>Productos</h4><p>Rendimiento, catálogo y búsqueda de productos</p></div>

    <!-- Tab nav -->
    <ul class="nav nav-tabs rm-tabs mb-4" id="products-tabs">
      <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#prod-tab-performance"><i class="fas fa-chart-bar me-2"></i>Rendimiento</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#prod-tab-catalog"><i class="fas fa-list me-2"></i>Catálogo</a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#prod-tab-search"><i class="fas fa-search me-2"></i>Búsqueda</a></li>
    </ul>

    <div class="tab-content">
      <!-- Performance tab -->
      <div class="tab-pane fade show active" id="prod-tab-performance">
        <div class="row g-3 mb-4">
          <div class="col-lg-6">
            <div class="card rm-card h-100">
              <div class="rm-card-header"><i class="fas fa-arrow-up me-2 text-success"></i>Top 10 Más Vendidos (Este Mes)</div>
              <div class="card-body"><div class="rm-chart-wrap"><canvas id="prod-top-chart"></canvas></div></div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card rm-card h-100">
              <div class="rm-card-header"><i class="fas fa-arrow-down me-2 text-danger"></i>10 de Menor Rotación (Este Mes)</div>
              <div class="card-body"><div class="rm-chart-wrap"><canvas id="prod-bottom-chart"></canvas></div></div>
            </div>
          </div>
        </div>
        <!-- Movement chart -->
        <div class="row g-3">
          <div class="col-12">
            <div class="card rm-card">
              <div class="rm-card-header"><i class="fas fa-wave-square me-2 text-primary"></i>Movimiento de Producto</div>
              <div class="card-body">
                <div class="d-flex gap-2 mb-3 position-relative">
                  <div class="position-relative" style="max-width:420px; width:100%;">
                    <input type="text" id="prod-movement-code" class="form-control" placeholder="Código, barcode o nombre" autocomplete="off">
                    <div id="prod-movement-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:1050; max-height:260px; overflow:auto;"></div>
                  </div>
                  <button class="btn btn-primary" id="prod-movement-btn"><i class="fas fa-chart-bar me-1"></i>Ver Movimiento</button>
                </div>
                <div id="prod-movement-wrap" class="d-none">
                  <div class="rm-chart-wrap" style="height:220px;"><canvas id="prod-movement-chart"></canvas></div>
                </div>
                <div id="prod-movement-msg" class="text-center text-muted py-3"></div>

                <!-- Rich detail panel (mirrors Pre-Orden modal, mv- prefix) -->
                <div id="mv-detail-wrap" class="d-none mt-3">
                  <h5 id="mv-product-name" class="fw-bold mb-3"></h5>

                  <div id="mv-monthButtonsContainer" class="d-flex flex-wrap gap-2 my-3"></div>

                  <table class="table table-bordered text-center mb-3">
                    <thead class="table-secondary">
                      <tr><th>Ventas</th><th>Costo</th><th>Profit</th><th>Recibos</th><th>Vendidos</th></tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td id="mv-summary-ventas">$0.00</td>
                        <td id="mv-summary-costo">$0.00</td>
                        <td id="mv-summary-profit">$0.00</td>
                        <td id="mv-summary-recibos">0</td>
                        <td id="mv-summary-vendidos">0</td>
                      </tr>
                    </tbody>
                  </table>

                  <div class="row">
                    <div class="col-md-6">
                      <h6 class="fw-bold">Resumen anual</h6>
                      <p class="mb-0">Ventas totales (unidades): <span id="mv-annual-total-sales-units">—</span></p>
                      <p class="mb-0">Valor bruto de ventas: <span id="mv-annual-gross-sales-value">—</span></p>
                      <p class="mb-0">Costos totales: <span id="mv-annual-total-costs">—</span></p>
                      <p class="mb-0">Ganancias totales: <span id="mv-annual-total-profit">—</span></p>
                    </div>
                    <div class="col-md-6">
                      <h6 class="fw-bold">Orden Sugerida: <span id="mv-suggested-order-quantity">—</span></h6>
                      <p class="text-danger mb-0" style="font-size:10px"><span id="mv-suggested-order-excess-message"></span></p>
                      <p class="mb-0 fw-bold">Demanda:</p>
                      <p class="text-end mb-0" style="font-size:10px">*basado en los últimos 3 meses</p>
                      <ul class="mb-0 list-unstyled">
                        <li class="text-end">Semanal: <span id="mv-demand-weekly">—</span></li>
                        <li class="text-end">Mensual: <span id="mv-demand-monthly">—</span></li>
                      </ul>
                      <p class="mb-0">Inventario actual: <span id="mv-current-inventory">—</span></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Catalog tab -->
      <div class="tab-pane fade" id="prod-tab-catalog">
        <!-- Filter bar -->
        <div class="d-flex gap-2 flex-wrap align-items-end mb-3">
          <div>
            <label class="form-label small mb-1">Departamento</label>
            <select id="prod-filter-dept" class="form-select form-select-sm" style="min-width:160px;"><option value="">Todos</option></select>
          </div>
          <div>
            <label class="form-label small mb-1">Categoría</label>
            <select id="prod-filter-cat" class="form-select form-select-sm" style="min-width:160px;"><option value="">Todas</option></select>
          </div>
          <div>
            <label class="form-label small mb-1">Código</label>
            <input type="text" id="prod-filter-code" class="form-control form-control-sm" placeholder="Ej: PROD001" style="width:130px;">
          </div>
          <div>
            <label class="form-label small mb-1">Nombre</label>
            <input type="text" id="prod-filter-name" class="form-control form-control-sm" placeholder="Buscar..." style="width:160px;">
          </div>
          <button class="btn btn-sm btn-primary" id="prod-filter-apply"><i class="fas fa-filter me-1"></i>Filtrar</button>
          <button class="btn btn-sm btn-outline-secondary" id="prod-filter-reset">Limpiar</button>
          <button class="btn btn-sm btn-success ms-auto" id="prod-add-btn"><i class="fas fa-plus me-1"></i>Nuevo Producto</button>
        </div>
        <div class="card rm-card">
          <div class="card-body p-0">
            <table id="prod-catalog-table" class="table rm-datatable w-100">
              <thead><tr>
                <th>Código</th><th>Nombre</th><th>Departamento</th><th>Categoría</th>
                <th class="text-end">Precio</th><th class="text-end">Costo</th>
                <th class="text-end">Stock</th><th>Barcode</th><th class="text-center">Acciones</th>
              </tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Search tab -->
      <div class="tab-pane fade" id="prod-tab-search">
        <div class="card rm-card">
          <div class="card-body">
            <p class="text-muted small mb-3">Busca por código, barcode o nombre para ver todos sus detalles.</p>
            <div class="d-flex gap-2 mb-4 position-relative" style="max-width:560px;">
              <div class="position-relative flex-grow-1">
                <input type="text" id="prod-search-input" class="form-control" placeholder="Código, barcode o nombre..." autocomplete="off">
                <div id="prod-search-suggestions" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:1050; max-height:260px; overflow:auto;"></div>
              </div>
              <button class="btn btn-primary" id="prod-search-btn"><i class="fas fa-search me-1"></i>Buscar</button>
            </div>
            <div id="prod-search-result" class="d-none"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ─── CLIENTS SECTION ───────────────────────────────────── -->
  <section id="clients-section" class="rm-section">
    <div class="rm-page-header d-flex align-items-center justify-content-between">
      <div><h4>Clientes</h4><p>Administra tu base de clientes</p></div>
      <button class="btn btn-sm btn-success" id="cl-add-btn"><i class="fas fa-plus me-1"></i>Nuevo Cliente</button>
    </div>

    <!-- 3 KPI cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-users"></i></div>
          <div class="rm-kpi-label">Clientes Activos</div>
          <div class="rm-kpi-value" id="cl-kpi-active">—</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-user-check"></i></div>
          <div class="rm-kpi-label">Compraron (30 días)</div>
          <div class="rm-kpi-value" id="cl-kpi-recent">—</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-user-plus"></i></div>
          <div class="rm-kpi-label">Nuevos Este Mes</div>
          <div class="rm-kpi-value" id="cl-kpi-new">—</div>
        </div>
      </div>
    </div>

    <div class="card rm-card">
      <div class="card-body">
        <table id="clients-table" class="table rm-datatable w-100">
          <thead><tr>
            <th>ID</th><th>Nombre</th><th>Apellido</th><th>Ciudad</th>
            <th>Teléfono</th><th>Email</th><th>Categoría</th>
            <th class="text-end">Balance</th><th>Última Compra</th>
            <th class="text-center">Estado</th><th class="text-center">Acciones</th>
          </tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ─── REPORTS SECTION ──────────────────────────────────────── -->
  <section id="reports-section" class="rm-section">
    <div class="rm-page-header d-flex align-items-center justify-content-between">
      <div><h4>Reportes</h4><p>Genera y exporta reportes del negocio</p></div>
    </div>

    <!-- Tab navigation -->
    <ul class="nav nav-pills mb-4 gap-2" id="rpt-tabs-nav">
      <li class="nav-item">
        <button class="nav-link active" data-rpt="ventas">
          <i class="fas fa-chart-bar me-1"></i>Reporte de Ventas
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="productos">
          <i class="fas fa-box me-1"></i>Ventas por Productos
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="metodos">
          <i class="fas fa-credit-card me-1"></i>Métodos de Pago
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="tendencia">
          <i class="fas fa-chart-line me-1"></i>Tendencia Mensual
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="departamento">
          <i class="fas fa-store me-1"></i>Por Departamento
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="categoria">
          <i class="fas fa-tags me-1"></i>Por Categoría
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="horas">
          <i class="fas fa-clock me-1"></i>Análisis de Horas
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="ivu">
          <i class="fas fa-file-invoice me-1"></i>Reporte IVU
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-rpt="yoy">
          <i class="fas fa-exchange-alt me-1"></i>Año vs Año
        </button>
      </li>
    </ul>

    <!-- ── Panel: Reporte de Ventas ──────────────────────────────── -->
    <div id="rpt-ventas" class="rpt-panel">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <label class="form-label small mb-0 fw-semibold">Desde</label>
        <input type="date" id="rv-from" class="form-control form-control-sm" style="width:150px">
        <label class="form-label small mb-0 fw-semibold">Hasta</label>
        <input type="date" id="rv-to"   class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-sm btn-primary" id="rv-apply"><i class="fas fa-search me-1"></i>Generar</button>
        <button class="btn btn-sm btn-outline-secondary" id="rv-refresh" title="Actualizar"><i class="fas fa-sync-alt"></i></button>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Total Ventas</div>
          <div class="rm-kpi-value" id="rv-kpi-sales">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-chart-line"></i></div>
          <div class="rm-kpi-label">Ganancia</div>
          <div class="rm-kpi-value" id="rv-kpi-profit">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">Margen</div>
          <div class="rm-kpi-value" id="rv-kpi-margin">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-receipt"></i></div>
          <div class="rm-kpi-label">Transacciones</div>
          <div class="rm-kpi-value" id="rv-kpi-txcount">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-danger-soft"><i class="fas fa-ticket"></i></div>
          <div class="rm-kpi-label">Ticket Prom.</div>
          <div class="rm-kpi-value" id="rv-kpi-ticket">—</div>
        </div></div>
      </div>
      <div class="rm-card mb-4">
        <div class="rm-card__header"><span><i class="fas fa-chart-bar me-1"></i>Ventas Diarias</span></div>
        <div class="rm-card__body"><div class="rm-chart-wrap" style="height:220px"><canvas id="rv-chart"></canvas></div></div>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rv-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Fecha</th>
              <th class="text-end">Ventas</th>
              <th class="text-end">Ganancia</th>
              <th class="text-end">Margen</th>
              <th class="text-end">Transacciones</th>
              <th class="text-end">Ticket Prom.</th>
            </tr></thead>
            <tbody></tbody>
            <tfoot><tr class="fw-bold table-light">
              <td>TOTAL</td>
              <td class="text-end" id="rv-foot-sales"></td>
              <td class="text-end" id="rv-foot-profit"></td>
              <td class="text-end" id="rv-foot-margin"></td>
              <td class="text-end" id="rv-foot-tx"></td>
              <td class="text-end"></td>
            </tr></tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Ventas por Productos ───────────────────────────── -->
    <div id="rpt-productos" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <label class="form-label small mb-0 fw-semibold">Desde</label>
        <input type="date" id="rprod-from" class="form-control form-control-sm" style="width:150px">
        <label class="form-label small mb-0 fw-semibold">Hasta</label>
        <input type="date" id="rprod-to"   class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-sm btn-primary" id="rprod-apply"><i class="fas fa-search me-1"></i>Generar</button>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rprod-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Código</th>
              <th>Producto</th>
              <th>Departamento</th>
              <th>Categoría</th>
              <th class="text-end">Unidades</th>
              <th class="text-end">Ventas</th>
              <th class="text-end">Ganancia</th>
              <th class="text-end">Margen</th>
            </tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Métodos de Pago ─────────────────────────────────── -->
    <div id="rpt-metodos" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <label class="form-label small mb-0 fw-semibold">Desde</label>
        <input type="date" id="rpay-from" class="form-control form-control-sm" style="width:150px">
        <label class="form-label small mb-0 fw-semibold">Hasta</label>
        <input type="date" id="rpay-to"   class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-sm btn-primary" id="rpay-apply"><i class="fas fa-search me-1"></i>Generar</button>
      </div>
      <!-- Summary KPI cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-money-bill-wave"></i></div>
          <div class="rm-kpi-label">Efectivo</div>
          <div class="rm-kpi-value" id="rpay-kpi-cash">—</div>
        </div></div>
        <div class="col-6 col-md-4 col-xl"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-credit-card"></i></div>
          <div class="rm-kpi-label">Tarjeta Crédito</div>
          <div class="rm-kpi-value" id="rpay-kpi-credit">—</div>
        </div></div>
        <div class="col-6 col-md-4 col-xl"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-credit-card"></i></div>
          <div class="rm-kpi-label">Tarjeta Débito</div>
          <div class="rm-kpi-value" id="rpay-kpi-debit">—</div>
        </div></div>
        <div class="col-6 col-md-4 col-xl"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-mobile-alt"></i></div>
          <div class="rm-kpi-label">ATH Móvil</div>
          <div class="rm-kpi-value" id="rpay-kpi-ath">—</div>
        </div></div>
        <div class="col-6 col-md-4 col-xl"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-danger-soft"><i class="fas fa-file-invoice-dollar"></i></div>
          <div class="rm-kpi-label">Cheque</div>
          <div class="rm-kpi-value" id="rpay-kpi-check">—</div>
        </div></div>
        <div class="col-6 col-md-4 col-xl"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-calculator"></i></div>
          <div class="rm-kpi-label">Total General</div>
          <div class="rm-kpi-value" id="rpay-kpi-total">—</div>
        </div></div>
      </div>
      <!-- Charts row -->
      <div class="row g-4 mb-4">
        <div class="col-md-5">
          <div class="rm-card h-100">
            <div class="rm-card__header"><span>Distribución por Método</span></div>
            <div class="rm-card__body d-flex align-items-center justify-content-center">
              <div class="rm-chart-wrap" style="height:240px;width:100%"><canvas id="rpay-donut"></canvas></div>
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="rm-card h-100">
            <div class="rm-card__header"><span>Tendencia por Método</span></div>
            <div class="rm-card__body">
              <div class="rm-chart-wrap" style="height:240px"><canvas id="rpay-bar"></canvas></div>
            </div>
          </div>
        </div>
      </div>
      <!-- Detailed table -->
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rpay-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Fecha</th>
              <th class="text-end">Efectivo</th>
              <th class="text-end">Crédito</th>
              <th class="text-end">Débito</th>
              <th class="text-end">ATH Móvil</th>
              <th class="text-end">Cheque</th>
              <th class="text-end fw-bold">Total</th>
            </tr></thead>
            <tbody></tbody>
            <tfoot><tr class="fw-bold table-light">
              <td>TOTAL</td>
              <td class="text-end" id="rpay-foot-cash"></td>
              <td class="text-end" id="rpay-foot-credit"></td>
              <td class="text-end" id="rpay-foot-debit"></td>
              <td class="text-end" id="rpay-foot-ath"></td>
              <td class="text-end" id="rpay-foot-check"></td>
              <td class="text-end" id="rpay-foot-total"></td>
            </tr></tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Tendencia Mensual ──────────────────────────────── -->
    <div id="rpt-tendencia" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <span class="text-muted small"><i class="fas fa-info-circle me-1"></i>Muestra los últimos 13 meses automáticamente</span>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Ventas 12 Meses</div>
          <div class="rm-kpi-value" id="rtend-kpi-sales">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-chart-line"></i></div>
          <div class="rm-kpi-label">Ganancia 12 Meses</div>
          <div class="rm-kpi-value" id="rtend-kpi-profit">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">Margen Prom.</div>
          <div class="rm-kpi-value" id="rtend-kpi-margin">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-trophy"></i></div>
          <div class="rm-kpi-label">Mejor Mes</div>
          <div class="rm-kpi-value" id="rtend-kpi-best">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-danger-soft"><i class="fas fa-arrow-trend-down"></i></div>
          <div class="rm-kpi-label">Menor Margen</div>
          <div class="rm-kpi-value" id="rtend-kpi-worst">—</div>
        </div></div>
      </div>
      <div class="rm-card mb-4">
        <div class="rm-card__header"><span><i class="fas fa-chart-bar me-1"></i>Ventas Mensuales y Tendencia de Margen</span></div>
        <div class="rm-card__body"><div class="rm-chart-wrap" style="height:260px"><canvas id="rtend-chart"></canvas></div></div>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rtend-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Mes</th>
              <th class="text-end">Facturas</th>
              <th class="text-end">Ventas</th>
              <th class="text-end">Subtotal</th>
              <th class="text-end">Descuentos</th>
              <th class="text-end">Ganancia</th>
              <th class="text-end">Margen%</th>
              <th class="text-end">Ticket Prom.</th>
            </tr></thead>
            <tbody></tbody>
            <tfoot><tr class="fw-bold table-light">
              <td>TOTAL</td>
              <td class="text-end" id="rtend-foot-invoices"></td>
              <td class="text-end" id="rtend-foot-sales"></td>
              <td class="text-end" id="rtend-foot-subtotal"></td>
              <td class="text-end" id="rtend-foot-discount"></td>
              <td class="text-end" id="rtend-foot-profit"></td>
              <td class="text-end" id="rtend-foot-margin"></td>
              <td class="text-end"></td>
            </tr></tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Por Departamento ─────────────────────────────── -->
    <div id="rpt-departamento" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <label class="form-label small mb-0 fw-semibold">Desde</label>
        <input type="date" id="rdept-from" class="form-control form-control-sm" style="width:150px">
        <label class="form-label small mb-0 fw-semibold">Hasta</label>
        <input type="date" id="rdept-to" class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-sm btn-primary" id="rdept-apply"><i class="fas fa-search me-1"></i>Generar</button>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-layer-group"></i></div>
          <div class="rm-kpi-label">Departamentos</div>
          <div class="rm-kpi-value" id="rdept-kpi-depts">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Total Ventas</div>
          <div class="rm-kpi-value" id="rdept-kpi-sales">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-chart-line"></i></div>
          <div class="rm-kpi-label">Total Ganancia</div>
          <div class="rm-kpi-value" id="rdept-kpi-profit">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">Margen Prom.</div>
          <div class="rm-kpi-value" id="rdept-kpi-margin">—</div>
        </div></div>
      </div>
      <div class="rm-card mb-4">
        <div class="rm-card__header"><span><i class="fas fa-chart-bar me-1"></i>Ventas y Ganancia por Departamento (Top 15)</span></div>
        <div class="rm-card__body"><div class="rm-chart-wrap" style="height:380px"><canvas id="rdept-chart"></canvas></div></div>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rdept-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Departamento</th>
              <th class="text-end">Facturas</th>
              <th class="text-end">Cant. Vendida</th>
              <th class="text-end">Ventas</th>
              <th class="text-end">Ganancia</th>
              <th class="text-end">Precio Prom.</th>
              <th class="text-end">Margen%</th>
            </tr></thead>
            <tbody></tbody>
            <tfoot><tr class="fw-bold table-light">
              <td>TOTAL</td>
              <td class="text-end" id="rdept-foot-invoices"></td>
              <td class="text-end" id="rdept-foot-qty"></td>
              <td class="text-end" id="rdept-foot-sales"></td>
              <td class="text-end" id="rdept-foot-profit"></td>
              <td class="text-end"></td>
              <td class="text-end" id="rdept-foot-margin"></td>
            </tr></tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Por Categoría ────────────────────────────────── -->
    <div id="rpt-categoria" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <label class="form-label small mb-0 fw-semibold">Desde</label>
        <input type="date" id="rcat-from" class="form-control form-control-sm" style="width:150px">
        <label class="form-label small mb-0 fw-semibold">Hasta</label>
        <input type="date" id="rcat-to" class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-sm btn-primary" id="rcat-apply"><i class="fas fa-search me-1"></i>Generar</button>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-tags"></i></div>
          <div class="rm-kpi-label">Categorías</div>
          <div class="rm-kpi-value" id="rcat-kpi-cats">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Total Ventas</div>
          <div class="rm-kpi-value" id="rcat-kpi-sales">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-chart-line"></i></div>
          <div class="rm-kpi-label">Total Ganancia</div>
          <div class="rm-kpi-value" id="rcat-kpi-profit">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">Margen Prom.</div>
          <div class="rm-kpi-value" id="rcat-kpi-margin">—</div>
        </div></div>
      </div>
      <div class="rm-card mb-4">
        <div class="rm-card__header"><span><i class="fas fa-chart-bar me-1"></i>Ventas y Ganancia por Categoría (Top 15)</span></div>
        <div class="rm-card__body"><div class="rm-chart-wrap" style="height:380px"><canvas id="rcat-chart"></canvas></div></div>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rcat-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Categoría</th>
              <th class="text-end">Facturas</th>
              <th class="text-end">Cant. Vendida</th>
              <th class="text-end">Ventas</th>
              <th class="text-end">Ganancia</th>
              <th class="text-end">Precio Prom.</th>
              <th class="text-end">Margen%</th>
            </tr></thead>
            <tbody></tbody>
            <tfoot><tr class="fw-bold table-light">
              <td>TOTAL</td>
              <td class="text-end" id="rcat-foot-invoices"></td>
              <td class="text-end" id="rcat-foot-qty"></td>
              <td class="text-end" id="rcat-foot-sales"></td>
              <td class="text-end" id="rcat-foot-profit"></td>
              <td class="text-end"></td>
              <td class="text-end" id="rcat-foot-margin"></td>
            </tr></tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Análisis de Horas ─────────────────────────────── -->
    <div id="rpt-horas" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <label class="form-label small mb-0 fw-semibold">Desde</label>
        <input type="date" id="rhrs-from" class="form-control form-control-sm" style="width:150px">
        <label class="form-label small mb-0 fw-semibold">Hasta</label>
        <input type="date" id="rhrs-to" class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-sm btn-primary" id="rhrs-apply"><i class="fas fa-search me-1"></i>Generar</button>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-fire"></i></div>
          <div class="rm-kpi-label">Hora Pico (Ventas)</div>
          <div class="rm-kpi-value" id="rhrs-kpi-peak">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-star"></i></div>
          <div class="rm-kpi-label">Hora Más Rentable</div>
          <div class="rm-kpi-value" id="rhrs-kpi-bestprofit">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-receipt"></i></div>
          <div class="rm-kpi-label">Total Transacciones</div>
          <div class="rm-kpi-value" id="rhrs-kpi-txcount">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-ticket"></i></div>
          <div class="rm-kpi-label">Ticket Prom.</div>
          <div class="rm-kpi-value" id="rhrs-kpi-ticket">—</div>
        </div></div>
      </div>
      <div class="rm-card mb-4">
        <div class="rm-card__header"><span><i class="fas fa-clock me-1"></i>Ventas por Hora del Día</span></div>
        <div class="rm-card__body"><div class="rm-chart-wrap" style="height:260px"><canvas id="rhrs-chart"></canvas></div></div>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rhrs-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Hora</th>
              <th class="text-end">Transacciones</th>
              <th class="text-end">Ventas</th>
              <th class="text-end">Ganancia</th>
              <th class="text-end">Ticket Prom.</th>
              <th class="text-end">Artículos Vendidos</th>
            </tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Reporte IVU ──────────────────────────────────── -->
    <div id="rpt-ivu" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <label class="form-label small mb-0 fw-semibold">Desde</label>
        <input type="date" id="rivu-from" class="form-control form-control-sm" style="width:150px">
        <label class="form-label small mb-0 fw-semibold">Hasta</label>
        <input type="date" id="rivu-to" class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-sm btn-primary" id="rivu-apply"><i class="fas fa-search me-1"></i>Generar</button>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-file-invoice-dollar"></i></div>
          <div class="rm-kpi-label">Total Subtotal</div>
          <div class="rm-kpi-value" id="rivu-kpi-subtotal">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-warning-soft"><i class="fas fa-building"></i></div>
          <div class="rm-kpi-label">IVU Municipal</div>
          <div class="rm-kpi-value" id="rivu-kpi-city">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-danger-soft"><i class="fas fa-landmark"></i></div>
          <div class="rm-kpi-label">IVU Estatal</div>
          <div class="rm-kpi-value" id="rivu-kpi-state">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-calculator"></i></div>
          <div class="rm-kpi-label">Total IVU</div>
          <div class="rm-kpi-value" id="rivu-kpi-total">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Ventas Totales</div>
          <div class="rm-kpi-value" id="rivu-kpi-sales">—</div>
        </div></div>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="rivu-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Fecha</th>
              <th class="text-end">Transacciones</th>
              <th class="text-end">Subtotal</th>
              <th class="text-end">IVU Municipal</th>
              <th class="text-end">IVU Estatal</th>
              <th class="text-end">Total IVU</th>
              <th class="text-end fw-bold">Ventas Totales</th>
            </tr></thead>
            <tbody></tbody>
            <tfoot><tr class="fw-bold table-light">
              <td>TOTAL</td>
              <td class="text-end" id="rivu-foot-tx"></td>
              <td class="text-end" id="rivu-foot-subtotal"></td>
              <td class="text-end" id="rivu-foot-city"></td>
              <td class="text-end" id="rivu-foot-state"></td>
              <td class="text-end" id="rivu-foot-ivu"></td>
              <td class="text-end" id="rivu-foot-sales"></td>
            </tr></tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Panel: Año vs Año ──────────────────────────────────── -->
    <div id="rpt-yoy" class="rpt-panel d-none">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <span class="fw-semibold text-muted small" id="ryoy-range-label"></span>
        <button class="btn btn-sm btn-primary ms-auto" id="ryoy-apply"><i class="fas fa-sync me-1"></i>Actualizar</button>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-primary-soft"><i class="fas fa-calendar-check"></i></div>
          <div class="rm-kpi-label" id="ryoy-label-cy">Este Año</div>
          <div class="rm-kpi-value" id="ryoy-kpi-cy">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-secondary-soft"><i class="fas fa-calendar"></i></div>
          <div class="rm-kpi-label" id="ryoy-label-py">Año Pasado</div>
          <div class="rm-kpi-value" id="ryoy-kpi-py">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-success-soft"><i class="fas fa-dollar-sign"></i></div>
          <div class="rm-kpi-label">Variación $</div>
          <div class="rm-kpi-value" id="ryoy-kpi-delta">—</div>
        </div></div>
        <div class="col-6 col-md"><div class="rm-kpi-card">
          <div class="rm-kpi-icon bg-info-soft"><i class="fas fa-percent"></i></div>
          <div class="rm-kpi-label">Crecimiento</div>
          <div class="rm-kpi-value" id="ryoy-kpi-pct">—</div>
        </div></div>
      </div>
      <div class="rm-card mb-4">
        <div class="rm-card__header"><h6 class="rm-card__title">Ventas Mensuales — Comparación</h6></div>
        <div class="rm-card__body"><div class="rm-chart-wrap"><canvas id="ryoy-chart"></canvas></div></div>
      </div>
      <div class="rm-card">
        <div class="rm-card__body">
          <table id="ryoy-table" class="table rm-datatable w-100">
            <thead><tr>
              <th>Mes</th>
              <th class="text-end">Ventas Este Año</th>
              <th class="text-end">Ventas Año Pasado</th>
              <th class="text-end">Variación $</th>
              <th class="text-end">Variación %</th>
              <th class="text-end">Ganancia Este Año</th>
              <th class="text-end">Ganancia Año Pasado</th>
            </tr></thead>
            <tbody></tbody>
            <tfoot><tr class="fw-bold table-light">
              <td>TOTAL</td>
              <td class="text-end" id="ryoy-foot-cy"></td>
              <td class="text-end" id="ryoy-foot-py"></td>
              <td class="text-end" id="ryoy-foot-delta"></td>
              <td class="text-end" id="ryoy-foot-pct"></td>
              <td class="text-end" id="ryoy-foot-profit-cy"></td>
              <td class="text-end" id="ryoy-foot-profit-py"></td>
            </tr></tfoot>
          </table>
        </div>
      </div>
    </div>

  </section>

  <!-- ─── SCHEDULE SECTION ──────────────────────────────────── -->
  <section id="schedule-section" class="rm-section">
    <div id="horario-section" style="display:none"></div>
    <?php include 'view/components/schedule/schedule-fragment.php'; ?>
  </section>

</main><!-- /rm-main -->

<!-- ═══════════════════ MODALS ═══════════════════ -->

<!-- Schedule Modals (must be outside rm-section to avoid stacking-context z-index trap) -->
<?php include 'view/components/schedule/form-schedule.php'; ?>
<?php include 'view/components/schedule/form-available-schedule.php'; ?>

<!-- Product CRUD Modal -->
<div class="modal fade" id="prod-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="prod-modal-title">Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pm-mode" value="new">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Código</label>
            <input type="text" id="pm-code" class="form-control form-control-sm" placeholder="Auto">
          </div>
          <div class="col-md-8">
            <label class="form-label small fw-semibold">Nombre del Producto</label>
            <input type="text" id="pm-name" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Precio</label>
            <input type="number" id="pm-price" class="form-control form-control-sm" step="0.01" min="0" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Costo</label>
            <input type="number" id="pm-cost" class="form-control form-control-sm" step="0.01" min="0" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Stock</label>
            <input type="number" id="pm-stock" class="form-control form-control-sm" min="0" value="0">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Departamento</label>
            <select id="pm-dept" class="form-select form-select-sm" required><option value="">Seleccionar...</option></select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Categoría</label>
            <select id="pm-cat" class="form-select form-select-sm" required><option value="">Seleccionar...</option></select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Barcode</label>
            <input type="text" id="pm-barcode" class="form-control form-control-sm">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Activo</label>
            <select id="pm-active" class="form-select form-select-sm">
              <option value="1">Sí</option><option value="0">No</option>
            </select>
          </div>
        </div>
        <div id="pm-error" class="alert alert-danger mt-3 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="pm-save-btn"><i class="fas fa-save me-1"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Client CRUD Modal -->
<div class="modal fade" id="client-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cl-modal-title">Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cm-mode" value="new">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label small fw-semibold">ID Cliente</label>
            <input type="text" id="cm-id" class="form-control form-control-sm" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
            <input type="text" id="cm-firstname" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-5">
            <label class="form-label small fw-semibold">Apellido</label>
            <input type="text" id="cm-lastname" class="form-control form-control-sm">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Dirección 1</label>
            <input type="text" id="cm-addr1" class="form-control form-control-sm">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Dirección 2</label>
            <input type="text" id="cm-addr2" class="form-control form-control-sm">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Ciudad</label>
            <input type="text" id="cm-city" class="form-control form-control-sm">
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Zip Code</label>
            <input type="text" id="cm-zip" class="form-control form-control-sm">
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-semibold">País</label>
            <input type="text" id="cm-country" class="form-control form-control-sm" value="PR">
          </div>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Teléfono</label>
            <input type="text" id="cm-phone" class="form-control form-control-sm">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Email</label>
            <input type="email" id="cm-email" class="form-control form-control-sm">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Categoría</label>
            <select id="cm-category" class="form-select form-select-sm"><option value="">Sin categoría</option></select>
          </div>
          <div class="col-md-2">
            <label class="form-label small fw-semibold">Activo</label>
            <select id="cm-active" class="form-select form-select-sm">
              <option value="S">Sí</option><option value="N">No</option>
            </select>
          </div>
        </div>
        <div id="cm-error" class="alert alert-danger mt-3 d-none"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="cm-save-btn"><i class="fas fa-save me-1"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Receive Inventory Modal -->
<?php include 'modalReceiveInventory.php'; ?>

<?php include 'scripts.php'; ?>
<script src="js/config.js"></script>
<script type="module">
  import { initApp } from './js/app.js';
  initApp();
</script>

</body>
</html>
