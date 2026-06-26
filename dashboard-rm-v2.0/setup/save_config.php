<?php
require_once 'config_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer el cuerpo crudo de la petición y decodificar JSON
    $input = json_decode(file_get_contents("php://input"), true);

    $backend_ip = $input['backend_ip'] ?? '';
    $backend_port = $input['backend_port'] ?? '';
    $monthsOfCover = $input['inventoryMonthsOfCover'] ?? '';
    $name = $input['name'] ?? '';
    $isSelected = $input['isSelected'] ?? true;
    $backend_index = $input['backend_index'] ?? null;
    $action = $input['action'] ?? 'save';

    if ($action === 'select') {
        if ($backend_index !== null && select_configBackend($backend_index)) {
            $sessionClosed = close_active_session_if_needed();
            echo json_encode([
                "status" => "ok",
                "sessionClosed" => $sessionClosed
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No se encontró el backend seleccionado."
            ]);
        }

        exit;
    }

    if ($action === 'delete') {
        $deletedBackendWasSelected = is_backend_selected_by_index($backend_index);

        if ($backend_index !== null && delete_configBackend($backend_index)) {
            $sessionClosed = $deletedBackendWasSelected ? close_active_session_if_needed() : false;
            echo json_encode([
                "status" => "ok",
                "sessionClosed" => $sessionClosed
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No se encontró el backend que quieres eliminar."
            ]);
        }

        exit;
    }

    if (!empty($monthsOfCover) && empty($backend_ip)) {
        save_inventoryMonthsOfCover($monthsOfCover);
        echo json_encode(["status" => "ok"]);
    } else if (!empty($name) && !empty($backend_ip) && !empty($backend_port) && !empty($monthsOfCover)) {
        save_configBackend($backend_ip, $backend_port, $name, $monthsOfCover, $isSelected, $backend_index);
        $sessionClosed = filter_var($isSelected, FILTER_VALIDATE_BOOLEAN)
            ? close_active_session_if_needed()
            : false;

        echo json_encode([
            "status" => "ok",
            "sessionClosed" => $sessionClosed
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Por favor, completa todos los campos."
        ]);
    }
}

function is_backend_selected_by_index($backend_index) {
    if ($backend_index === null) {
        return false;
    }

    $config = get_configBackend();
    $index = (int) $backend_index;

    return isset($config['backends'][$index]) && !empty($config['backends'][$index]['isSelected']);
}

function close_active_session_if_needed() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $hasActiveSession = !empty($_SESSION['loggedin']);

    if (!$hasActiveSession) {
        session_write_close();
        return false;
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
    return true;
}
