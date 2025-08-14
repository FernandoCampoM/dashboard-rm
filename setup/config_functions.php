<?php
function get_configBackend() {
    $file = __DIR__ . '/backend_config.json';
    if (file_exists($file)) {
        $json = file_get_contents($file);
        return json_decode($json, true);
    }
    return null;
}

function save_configBackend($backend_ip, $backend_port) {
    $config = [
        'backend_ip' => $backend_ip,
        'backend_port' => $backend_port
    ];
    file_put_contents(__DIR__ . '/backend_config.json', json_encode($config, JSON_PRETTY_PRINT));
}
?>
