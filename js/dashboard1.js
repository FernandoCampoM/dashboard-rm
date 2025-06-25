// Dashboard JavaScript

// Global variables
let dateFrom = moment().subtract(30, 'days').format('YYYY-MM-DD');
let dateTo = moment().format('YYYY-MM-DD');
let companyInfo = {};
let salesTotals = {};
let salesByCategory = [];
let salesByDepartment = [];
let salesByHour = [];
let salesTrend = [];
let lowStockItems = [];
let inventoryValue = [];
let topProducts = [];
let salesByMethod = [];

// Mock data for when API calls fail
const mockData = {
    companyInfo: {
        Name: "Demo Supermarket",
        Address1: "123 Main Street",
        City: "Anytown",
        Country: "USA",
        ZipCode: "12345",
        Phone: "(555) 123-4567",
        Email: "info@demosupermarket.com"
    },
    salesTotals: {
        TotalSales: 125000,
        TotalCost: 87500,
        TransactionCount: 1250
    },
    salesByCategory: [
        { CategoryName: "Produce", TotalSales: 32500, TotalProfit: 9750 },
        { CategoryName: "Dairy", TotalSales: 28750, TotalProfit: 8625 },
        { CategoryName: "Meat", TotalSales: 25000, TotalProfit: 7500 },
        { CategoryName: "Bakery", TotalSales: 18750, TotalProfit: 5625 },
        { CategoryName: "Beverages", TotalSales: 15000, TotalProfit: 4500 }
    ],
    salesByDepartment: [
        { DepartmentName: "Fresh Foods", TotalSales: 62500, TotalProfit: 18750, ProfitMarginPercentage: 30 },
        { DepartmentName: "Grocery", TotalSales: 37500, TotalProfit: 11250, ProfitMarginPercentage: 30 },
        { DepartmentName: "Frozen", TotalSales: 25000, TotalProfit: 7500, ProfitMarginPercentage: 30 }
    ],
    salesByHour: [
        { HourOfDay: 8, TotalSales: 5000, TransactionCount: 50 },
        { HourOfDay: 9, TotalSales: 7500, TransactionCount: 75 },
        { HourOfDay: 10, TotalSales: 10000, TransactionCount: 100 },
        { HourOfDay: 11, TotalSales: 12500, TransactionCount: 125 },
        { HourOfDay: 12, TotalSales: 15000, TransactionCount: 150 },
        { HourOfDay: 13, TotalSales: 12500, TransactionCount: 125 },
        { HourOfDay: 14, TotalSales: 10000, TransactionCount: 100 },
        { HourOfDay: 15, TotalSales: 7500, TransactionCount: 75 },
        { HourOfDay: 16, TotalSales: 10000, TransactionCount: 100 },
        { HourOfDay: 17, TotalSales: 12500, TransactionCount: 125 },
        { HourOfDay: 18, TotalSales: 15000, TransactionCount: 150 },
        { HourOfDay: 19, TotalSales: 7500, TransactionCount: 75 }
    ],
    salesTrend: [
        { Year: 2025, Month: 1, TotalSales: 110000, TotalProfit: 33000 },
        { Year: 2025, Month: 2, TotalSales: 115000, TotalProfit: 34500 },
        { Year: 2025, Month: 3, TotalSales: 120000, TotalProfit: 36000 },
        { Year: 2025, Month: 4, TotalSales: 125000, TotalProfit: 37500 },
        { Year: 2025, Month: 5, TotalSales: 130000, TotalProfit: 39000 },
        { Year: 2025, Month: 6, TotalSales: 135000, TotalProfit: 40500 }
    ],
    lowStockItems: [
        { ProductName: "Organic Apples", Department: "Produce", CurrentStock: 5, MinimumLevel: 10, ProductCode: "P001" },
        { ProductName: "Whole Milk", Department: "Dairy", CurrentStock: 8, MinimumLevel: 15, ProductCode: "D001" },
        { ProductName: "Wheat Bread", Department: "Bakery", CurrentStock: 3, MinimumLevel: 8, ProductCode: "B001" },
        { ProductName: "Ground Beef", Department: "Meat", CurrentStock: 4, MinimumLevel: 10, ProductCode: "M001" },
        { ProductName: "Orange Juice", Department: "Beverages", CurrentStock: 6, MinimumLevel: 12, ProductCode: "BV001" }
    ],
    inventoryValue: [
        { Department: "Produce", ItemCount: 150, TotalCost: 15000, TotalRetail: 22500 },
        { Department: "Dairy", ItemCount: 100, TotalCost: 10000, TotalRetail: 15000 },
        { Department: "Meat", ItemCount: 75, TotalCost: 18750, TotalRetail: 28125 },
        { Department: "Bakery", ItemCount: 50, TotalCost: 5000, TotalRetail: 7500 },
        { Department: "Beverages", ItemCount: 125, TotalCost: 12500, TotalRetail: 18750 },
        { Department: "TOTAL", ItemCount: 500, TotalCost: 61250, TotalRetail: 91875 }
    ],
    topProducts: [
        { ProductName: "Organic Bananas", Department: "Produce", Category: "Fruits", TotalQuantitySold: 500, TotalSales: 750, ProfitMarginPercentage: 33.3, CurrentStock: 100 },
        { ProductName: "Whole Milk", Department: "Dairy", Category: "Milk", TotalQuantitySold: 450, TotalSales: 1125, ProfitMarginPercentage: 30.0, CurrentStock: 75 },
        { ProductName: "White Bread", Department: "Bakery", Category: "Bread", TotalQuantitySold: 400, TotalSales: 800, ProfitMarginPercentage: 25.0, CurrentStock: 50 },
        { ProductName: "Ground Beef", Department: "Meat", Category: "Beef", TotalQuantitySold: 350, TotalSales: 1750, ProfitMarginPercentage: 35.0, CurrentStock: 25 },
        { ProductName: "Bottled Water", Department: "Beverages", Category: "Water", TotalQuantitySold: 300, TotalSales: 450, ProfitMarginPercentage: 40.0, CurrentStock: 150 }
    ],
    salesByMethod: [
        { Date: "2025-03-01", CashPayments: 15000, CreditCardPayments: 30000, DebitCardPayments: 15000 },
        { Date: "2025-03-02", CashPayments: 12500, CreditCardPayments: 25000, DebitCardPayments: 12500 }
    ]
};

