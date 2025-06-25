<?php
// Configuration Check Tool
// This file helps diagnose issues with the config.php file

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
    <title>Configuration Check Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 30px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; }
        .error { color: #d9534f; font-weight: bold; }
        .success { color: #5cb85c; font-weight: bold; }
        .warning { color: #f0ad4e; font-weight: bold; }
        .code-block { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .config-check { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Configuration Check Tool</h1>
    
    <div class="config-check">
        <h2>Configuration File Check</h2>
        
        <?php
        // Check if config.php exists
        if (!file_exists('config.php')) {
            echo "<p class='error'>Error: config.php file not found</p>";
            echo "<p>Make sure your config.php file exists in the same directory as this script.</p>";
        } else {
            echo "<p class='success'>✓ config.php file found</p>";
            
            // Try to include the config file
            try {
                require_once 'config.php';
                echo "<p class='success'>✓ config.php file loaded successfully</p>";
            } catch (Exception $e) {
                echo "<p class='error'>Error loading config.php: " . $e->getMessage() . "</p>";
            }
            
            // Check if callAPI function exists
            if (!function_exists('callAPI')) {
                echo "<p class='error'>Error: callAPI function not found in config.php</p>";
                echo "<p>Make sure your config.php file contains the callAPI function and it's properly defined.</p>";
                
                // Provide a template for the callAPI function
                echo "<div class='code-block'>";
                echo "<h3>Example callAPI function:</h3>";
                echo "<pre>";
                echo htmlspecialchars("<?php
// Configuration file
// This file contains configuration settings and the callAPI function

// API credentials
define('API_USERNAME', 'your_username');
define('API_PASSWORD', 'your_password');
define('API_BASE_URL', 'https://your-api-base-url.com/api/');

/**
 * Call the API with Basic Authentication
 * 
 * @param string \$endpoint The API endpoint to call
 * @param array \$params Additional parameters to pass to the API
 * @return mixed The API response or false on error
 */
function callAPI(\$endpoint, \$params = []) {
    // Build the API URL
    \$url = API_BASE_URL . \$endpoint;
    
    // Add parameters to the URL if any
    if (!empty(\$params)) {
        \$url .= '?' . http_build_query(\$params);
    }
    
    // Initialize cURL
    \$ch = curl_init();
    
    // Set cURL options
    curl_setopt(\$ch, CURLOPT_URL, \$url);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt(\$ch, CURLOPT_USERPWD, API_USERNAME . ':' . API_PASSWORD);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 30);
    curl_setopt(\$ch, CURLOPT_SSL_VERIFYPEER, false); // For development only, remove in production
    
    // Execute the cURL request
    \$response = curl_exec(\$ch);
    \$httpCode = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno(\$ch)) {
        error_log('cURL Error: ' . curl_error(\$ch));
        curl_close(\$ch);
        return false;
    }
    
    // Close cURL
    curl_close(\$ch);
    
    // Check HTTP status code
    if (\$httpCode != 200) {
        error_log('API Error: HTTP Status Code ' . \$httpCode);
        error_log('API Response: ' . \$response);
        return false;
    }
    
    // Decode the JSON response
    \$data = json_decode(\$response, true);
    
    // Check if JSON was valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('API Error: Invalid JSON response');
        error_log('API Response: ' . \$response);
        return false;
    }
    
    return \$data;
}");
                echo "</pre>";
                echo "</div>";
            } else {
                echo "<p class='success'>✓ callAPI function found</p>";
                
                // Check if API credentials are defined
                $credentialsFound = true;
                
                if (!defined('API_USERNAME')) {
                    echo "<p class='error'>Error: API_USERNAME constant not defined in config.php</p>";
                    $credentialsFound = false;
                }
                
                if (!defined('API_PASSWORD')) {
                    echo "<p class='error'>Error: API_PASSWORD constant not defined in config.php</p>";
                    $credentialsFound = false;
                }
                
                if (!defined('API_BASE_URL')) {
                    echo "<p class='error'>Error: API_BASE_URL constant not defined in config.php</p>";
                    $credentialsFound = false;
                }
                
                if ($credentialsFound) {
                    echo "<p class='success'>✓ API credentials found</p>";
                    
                    // Test the API connection
                    echo "<h3>Testing API Connection</h3>";
                    
                    try {
                        $result = callAPI('InfoCompany', []);
                        
                        if ($result !== false) {
                            echo "<p class='success'>✓ Successfully connected to API</p>";
                            echo "<p>API returned data:</p>";
                            echo "<pre>";
                            print_r($result);
                            echo "</pre>";
                        } else {
                            echo "<p class='error'>Error: Could not connect to API</p>";
                            echo "<p>The callAPI function returned false. Check your API credentials and connection.</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
                    }
                } else {
                    echo "<p class='warning'>Warning: API credentials not found. Cannot test API connection.</p>";
                }
            }
        }
        ?>
    </div>
    
    <h2>Troubleshooting Tips</h2>
    <ul>
        <li>Make sure your config.php file contains the correct API credentials</li>
        <li>Check that the callAPI function is properly implemented</li>
        <li>Verify that the API endpoints are accessible from your server</li>
        <li>Check for PHP errors in your server logs</li>
        <li>Try using the <a href="api_debug.php">API Debug Tool</a> to test specific endpoints</li>
    </ul>
    
    // Actualizar la sección de Next Steps
echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='api_debug.php'>Use the API Debug Tool</a> to test specific API endpoints</li>";
echo "<li><a href='index.php'>Go to Dashboard</a> to see if the issues are resolved</li>";
echo "</ul>";
</body>
</html>