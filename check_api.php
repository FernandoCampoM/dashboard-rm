<?php
// Simple API Check Tool
require_once 'config.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test the InfoCompany endpoint (simplest one)
try {
    $result = callAPI('InfoCompany', []);
    
    if ($result !== false) {
        echo "<h2 style='color:green'>API Connection Successful!</h2>";
        echo "<p>The API connection is working correctly. Your dashboard should now be functioning.</p>";
        echo "<p><a href='index.php'>Go to Dashboard</a></p>";
        
        echo "<h3>Sample API Response:</h3>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } else {
        echo "<h2 style='color:red'>API Connection Failed</h2>";
        echo "<p>The API call returned false. Check your API credentials and connection.</p>";
    }
} catch (Exception $e) {
    echo "<h2 style='color:red'>API Connection Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>