// Initialize the dashboard
$(document).ready(function() {
    // Initialize date range picker
    initDateRangePicker();
    
    // Load initial data
    loadDashboardData();
    
    // Set up tab change events
    setupTabEvents();
    
    // Add a message about demo mode if needed
    checkDemoMode();
});

// Actualizar la parte donde se muestra el mensaje de modo demo
function checkDemoMode() {
    // Check if we've had API failures
    if (localStorage.getItem('dashboard_api_failures') === 'true') {
        $('body').append(
            '<div class="alert alert-warning alert-dismissible fade show position-fixed bottom-0 end-0 m-3" role="alert">' +
            '<strong>Modo Demo Activo</strong> - Usando datos de muestra debido a problemas de conexión con la API. ' +
            '<a href="api_debug.php" target="_blank">Depurar problemas de API</a>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>'
        );
    }
}

// Initialize date range picker
function initDateRangePicker() {
    $('#date-range').daterangepicker({
        startDate: moment().subtract(30, 'days'),
        endDate: moment(),
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, function(start, end, label) {
        dateFrom = start.format('YYYY-MM-DD');
        dateTo = end.format('YYYY-MM-DD');
        loadDashboardData();
    });
}

// Set up tab change events
function setupTabEvents() {
    // Sales timeframe tabs
    $('#salesTimeframeTabs button').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
        updateSalesTimeframe($(this).attr('id').replace('-tab', ''));
    });
    
    // Inventory tabs
    $('#inventoryTabs button').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Sales analysis tabs
    $('#salesAnalysisTabs button').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Sales trends tabs
    $('#salesTrendsTabs button').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
}

