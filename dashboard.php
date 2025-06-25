<?php
// Incluir archivo de configuración que contiene la función callAPI
require_once 'config.php';

// Obtener fechas para el rango de datos (último mes por defecto)
$today = date('Y-m-d');
$thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : $thirtyDaysAgo;
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : $today;

// Obtener datos para el dashboard
try {
    // Obtener datos de ventas totales
    $salesTotals = callAPI('SalesTotals', ['DateFrom' => $dateFrom, 'DateTo' => $dateTo]);
    
    // Obtener datos de ventas por hora
    $salesByHour = callAPI('SalesByHour', ['DateFrom' => $dateFrom, 'DateTo' => $dateTo]);
    
    // Obtener datos de ventas por categoría
    $salesByCategory = callAPI('SalesByCategory', ['DateFrom' => $dateFrom, 'DateTo' => $dateTo]);
    
    // Obtener datos de ventas por método de pago
    $salesByMethod = callAPI('SalesByMethod', ['DateFrom' => $dateFrom, 'DateTo' => $dateTo]);
    
    // Obtener datos de productos más vendidos
    $topProducts = callAPI('TopSellProducts', ['DateFrom' => $dateFrom, 'DateTo' => $dateTo]);
    
    // Obtener datos de tendencia de ventas mensuales (últimos 12 meses)
    $yearAgo = date('Y-m-d', strtotime('-12 months'));
    $salesTrend = callAPI('SaleTrendByMonth', ['DateFrom' => $yearAgo, 'DateTo' => $dateTo]);
    
    // Obtener datos de productos con bajo stock
    $lowStockItems = callAPI('LowLevelItems', ['Active' => 1]);
    
    // Obtener información de la empresa
    $companyInfo = callAPI('InfoCompany');
    
    // Indicador de éxito
    $dataLoaded = true;
} catch (Exception $e) {
    // Capturar cualquier error
    $error = $e->getMessage();
    $dataLoaded = false;
}

// Preparar datos para gráficos
$hourlyLabels = [];
$hourlySales = [];
$hourlyTransactions = [];

if (isset($salesByHour) && is_array($salesByHour)) {
    foreach ($salesByHour as $hour) {
        $hourlyLabels[] = $hour['HourOfDay'] . ':00';
        $hourlySales[] = $hour['TotalSales'];
        $hourlyTransactions[] = $hour['TransactionCount'];
    }
}

// Preparar datos de categorías (top 10)
$categoryLabels = [];
$categorySales = [];
$categoryProfit = [];

if (isset($salesByCategory) && is_array($salesByCategory)) {
    // Ordenar por ventas totales (descendente)
    usort($salesByCategory, function($a, $b) {
        return $b['TotalSales'] - $a['TotalSales'];
    });
    
    // Tomar las 10 principales categorías
    $topCategories = array_slice($salesByCategory, 0, 10);
    
    foreach ($topCategories as $category) {
        $categoryLabels[] = $category['CategoryName'];
        $categorySales[] = $category['TotalSales'];
        $categoryProfit[] = $category['TotalProfit'];
    }
}

// Preparar datos de métodos de pago
$paymentMethods = [
    'Efectivo' => 0,
    'Tarjeta de Crédito' => 0,
    'Tarjeta de Débito' => 0,
    'Cheque' => 0,
    'ATH Móvil' => 0
];

if (isset($salesByMethod) && is_array($salesByMethod)) {
    foreach ($salesByMethod as $method) {
        $paymentMethods['Efectivo'] += isset($method['CashPayments']) ? $method['CashPayments'] : 0;
        $paymentMethods['Tarjeta de Crédito'] += isset($method['CreditCardPayments']) ? $method['CreditCardPayments'] : 0;
        $paymentMethods['Tarjeta de Débito'] += isset($method['DebitCardPayments']) ? $method['DebitCardPayments'] : 0;
        $paymentMethods['Cheque'] += isset($method['CheckPayments']) ? $method['CheckPayments'] : 0;
        $paymentMethods['ATH Móvil'] += isset($method['AthMovilPayments']) ? $method['AthMovilPayments'] : 0;
    }
}

// Preparar datos de tendencia mensual
$trendLabels = [];
$trendSales = [];
$trendProfit = [];

if (isset($salesTrend) && is_array($salesTrend)) {
    // Ordenar por fecha (ascendente)
    usort($salesTrend, function($a, $b) {
        return strcmp($a['MonthYear'], $b['MonthYear']);
    });
    
    foreach ($salesTrend as $month) {
        $date = new DateTime($month['Year'] . '-' . str_pad($month['Month'], 2, '0', STR_PAD_LEFT) . '-01');
        $trendLabels[] = $date->format('M Y');
        $trendSales[] = $month['TotalSales'];
        $trendProfit[] = isset($month['TotalProfit']) ? $month['TotalProfit'] : 0;
    }
}

