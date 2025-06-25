<?php
// Enhanced API Debug Tool
// This file helps diagnose API connection issues

// Include configuration file
require_once 'config.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set content type to HTML
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 30px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; }
        .error { color: #d9534f; font-weight: bold; }
        .success { color: #5cb85c; }
        .endpoint { margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .endpoint:hover { background: #f9f9f9; }
        .endpoint a { text-decoration: none; color: #0275d8; display: block; }
        .params { margin-top: 20px; }
        .param-group { margin-bottom: 15px; }
        button { padding: 8px 15px; background: #0275d8; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #025aa5; }
        input, select { padding: 8px; margin-right: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .raw-response { margin-top: 20px; }
        .config-check { margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
        .test-result { margin-top: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Enhanced API Debug Tool</h1>
    
    <?php
    // Check if config.php exists and has the callAPI function
    echo "<div class='config-check'>";
    echo "<h2>Configuration Check</h2>";
    
    if (!function_exists('callAPI')) {
        echo "<p class='error'>Error: callAPI function not found in config.php</p>";
        echo "<p>Make sure your config.php file contains the callAPI function and it's properly defined.</p>";
    } else {
        echo "<p class='success'>✓ callAPI function found</p>";
    }
    
    // Test basic API connectivity
    echo "<h3>Testing Basic API Connectivity</h3>";
    try {
        $result = callAPI('InfoCompany', []);
        if ($result !== false) {
            echo "<p class='success'>✓ Successfully connected to API</p>";
        } else {
            echo "<p class='error'>Error: Could not connect to API</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // Get the endpoint from the query string
    $endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'InfoCompany';
    
    // Get additional parameters
    $params = [];
    foreach ($_GET as $key => $value) {
        if ($key !== 'endpoint' && $key !== 'test') {
            $params[$key] = $value;
        }
    }
    
    // Default parameters if none provided
    if (empty($params) && $endpoint !== 'InfoCompany') {
        $params = [
            'DateFrom' => date('Y-m-d', strtotime('-30 days')),
            'DateTo' => date('Y-m-d')
        ];
    }
    
    echo "<h2>Testing Endpoint: $endpoint</h2>";
    
    // Parameter form
    echo "<div class='params'>";
    echo "<h3>Parameters</h3>";
    echo "<form method='get'>";
    echo "<input type='hidden' name='endpoint' value='$endpoint'>";
    
    // Common parameters for date-based endpoints
    if (in_array($endpoint, ['SalesTotals', 'SalesByHour', 'SalesByCategory', 'SalesByDepartment', 'SalesByMethod', 'TopSellProducts', 'SaleTrendByMonth'])) {
        $dateFrom = isset($_GET['DateFrom']) ? $_GET['DateFrom'] : date('Y-m-d', strtotime('-30 days'));
        $dateTo = isset($_GET['DateTo']) ? $_GET['DateTo'] : date('Y-m-d');
        
        echo "<div class='param-group'>";
        echo "<label for='DateFrom'>Date From: </label>";
        echo "<input type='date' id='DateFrom' name='DateFrom' value='$dateFrom'>";
        echo "</div>";
        
        echo "<div class='param-group'>";
        echo "<label for='DateTo'>Date To: </label>";
        echo "<input type='date' id='DateTo' name='DateTo' value='$dateTo'>";
        echo "</div>";
    }
    
    // Add specific parameters for certain endpoints
    if ($endpoint === 'LowLevelItems') {
        $active = isset($_GET['Active']) ? $_GET['Active'] : '1';
        echo "<div class='param-group'>";
        echo "<label for='Active'>Active: </label>";
        echo "<select id='Active' name='Active'>";
        echo "<option value='1'" . ($active == '1' ? ' selected' : '') . ">Yes</option>";
        echo "<option value='0'" . ($active == '0' ? ' selected' : '') . ">No</option>";
        echo "</select>";
        echo "</div>";
    }
    
    echo "<button type='submit'>Test Endpoint</button>";
    echo "</form>";
    echo "</div>";
    
    // Display current parameters
    echo "<h3>Current Parameters:</h3>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";
    
    // Test the API call
    echo "<h3>API Response:</h3>";
    try {
        // Make the API call
        $result = callAPI($endpoint, $params);
        
        // Display the result
        if ($result !== false) {
            echo "<pre>";
            print_r($result);
            echo "</pre>";
        } else {
            echo "<p class='error'>Error: API call returned false</p>";
        }
        
        // Show raw response for debugging
        echo "<div class='raw-response'>";
        echo "<h3>Raw API Response (for debugging):</h3>";
        
        // Make a direct API call and capture the raw response
        // This is a placeholder - you'll need to modify this based on how your callAPI function works
        echo "<p>To see the raw API response, you may need to modify your callAPI function to return the raw response.</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
    // List of available endpoints
    echo "<h2>Available Endpoints</h2>";
    echo "<div class='endpoints'>";
    $endpoints = [
        'InfoCompany' => 'Company Information',
        'SalesTotals' => 'Sales Totals',
        'SalesByHour' => 'Sales By Hour',
        'SalesByCategory' => 'Sales By Category',
        'SalesByDepartment' => 'Sales By Department',
        'SalesByMethod' => 'Sales By Payment Method',
        'TopSellProducts' => 'Top Selling Products',
        'InventoryValue' => 'Inventory Value',
        'SaleTrendByMonth' => 'Sales Trend By Month',
        'LowLevelItems' => 'Low Stock Items'
    ];
    
    foreach ($endpoints as $ep => $description) {
        echo "<div class='endpoint'>";
        echo "<a href='api_debug.php?endpoint=$ep'><strong>$ep</strong> - $description</a>";
        echo "</div>";
    }
    echo "</div>";
    
    // Add a section to test the API proxy
    echo "<h2>Test API Proxy</h2>";
    echo "<p>This will test the api_proxy.php file to ensure it's correctly forwarding requests to the API.</p>";
    echo "<form method='get'>";
    echo "<input type='hidden' name='test' value='proxy'>";
    echo "<div class='param-group'>";
    echo "<label for='proxy-endpoint'>Endpoint: </label>";
    echo "<select id='proxy-endpoint' name='endpoint'>";
    foreach ($endpoints as $ep => $description) {
        echo "<option value='$ep'>$ep</option>";
    }
    echo "</select>";
    echo "</div>";
    echo "<button type='submit'>Test API Proxy</button>";
    echo "</form>";
    
    // If testing the proxy
    if (isset($_GET['test']) && $_GET['test'] === 'proxy') {
        echo "<h3>API Proxy Test Results:</h3>";
        
        // Build the URL for the API proxy
        $proxyUrl = 'api_proxy.php?endpoint=' . urlencode($endpoint);
        foreach ($params as $key => $value) {
            $proxyUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        
        echo "<p>Testing URL: <code>$proxyUrl</code></p>";
        
        // Make the request to the API proxy
        $ch = curl_init($proxyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);
        
        echo "<p>HTTP Status Code: <span class='" . ($httpCode == 200 ? 'success' : 'error') . "'>$httpCode</span></p>";
        
        echo "<h4>Response Headers:</h4>";
        echo "<pre>" . htmlspecialchars($header) . "</pre>";
        
        echo "<h4>Response Body:</h4>";
        echo "<pre>" . htmlspecialchars($body) . "</pre>";
        
        // Try to parse the JSON
        $jsonValid = false;
        try {
            $jsonData = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $jsonValid = true;
                echo "<p class='success'>✓ Valid JSON response</p>";
            } else {
                echo "<p class='error'>Invalid JSON: " . json_last_error_msg() . "</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>Error parsing JSON: " . $e->getMessage() . "</p>";
        }
    }
    ?>
    
    <h2>Troubleshooting Tips</h2>
    <ul>
        <li>Make sure your config.php file contains the correct API credentials</li>
        <li>Check that the callAPI function is properly implemented</li>
        <li>Verify that the API endpoints are accessible from your server</li>
        <li>Check for PHP errors in your server logs</li>
        <li>Try simplifying the parameters to isolate the issue</li>
    </ul>
</body>
</html>