// Load all dashboard data
function loadDashboardData() {
    showLoadingState();
    
    // Reset API failure counter
    let apiFailures = 0;
    
    // Load company info
    $.ajax({
        url: 'api_proxy.php',
        data: { endpoint: 'InfoCompany' },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                companyInfo = data;
                updateCompanyInfo();
            } else {
                apiFailures++;
                showError('Failed to load company information: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                companyInfo = mockData.companyInfo;
                updateCompanyInfo();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load company information: ' + error);
            // Use mock data
            companyInfo = mockData.companyInfo;
            updateCompanyInfo();
        }
    });
    
    // Load sales totals
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'SalesTotals',
            DateFrom: dateFrom,
            DateTo: dateTo
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                salesTotals = data;
                updateSalesTotals();
            } else {
                apiFailures++;
                showError('Failed to load sales totals: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                salesTotals = mockData.salesTotals;
                updateSalesTotals();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load sales totals: ' + error);
            // Use mock data
            salesTotals = mockData.salesTotals;
            updateSalesTotals();
        }
    });
    
    // Load sales by category
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'SalesByCategory',
            DateFrom: dateFrom,
            DateTo: dateTo
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                salesByCategory = data || [];
                updateCategoryChart();
            } else {
                apiFailures++;
                showError('Failed to load sales by category: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                salesByCategory = mockData.salesByCategory;
                updateCategoryChart();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load sales by category: ' + error);
            // Use mock data
            salesByCategory = mockData.salesByCategory;
            updateCategoryChart();
        }
    });
    
    // Load sales by department
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'SalesByDepartment',
            DateFrom: dateFrom,
            DateTo: dateTo
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                salesByDepartment = data || [];
                updateDepartmentChart();
            } else {
                apiFailures++;
                showError('Failed to load sales by department: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                salesByDepartment = mockData.salesByDepartment;
                updateDepartmentChart();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load sales by department: ' + error);
            // Use mock data
            salesByDepartment = mockData.salesByDepartment;
            updateDepartmentChart();
        }
    });
    
// Load sales by hour
$.ajax({
    url: 'api_proxy.php',
    data: { 
        endpoint: 'SalesByHour',
        DateFrom: dateFrom,
        DateTo: dateTo
    },
    method: 'GET',
    dataType: 'json',
    success: function(data) {
        if (data && !data.error) {
            salesByHour = data || [];
            updateHourlyChart();
        } else {
            apiFailures++;
            showError('Failed to load hourly sales: ' + (data && data.error ? data.error : 'Unknown error'));
            // Use mock data
            salesByHour = mockData.salesByHour;
            updateHourlyChart();
        }
    },
    error: function(xhr, status, error) {
        apiFailures++;
        showError('Failed to load hourly sales: ' + error);
        // Use mock data
        salesByHour = mockData.salesByHour;
        updateHourlyChart();
    }
});
    
    // Load sales trend by month
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'SaleTrendByMonth',
            DateFrom: moment(dateFrom).subtract(12, 'months').format('YYYY-MM-DD'),
            DateTo: dateTo
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                salesTrend = data || [];
                updateMonthlyTrendChart();
                updateProfitTrendChart();
            } else {
                apiFailures++;
                showError('Failed to load sales trend: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                salesTrend = mockData.salesTrend;
                updateMonthlyTrendChart();
                updateProfitTrendChart();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load sales trend: ' + error);
            // Use mock data
            salesTrend = mockData.salesTrend;
            updateMonthlyTrendChart();
            updateProfitTrendChart();
        }
    });
    
    // Load low stock items
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'LowLevelItems',
            Active: 1
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                lowStockItems = data || [];
                updateLowStockTable();
            } else {
                apiFailures++;
                showError('Failed to load low stock items: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                lowStockItems = mockData.lowStockItems;
                updateLowStockTable();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load low stock items: ' + error);
            // Use mock data
            lowStockItems = mockData.lowStockItems;
            updateLowStockTable();
        }
    });
    
    // Load inventory value
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'InventoryValue'
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                inventoryValue = data || [];
                updateInventoryValue();
            } else {
                apiFailures++;
                showError('Failed to load inventory value: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                inventoryValue = mockData.inventoryValue;
                updateInventoryValue();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load inventory value: ' + error);
            // Use mock data
            inventoryValue = mockData.inventoryValue;
            updateInventoryValue();
        }
    });
    
    // Load top selling products
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'TopSellProducts',
            DateFrom: dateFrom,
            DateTo: dateTo
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                topProducts = data || [];
                updateTopProductsTable();
            } else {
                apiFailures++;
                showError('Failed to load top products: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                topProducts = mockData.topProducts;
                updateTopProductsTable();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load top products: ' + error);
            // Use mock data
            topProducts = mockData.topProducts;
            updateTopProductsTable();
        }
    });
    
    // Load sales by method
    $.ajax({
        url: 'api_proxy.php',
        data: { 
            endpoint: 'SalesByMethod',
            DateFrom: dateFrom,
            DateTo: dateTo
        },
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data && !data.error) {
                salesByMethod = data || [];
                updatePaymentMethods();
            } else {
                apiFailures++;
                showError('Failed to load payment methods: ' + (data && data.error ? data.error : 'Unknown error'));
                // Use mock data
                salesByMethod = mockData.salesByMethod;
                updatePaymentMethods();
            }
        },
        error: function(xhr, status, error) {
            apiFailures++;
            showError('Failed to load payment methods: ' + error);
            // Use mock data
            salesByMethod = mockData.salesByMethod;
            updatePaymentMethods();
        },
        complete: function() {
            // Check if we had API failures and set demo mode flag
            if (apiFailures > 0) {
                localStorage.setItem('dashboard_api_failures', 'true');
                checkDemoMode();
            }
        }
    });
}

