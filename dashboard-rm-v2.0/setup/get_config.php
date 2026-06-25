<?php
require_once 'config_functions.php'; // Aquí está get_config()

$config = get_configBackend();
if ($config) {
    echo json_encode([
        "status" => "ok",
        "config" => $config
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No hay configuración guardada"
    ]);
}