// Calcular métricas adicionales
$grossProfit = 0;
$grossMargin = 0;

if (isset($salesTotals) && is_array($salesTotals)) {
    $grossProfit = isset($salesTotals['TotalSales']) && isset($salesTotals['TotalCost']) ? 
                  $salesTotals['TotalSales'] - $salesTotals['TotalCost'] : 0;
    
    // Prevenir división por cero
    $grossMargin = (isset($salesTotals['TotalSales']) && $salesTotals['TotalSales'] > 0) ? 
                  ($grossProfit / $salesTotals['TotalSales'] * 100) : 0;
}

// Función para convertir arrays PHP a formato JavaScript
function toJsArray($array) {
    return json_encode($array);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ejecutivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            margin-bottom: 20px;
            height: 100%;
        }
        .card-body {
            display: flex;
            flex-direction: column;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .metric-label {
            font-size: 1rem;
            color: #6c757d;
        }
        /* FIX: Establecer altura fija para los contenedores de gráficos */
        .chart-container {
            position: relative;
            height: 300px !important; /* Altura fija para todos los gráficos */
            width: 100%;
            margin-bottom: 10px;
        }
        .low-stock {
            color: #dc3545;
            font-weight: bold;
        }
        .company-header {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <?php if (isset($companyInfo) && is_array($companyInfo)): ?>
        <div class="company-header">
            <div class="row">
                <div class="col-md-6">
                    <h1><?php echo htmlspecialchars($companyInfo['Name']); ?></h1>
                    <p>
                        <?php echo htmlspecialchars($companyInfo['Address1']); ?><br>
                        <?php if (!empty($companyInfo['Address2'])): echo htmlspecialchars($companyInfo['Address2']) . '<br>'; endif; ?>
                        <?php echo htmlspecialchars($companyInfo['City']); ?>, <?php echo htmlspecialchars($companyInfo['Country']); ?> <?php echo htmlspecialchars($companyInfo['ZipCode']); ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>
                        Tel: <?php echo htmlspecialchars($companyInfo['Phone']); ?><br>
                        <?php if (!empty($companyInfo['Email'])): echo htmlspecialchars($companyInfo['Email']); endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Dashboard de Ventas</h2>
            </div>
            <div class="col-md-6">
                <form method="get" action="" class="row g-3">
                    <div class="col-auto">
                        <label for="dateFrom" class="col-form-label">Desde:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="<?php echo htmlspecialchars($dateFrom); ?>">
                    </div>
                    <div class="col-auto">
                        <label for="dateTo" class="col-form-label">Hasta:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control" id="dateTo" name="dateTo" value="<?php echo htmlspecialchars($dateTo); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!$dataLoaded): ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading">Error al cargar los datos</h4>
            <p><?php echo isset($error) ? htmlspecialchars($error) : 'Ocurrió un error al comunicarse con la API. Por favor, intente nuevamente.'; ?></p>
        </div>
        <?php else: ?>

        <!-- Métricas principales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="metric-label">Ventas Totales</div>
                        <div class="metric-value"><?php echo formatCurrency(isset($salesTotals['TotalSales']) ? $salesTotals['TotalSales'] : 0); ?></div>
                        <div class="text-muted"><?php echo number_format(isset($salesTotals['TransactionCount']) ? $salesTotals['TransactionCount'] : 0); ?> transacciones</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="metric-label">Ganancia Bruta</div>
                        <div class="metric-value"><?php echo formatCurrency($grossProfit); ?></div>
                        <div class="text-muted">Margen: <?php echo formatPercentage($grossMargin); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="metric-label">Ticket Promedio</div>
                        <div class="metric-value"><?php echo formatCurrency(isset($salesTotals['AverageTicketAmount']) ? $salesTotals['AverageTicketAmount'] : 0); ?></div>
                        <div class="text-muted">Por transacción</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="metric-label">Descuentos</div>
                        <div class="metric-value"><?php echo formatCurrency(isset($salesTotals['TotalDiscounts']) ? $salesTotals['TotalDiscounts'] : 0); ?></div>
                        <div class="text-muted">
                            <?php 
                            // Prevenir división por cero
                            $discountPercentage = (isset($salesTotals['TotalDiscounts']) && isset($salesTotals['TotalSales']) && $salesTotals['TotalSales'] > 0) ? 
                                ($salesTotals['TotalDiscounts'] / $salesTotals['TotalSales'] * 100) : 0;
                            echo formatPercentage($discountPercentage); 
                            ?> de las ventas
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Ventas por Hora del Día</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="hourlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Ventas por Método de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Top 10 Categorías</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5>Tendencia de Ventas Mensuales</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos Más Vendidos -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Productos Más Vendidos</h5>
                <a href="export_products.php?dateFrom=<?php echo urlencode($dateFrom); ?>&dateTo=<?php echo urlencode($dateTo); ?>" class="btn btn-sm btn-outline-primary">Exportar a CSV</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Departamento</th>
                                <th>Categoría</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Ventas</th>
                                <th class="text-end">Ganancia</th>
                                <th class="text-end">Margen</th>
                                <th class="text-end">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($topProducts) && is_array($topProducts)): ?>
                                <?php 
                                // Ordenar por cantidad vendida (descendente)
                                usort($topProducts, function($a, $b) {
                                    return $b['TotalQuantitySold'] - $a['TotalQuantitySold'];
                                });
                                
                                // Mostrar los primeros 10 productos
                                $topProducts = array_slice($topProducts, 0, 10);
                                
                                foreach ($topProducts as $product): 
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['ProductCode']); ?></td>
                                        <td><?php echo htmlspecialchars($product['ProductName']); ?></td>
                                        <td><?php echo htmlspecialchars($product['Department']); ?></td>
                                        <td><?php echo htmlspecialchars($product['Category']); ?></td>
                                        <td class="text-end"><?php echo number_format($product['TotalQuantitySold']); ?></td>
                                        <td class="text-end"><?php echo formatCurrency($product['TotalSales']); ?></td>
                                        <td class="text-end"><?php echo formatCurrency($product['TotalProfit']); ?></td>
                                        <td class="text-end"><?php echo formatPercentage($product['ProfitMarginPercentage']); ?></td>
                                        <td class="text-end <?php echo ($product['CurrentStock'] < 10) ? 'low-stock' : ''; ?>">
                                            <?php echo number_format($product['CurrentStock']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No hay datos disponibles</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Productos con Bajo Stock -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Productos con Bajo Stock</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Departamento</th>
                                <th>Categoría</th>
                                <th class="text-end">Stock Actual</th>
                                <th class="text-end">Nivel Mínimo</th>
                                <th class="text-end">Nivel Máximo</th>
                                <th class="text-end">Precio</th>
                                <th>Proveedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($lowStockItems) && is_array($lowStockItems)): ?>
                                <?php 
                                // Ordenar por stock actual (ascendente)
                                usort($lowStockItems, function($a, $b) {
                                    return $a['CurrentStock'] - $b['CurrentStock'];
                                });
                                
                                // Mostrar los primeros 10 productos
                                $lowStockItems = array_slice($lowStockItems, 0, 10);
                                
                                foreach ($lowStockItems as $item): 
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['ProductCode']); ?></td>
                                        <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                                        <td><?php echo htmlspecialchars($item['Department']); ?></td>
                                        <td><?php echo htmlspecialchars($item['Category']); ?></td>
                                        <td class="text-end <?php echo ($item['CurrentStock'] < $item['MinimumLevel']) ? 'low-stock' : ''; ?>">
                                            <?php echo number_format($item['CurrentStock']); ?>
                                        </td>
                                        <td class="text-end"><?php echo number_format($item['MinimumLevel']); ?></td>
                                        <td class="text-end"><?php echo number_format($item['MaximumLevel']); ?></td>
                                        <td class="text-end"><?php echo formatCurrency($item['Price']); ?></td>
                                        <td><?php echo htmlspecialchars($item['PrimarySupplier']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No hay datos disponibles</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($dataLoaded): ?>
    <script>
        // FIX: Configuración global para todos los gráficos
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        
        // Gráfico de ventas por hora
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo toJsArray($hourlyLabels); ?>,
                datasets: [
                    {
                        label: 'Ventas ($)',
                        data: <?php echo toJsArray($hourlySales); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Transacciones',
                        data: <?php echo toJsArray($hourlyTransactions); ?>,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        tension: 0.1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Ventas ($)'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Transacciones'
                        }
                    }
                }
            }
        });

        // Gráfico de ventas por categoría
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo toJsArray($categoryLabels); ?>,
                datasets: [
                    {
                        label: 'Ventas',
                        data: <?php echo toJsArray($categorySales); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Ganancia',
                        data: <?php echo toJsArray($categoryProfit); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto ($)'
                        }
                    }
                }
            }
        });

        // Gráfico de métodos de pago
        const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'pie',
            data: {
                labels: <?php echo toJsArray(array_keys($paymentMethods)); ?>,
                datasets: [{
                    data: <?php echo toJsArray(array_values($paymentMethods)); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                // Prevenir división por cero
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: $${value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de tendencia de ventas
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo toJsArray($trendLabels); ?>,
                datasets: [
                    {
                        label: 'Ventas',
                        data: <?php echo toJsArray($trendSales); ?>,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Ganancia',
                        data: <?php echo toJsArray($trendProfit); ?>,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Monto ($)'
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>