// Show loading state
function showLoadingState() {
    console.log("Loading data...");
    // You could add loading spinners here
}

// Show error message
function showError(message) {
    console.error(message);
    // You could show a toast or alert here
}

// Format currency
function formatCurrency(amount) {
    if (amount === undefined || amount === null) {
        return '$0.00';
    }
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
}

// Format percentage
function formatPercentage(value) {
    if (value === undefined || value === null) {
        return '0.0%';
    }
    return new Intl.NumberFormat('en-US', { style: 'percent', minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(value / 100);
}

// Safe number formatting
function safeToLocaleString(value) {
    if (value === undefined || value === null) {
        return '0';
    }
    return value.toLocaleString();
}

// Update company info
function updateCompanyInfo() {
    if (!companyInfo || typeof companyInfo !== 'object') {
        showError('Invalid company info data');
        return;
    }
    
    $('.company-name').text(companyInfo.Name || 'Company Name');
    
    let address = companyInfo.Address1 || '';
    if (companyInfo.Address2) {
        address += ', ' + companyInfo.Address2;
    }
    address += ', ' + (companyInfo.City || '') + ', ' + (companyInfo.Country || '') + ' ' + (companyInfo.ZipCode || '');
    
    $('#company-address-text').text(address);
    $('#company-phone').text(companyInfo.Phone || 'N/A');
    $('#company-email').text(companyInfo.Email || 'N/A');
}

// Update sales totals
function updateSalesTotals() {
    if (!salesTotals || typeof salesTotals !== 'object') {
        $('.total-sales').text('$0.00');
        $('.profit-margin').text('0.0%');
        $('.transaction-count').text('0');
        $('.profit-status').text('No data available').removeClass('text-danger text-warning text-success').addClass('text-muted');
        return;
    }
    
    $('.total-sales').text(formatCurrency(salesTotals.TotalSales || 0));
    
    // Calculate profit margin
    const totalSales = salesTotals.TotalSales || 0;
    const totalCost = salesTotals.TotalCost || 0;
    const grossProfit = totalSales - totalCost;
    const profitMargin = totalSales > 0 ? (grossProfit / totalSales) * 100 : 0;
    
    $('.profit-margin').text(formatPercentage(profitMargin));
    $('.transaction-count').text(safeToLocaleString(salesTotals.TransactionCount || 0));
    
    // Set profit status text
    if (profitMargin > 30) {
        $('.profit-status').text('Excellent profit margin').removeClass('text-danger text-warning text-muted').addClass('text-success');
    } else if (profitMargin > 20) {
        $('.profit-status').text('Good profit margin').removeClass('text-danger text-success text-muted').addClass('text-primary');
    } else if (profitMargin > 10) {
        $('.profit-status').text('Average profit margin').removeClass('text-danger text-success text-muted').addClass('text-warning');
    } else {
        $('.profit-status').text('Low profit margin').removeClass('text-success text-warning text-muted').addClass('text-danger');
    }
}

// Update sales timeframe
function updateSalesTimeframe(timeframe) {
    let newDateFrom;
    const today = moment();
    
    switch(timeframe) {
        case 'today':
            newDateFrom = today.format('YYYY-MM-DD');
            break;
        case 'week':
            newDateFrom = today.subtract(6, 'days').format('YYYY-MM-DD');
            break;
        case 'month':
            newDateFrom = today.subtract(29, 'days').format('YYYY-MM-DD');
            break;
        case 'year':
            newDateFrom = today.subtract(364, 'days').format('YYYY-MM-DD');
            break;
        default:
            newDateFrom = moment().subtract(30, 'days').format('YYYY-MM-DD');
    }
    
    dateFrom = newDateFrom;
    dateTo = moment().format('YYYY-MM-DD');
    
    // Update date range picker
    $('#date-range').data('daterangepicker').setStartDate(moment(dateFrom));
    $('#date-range').data('daterangepicker').setEndDate(moment(dateTo));
    
    // Reload data
    loadDashboardData();
}

// Update category chart
function updateCategoryChart() {
    if (!Array.isArray(salesByCategory) || salesByCategory.length === 0) {
        // If no data, create an empty chart
        const ctx = document.getElementById('categoryChart').getContext('2d');
        if (window.categoryChart instanceof Chart) {
            window.categoryChart.destroy();
        }
        window.categoryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data Available'],
                datasets: [{
                    label: 'Sales',
                    data: [0],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'No category data available for the selected period'
                    }
                }
            }
        });
        return;
    }
    
    // Sort categories by sales
    salesByCategory.sort((a, b) => (b.TotalSales || 0) - (a.TotalSales || 0));
    
    // Take top 10 categories
    const topCategories = salesByCategory.slice(0, 10);
    
    const labels = topCategories.map(cat => cat.CategoryName || 'Unknown');
    const salesData = topCategories.map(cat => cat.TotalSales || 0);
    const profitData = topCategories.map(cat => cat.TotalProfit || 0);
    
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.categoryChart instanceof Chart) {
        window.categoryChart.destroy();
    }
    
    window.categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Sales',
                    data: salesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Profit',
                    data: profitData,
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
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatCurrency(context.raw);
                        }
                    }
                }
            }
        }
    });
}

