<?php
/**
 * Script para probar la conectividad con la API
 * 
 * Este script realiza pruebas básicas de conexión con la API
 * para ayudar a diagnosticar problemas.
 * 
 * Uso: Visita http://tu-servidor/ruta/test_api.php en un navegador
 */

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para mostrar resultados de prueba con formato
function displayResult($test, $result, $details = '') {
    $resultClass = $result ? 'success' : 'error';
    echo "<div class='test-result $resultClass'>";
    echo "<h3>" . ($result ? '✅' : '❌') . " $test</h3>";
    if (!empty($details)) {
        echo "<pre>$details</pre>";
    }
    echo "</div>";
}

// Cargar configuración
if (file_exists('config.php')) {
    require_once 'config.php';
    displayResult("Carga de config.php", true, "La configuración se cargó correctamente");
} else {
    displayResult("Carga de config.php", false, "No se encontró el archivo config.php");
    exit;
}

// Comprobar constantes API
$apiBaseUrlDefined = defined('API_BASE_URL');
$apiUsernameDefined = defined('API_USERNAME');
$apiPasswordDefined = defined('API_PASSWORD');

displayResult(
    "Comprobación de constantes API", 
    $apiBaseUrlDefined && $apiUsernameDefined && $apiPasswordDefined,
    "API_BASE_URL: " . ($apiBaseUrlDefined ? API_BASE_URL : 'NO DEFINIDA') . "\n" .
    "API_USERNAME: " . ($apiUsernameDefined ? '******' : 'NO DEFINIDA') . "\n" .
    "API_PASSWORD: " . ($apiPasswordDefined ? '******' : 'NO DEFINIDA')
);

// Prueba de conexión a la API
if ($apiBaseUrlDefined && $apiUsernameDefined && $apiPasswordDefined) {
    // Probar endpoints básicos
    $endpoints = ['InfoCompany', 'InventoryDepartments', 'InventoryCategories'];
    
    foreach ($endpoints as $endpoint) {
        echo "<h3>Probando endpoint: $endpoint</h3>";
        
        try {
            // Intentar llamada a la API
            $result = callAPI($endpoint);
            
            if ($result === false) {
                displayResult(
                    "Llamada a $endpoint", 
                    false, 
                    "La llamada a la API falló. Verifica los logs para más detalles."
                );
            } else {
                $resultJson = json_encode($result, JSON_PRETTY_PRINT);
                displayResult(
                    "Llamada a $endpoint", 
                    true, 
                    "Respuesta recibida:\n" . substr($resultJson, 0, 1000) . 
                    (strlen($resultJson) > 1000 ? "\n... (respuesta truncada)" : "")
                );
            }
        } catch (Exception $e) {
            displayResult(
                "Llamada a $endpoint", 
                false, 
                "Excepción: " . $e->getMessage()
            );
        }
    }
}

// Prueba de permisos de directorios
$directories = [
    'logs',
    'data',
    'uploads/products'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        // Intentar crear el directorio
        $created = @mkdir($dir, 0755, true);
        displayResult(
            "Directorio $dir", 
            $created, 
            $created ? "El directorio no existía y se creó correctamente" : "El directorio no existe y no se pudo crear"
        );
    } else {
        // Comprobar permisos de escritura
        $writable = is_writable($dir);
        displayResult(
            "Directorio $dir", 
            $writable, 
            $writable ? "El directorio existe y tiene permisos de escritura" : "El directorio existe pero NO tiene permisos de escritura"
        );
    }
}

// Comprobar función de registro
$logDir = 'logs';
$logFile = $logDir . '/test_' . date('Y-m-d') . '.log';
$logSuccess = false;

if (file_exists($logDir) && is_writable($logDir)) {
    $logMessage = date('Y-m-d H:i:s') . " - Prueba de registro\n";
    $logSuccess = @file_put_contents($logFile, $logMessage, FILE_APPEND) !== false;
}

displayResult(
    "Registro en log", 
    $logSuccess, 
    $logSuccess ? "Mensaje de prueba guardado en $logFile" : "No se pudo escribir en el archivo de log"
);

// Estilo para la página
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
    h1, h2 { color: #333; }
    .test-result { margin: 10px 0; padding: 15px; border-radius: 5px; }
    .success { background-color: #d4edda; border-left: 5px solid #28a745; }
    .error { background-color: #f8d7da; border-left: 5px solid #dc3545; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow: auto; }
</style>";

// Finalizar con un resumen
echo "<h2>Resumen de la prueba</h2>";
echo "<p>Fecha y hora: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";