// Update department chart
function updateDepartmentChart() {
    if (!Array.isArray(salesByDepartment) || salesByDepartment.length === 0) {
        // If no data, create an empty chart
        const ctx = document.getElementById('departmentChart').getContext('2d');
        if (window.departmentChart instanceof Chart) {
            window.departmentChart.destroy();
        }
        window.departmentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data Available'],
                datasets: [{
                    label: 'Sales',
                    data: [0],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'No department data available for the selected period'
                    }
                }
            }
        });
        return;
    }
    
    // Sort departments by sales
    salesByDepartment.sort((a, b) => (b.TotalSales || 0) - (a.TotalSales || 0));
    
    const labels = salesByDepartment.map(dept => dept.DepartmentName || 'Unknown');
    const salesData = salesByDepartment.map(dept => dept.TotalSales || 0);
    const profitData = salesByDepartment.map(dept => dept.TotalProfit || 0);
    const marginData = salesByDepartment.map(dept => dept.ProfitMarginPercentage || 0);
    
    const ctx = document.getElementById('departmentChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.departmentChart instanceof Chart) {
        window.departmentChart.destroy();
    }
    
    window.departmentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Sales',
                    data: salesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Profit Margin %',
                    data: marginData,
                    type: 'line',
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    yAxisID: 'y1',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Profit Margin %') {
                                return context.dataset.label + ': ' + (context.raw || 0).toFixed(1) + '%';
                            }
                            return context.dataset.label + ': ' + formatCurrency(context.raw);
                        }
                    }
                }
            }
        }
    });
}

// Update hourly chart
function updateHourlyChart() {
    if (!Array.isArray(salesByHour) || salesByHour.length === 0) {
        // If no data, create an empty chart
        const ctx = document.getElementById('hourlyDistributionChart').getContext('2d');
        if (window.hourlyChart instanceof Chart) {
            window.hourlyChart.destroy();
        }
        window.hourlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['No Data Available'],
                datasets: [{
                    label: 'Sales',
                    data: [0],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'No hourly data available for the selected period'
                    }
                }
            }
        });
        return;
    }
    
    // Sort by hour
    salesByHour.sort((a, b) => (a.HourOfDay || 0) - (b.HourOfDay || 0));
    
    const labels = salesByHour.map(hour => (hour.HourOfDay || 0) + ':00');
    const salesData = salesByHour.map(hour => hour.TotalSales || 0);
    const transactionData = salesByHour.map(hour => hour.TransactionCount || 0);
    
    const ctx = document.getElementById('hourlyDistributionChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.hourlyChart instanceof Chart) {
        window.hourlyChart.destroy();
    }
    
    window.hourlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Sales',
                    data: salesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Transactions',
                    data: transactionData,
                    type: 'line',
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    yAxisID: 'y1',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Transactions') {
                                return context.dataset.label + ': ' + (context.raw || 0).toLocaleString();
                            }
                            return context.dataset.label + ': ' + formatCurrency(context.raw);
                        }
                    }
                }
            }
        }
    });
}

// Update monthly trend chart
function updateMonthlyTrendChart() {
    if (!Array.isArray(salesTrend) || salesTrend.length === 0) {
        // If no data, create an empty chart
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        if (window.monthlyChart instanceof Chart) {
            window.monthlyChart.destroy();
        }
        window.monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['No Data Available'],
                datasets: [{
                    label: 'Sales',
                    data: [0],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'No monthly trend data available'
                    }
                }
            }
        });
        return;
    }
    
    // Sort by date
    salesTrend.sort((a, b) => {
        const yearA = a.Year || 0;
        const yearB = b.Year || 0;
        const monthA = a.Month || 0;
        const monthB = b.Month || 0;
        
        if (yearA !== yearB) {
            return yearA - yearB;
        }
        return monthA - monthB;
    });
    
    const labels = salesTrend.map(month => {
        const year = month.Year || 2000;
        const monthNum = month.Month || 1;
        const date = new Date(year, monthNum - 1);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });
    
    const salesData = salesTrend.map(month => month.TotalSales || 0);
    const profitData = salesTrend.map(month => month.TotalProfit || (month.TotalSales ? month.TotalSales * 0.25 : 0)); // Fallback if TotalProfit is not available
    
    const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.monthlyChart instanceof Chart) {
        window.monthlyChart.destroy();
    }
    
    window.monthlyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Sales',
                    data: salesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Profit',
                    data: profitData,
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatCurrency(context.raw);
                        }
                    }
                }
            }
        }
    });
}

// Update profit trend chart
function updateProfitTrendChart() {
    if (!Array.isArray(salesTrend) || salesTrend.length === 0) {
        // If no data, create an empty chart
        const ctx = document.getElementById('profitTrendChart').getContext('2d');
        if (window.profitTrendChart instanceof Chart) {
            window.profitTrendChart.destroy();
        }
        window.profitTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['No Data Available'],
                datasets: [{
                    label: 'Profit Margin',
                    data: [0],
                    backgroundColor: 'rgba(75, 192, 192, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        return;
    }
    
    // Use last 6 months for the small profit trend chart
    const recentTrend = [...salesTrend].sort((a, b) => {
        const yearA = a.Year || 0;
        const yearB = b.Year || 0;
        const monthA = a.Month || 0;
        const monthB = b.Month || 0;
        
        if (yearA !== yearB) {
            return yearA - yearB;
        }
        return monthA - monthB;
    }).slice(-6);
    
    const labels = recentTrend.map(month => {
        const year = month.Year || 2000;
        const monthNum = month.Month || 1;
        const date = new Date(year, monthNum - 1);
        return date.toLocaleDateString('en-US', { month: 'short' });
    });
    
    // Calculate profit margins
    const marginData = recentTrend.map(month => {
        if (month.TotalProfit && month.TotalSales && month.TotalSales > 0) {
            return (month.TotalProfit / month.TotalSales) * 100;
        }
        return month.GrossMarginPercentage || 25; // Fallback
    });
    
    const ctx = document.getElementById('profitTrendChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.profitTrendChart instanceof Chart) {
        window.profitTrendChart.destroy();
    }
    
    window.profitTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Profit Margin %',
                    data: marginData,
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Profit Margin: ' + (context.raw || 0).toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
}

// Update low stock table
function updateLowStockTable() {
    if (!Array.isArray(lowStockItems) || lowStockItems.length === 0) {
        $('#low-stock-table tbody').html('<tr><td colspan="4" class="text-center">No low stock items found</td></tr>');
        return;
    }
    
    // Sort by stock level (ascending)
    lowStockItems.sort((a, b) => (a.CurrentStock || 0) - (b.CurrentStock || 0));
    
    // Take top 10 items with lowest stock
    const criticalItems = lowStockItems.slice(0, 10);
    
    let tableHtml = '';
    
    criticalItems.forEach(item => {
        const currentStock = item.CurrentStock || 0;
        const minimumLevel = item.MinimumLevel || 1;
        
        const stockStatus = currentStock < minimumLevel ? 'low-stock-warning' : 
                           (currentStock < minimumLevel * 1.5 ? 'medium-stock-warning' : 'good-stock');
        
        tableHtml += `
            <tr>
                <td>${item.ProductName || 'Unknown Product'}</td>
                <td>${item.Department || 'Unknown Department'}</td>
                <td class="${stockStatus}">${currentStock} / ${minimumLevel}</td>
                <td>
                    <button class="btn btn-sm btn-primary btn-action" onclick="orderProduct('${item.ProductCode || ''}')">
                        <i class="fas fa-shopping-cart me-1"></i> Order
                    </button>
                </td>
            </tr>
        `;
    });
    
    $('#low-stock-table tbody').html(tableHtml);
}

// Update inventory value
function updateInventoryValue() {
    if (!Array.isArray(inventoryValue) || inventoryValue.length === 0) {
        $('.total-inventory-value').text('Total Inventory Value: $0.00');
        $('#inventory-value-breakdown').html('<div class="alert alert-info">No inventory data available</div>');
        return;
    }
    
    // Find the total row
    const totalRow = inventoryValue.find(row => row.Department === 'TOTAL');
    
    if (totalRow) {
        $('.total-inventory-value').text('Total Inventory Value: ' + formatCurrency(totalRow.TotalRetail || 0));
    } else {
        $('.total-inventory-value').text('Total Inventory Value: $0.00');
    }
    
    // Filter out the total row for the breakdown
    const departments = inventoryValue.filter(row => row.Department !== 'TOTAL');
    
    let breakdownHtml = '';
    
    if (departments.length === 0) {
        breakdownHtml = '<div class="alert alert-info">No department breakdown available</div>';
    } else {
        departments.forEach(dept => {
            const itemCount = dept.ItemCount || 0;
            const totalCost = dept.TotalCost || 0;
            const totalRetail = dept.TotalRetail || 0;
            
            breakdownHtml += `
                <div class="department-value">
                    <div class="department-name">${dept.Department || 'Unknown Department'}</div>
                    <div class="department-stats">
                        <div class="department-stat">
                            <div class="department-stat-value">${safeToLocaleString(itemCount)}</div>
                            <div class="department-stat-label">Items</div>
                        </div>
                        <div class="department-stat">
                            <div class="department-stat-value">${formatCurrency(totalCost)}</div>
                            <div class="department-stat-label">Cost</div>
                        </div>
                        <div class="department-stat">
                            <div class="department-stat-value">${formatCurrency(totalRetail)}</div>
                            <div class="department-stat-label">Retail</div>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    $('#inventory-value-breakdown').html(breakdownHtml);
}

// Update top products table
function updateTopProductsTable() {
    if (!Array.isArray(topProducts) || topProducts.length === 0) {
        $('#top-products-table tbody').html('<tr><td colspan="7" class="text-center">No product data available</td></tr>');
        return;
    }
    
    // Sort by quantity sold (descending)
    topProducts.sort((a, b) => (b.TotalQuantitySold || 0) - (a.TotalQuantitySold || 0));
    
    let tableHtml = '';
    
    topProducts.forEach(product => {
        const currentStock = product.CurrentStock || 0;
        
        const stockStatus = currentStock < 10 ? 'low-stock-warning' : 
                           (currentStock < 20 ? 'medium-stock-warning' : '');
        
        const profitMargin = product.ProfitMarginPercentage || 0;
        
        tableHtml += `
            <tr>
                <td>${product.ProductName || 'Unknown Product'}</td>
                <td>${product.Department || 'Unknown'}</td>
                <td>${product.Category || 'Unknown'}</td>
                <td>${safeToLocaleString(product.TotalQuantitySold || 0)}</td>
                <td>${formatCurrency(product.TotalSales || 0)}</td>
                <td>${profitMargin.toFixed(1)}%</td>
                <td class="${stockStatus}">${safeToLocaleString(currentStock)}</td>
            </tr>
        `;
    });
    
    $('#top-products-table tbody').html(tableHtml);
    
    // Initialize DataTable if not already initialized
    if (!$.fn.DataTable.isDataTable('#top-products-table')) {
        $('#top-products-table').DataTable({
            pageLength: 10,
            order: [[3, 'desc']], // Sort by quantity sold
            responsive: true
        });
    } else {
        // Refresh the table
        $('#top-products-table').DataTable().clear().rows.add($(tableHtml)).draw();
    }
}

// Update payment methods
function updatePaymentMethods() {
    if (!Array.isArray(salesByMethod) || salesByMethod.length === 0) {
        $('#cash-progress').css('width', '0%');
        $('#credit-progress').css('width', '0%');
        $('#debit-progress').css('width', '0%');
        $('#cash-percentage').text('0%');
        $('#credit-percentage').text('0%');
        $('#debit-percentage').text('0%');
        return;
    }
    
    // Calculate totals for each payment method
    let totalCash = 0;
    let totalCredit = 0;
    let totalDebit = 0;
    let totalPayments = 0;
    
    salesByMethod.forEach(day => {
        totalCash += day.CashPayments || 0;
        totalCredit += day.CreditCardPayments || 0;
        totalDebit += day.DebitCardPayments || 0;
    });
    
    totalPayments = totalCash + totalCredit + totalDebit;
    
    // Calculate percentages
    const cashPercentage = totalPayments > 0 ? (totalCash / totalPayments) * 100 : 0;
    const creditPercentage = totalPayments > 0 ? (totalCredit / totalPayments) * 100 : 0;
    const debitPercentage = totalPayments > 0 ? (totalDebit / totalPayments) * 100 : 0;
    
    // Update progress bars
    $('#cash-progress').css('width', cashPercentage + '%');
    $('#credit-progress').css('width', creditPercentage + '%');
    $('#debit-progress').css('width', debitPercentage + '%');
    
    // Update percentage text
    $('#cash-percentage').text(cashPercentage.toFixed(1) + '%');
    $('#credit-percentage').text(creditPercentage.toFixed(1) + '%');
    $('#debit-percentage').text(debitPercentage.toFixed(1) + '%');
}

// Order product function (placeholder)
function orderProduct(productCode) {
    alert('Ordering product: ' + productCode + '\nThis is a placeholder function.');
}