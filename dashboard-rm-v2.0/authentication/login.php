<?php

/**
 * Script de inicio de sesión para el Dashboard Ejecutivo
 * Este archivo maneja tanto la presentación del formulario como la lógica de validación
 * utilizando la API de Retail Manager.
 */

// Incluir el archivo de configuración y funciones de la API
 // Asegúrate de que tu archivo de configuración se llama config.php

session_start(); // Iniciar la sesión para manejar el estado de autenticación
require_once '../config.php';
require_once '../setup/config_functions.php';
//validar si existe una configuracion del backend guardada


if (!$config) {
     header("Location: ../setup/setup-backend.php");
    exit;
}  

$backendConfig = get_configBackend();
$configuredBackends = [];
$selectedBackend = null;
$selectedBackendIndex = 0;

if (is_array($backendConfig)) {
    if (isset($backendConfig['backends']) && is_array($backendConfig['backends'])) {
        $configuredBackends = array_values($backendConfig['backends']);
    } else {
        $configuredBackends = [$backendConfig];
    }
}

foreach ($configuredBackends as $index => $backend) {
    if (!empty($backend['isSelected'])) {
        $selectedBackend = $backend;
        $selectedBackendIndex = $index;
        break;
    }
}

if (!$selectedBackend && !empty($configuredBackends)) {
    $selectedBackend = $configuredBackends[0];
}

 // 1. Verificar si el usuario ha iniciado sesión
// Se comprueba si la variable de sesión 'loggedin' está establecida y es verdadera
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    // Si ha iniciado sesión, lo redirigimos al dashboard
    header('Location: ../index.php');
    exit; // Es crucial usar exit() después de una redirección para detener la ejecución del script
}


$error_message = ''; // Variable para almacenar mensajes de error
$responseUsers = []; 
$userID =-1;
$apiErrorDuringEmployeeFetch = false;
// Llamada a la API para obtener los empleados y listarlos en autenticación/login.php
$responseUsers = callAPI('LogInDashboard', []); // Llamada a la API para obtener los empleados
if($responseUsers === false) {
    $apiErrorDuringEmployeeFetch = true; // Indicar que hubo un error al obtener los empleados
    // Manejar el error de la llamada a la API
    $error_message = "Error al conectar con el servidor de la API. Por favor, inténtelo de nuevo más tarde.";
} else {
    // Verificar si la llamada a la API fue exitosa y si se obtuvieron datos
    if ($responseUsers && isset($responseUsers['status']) && (int)$responseUsers['status'] >= 400) {
        $apiErrorDuringEmployeeFetch = true;
        $error_message =$responseUsers['message'];
    }else if (!empty($responseUsers) && is_array($responseUsers)) {
        $error_message = "";
        
    }else{
        $error_message = "No se encontraron empleados. Por favor, verifique la configuración de la API o inténtelo de nuevo más tarde.";
    }
}
// 1. Procesar el formulario si se ha enviado (método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpiar y validar los datos de entrada
    
    $userID = isset($_POST['UserID']) ? sanitizeInput($_POST['UserID']) : '';
    $username = isset($_POST['Username']) ? sanitizeInput($_POST['Username']) : '';
    $password = isset($_POST['UserPass']) ? $_POST['UserPass'] : ''; // No se limpia la contraseña con htmlspecialchars para evitar problemas de autenticación
    if (is_array($userID)) {
        $userID = ''; // Si es un arreglo, la forzamos a ser una cadena vacía.
    }
    if (is_array($username)) {
        $username = ''; // Si es un arreglo, la forzamos a ser una cadena vacía.
    }
     // A. Obtener el Username correspondiente al SelectedUserID del array $responseUsers
    $username = '';
    if (!empty($responseUsers) && is_array($responseUsers)) {
        
        foreach ($responseUsers as $employee) {
            
            if (isset($employee['ID']) && (string)$employee['ID'] === (string)$userID) {
                
                
                $username = isset($employee['Name']) ? $employee['Name'] : '';
                break;
            }
        }
    }
  
    // Verificar que todos los campos requeridos estén llenos
    if (empty($userID) || empty($username) || empty($password)) {
        $error_message = "Por favor, complete todos los campos requeridos.";
    } else {
        // Preparar los parámetros para la llamada a la API
        $params = [
            'UserID'   => $userID,
            'UserName' => $username,
            'UserPass' => $password
        ];

        // 2. Realizar la llamada a la API para validar el usuario
        // El endpoint es /ValidateUser con los parámetros UserID, UserName y UserPass
        // La documentación indica que el método es GET y los parámetros son requeridos.
        $response = callAPI('ValidateUser', $params);
        

        // 3. Evaluar la respuesta de la API
        if ($response !== false) {
            if (is_array($response) && isset($response['status']) && (int)$response['status'] >= 400) {
                $error_message = $response['message'] ?? "No se pudo completar la autenticación con el backend activo.";
            } else {
                $response_status = '';
                // La API devuelve una cadena de texto como 'Valid', 'Invalid', o 'Inactive'
                if (is_array($response) && isset($response['message'])) {
                    // Si es un arreglo (JSON decodificado), obtenemos el valor de la clave 'message'
                    $response_status = trim(str_replace('"', '', $response['message']));
                } else {
                    // Si la respuesta es una cadena de texto (como indica la documentación)
                    $response_status = trim(str_replace('"', '', $response));
                }
                switch ($response_status) {
                    case "Valid":
                        // Usuario validado correctamente.
                        // Almacenar información en la sesión y redirigir al dashboard
                        $_SESSION['loggedin'] = true;
                        $_SESSION['UserID'] = $userID;
                        $_SESSION['Username'] = $username;
                        // El endpoint es /GetEmployees y acepta el parámetro ID 
                        $employeeInfoResponse = callAPI('GetEmployees', ['ID' => $userID]);
                        
                        // Verificar si la llamada a la API fue exitosa y si se obtuvieron datos
                        if ($employeeInfoResponse !== false && !empty($employeeInfoResponse)) {
                            // La API GetEmployees devuelve los datos del empleado.
                            // Puede ser un array de un solo elemento o un objeto directo dependiendo de la implementación de callAPI.
                            // Asumimos que si es un array, el primer elemnto contiene los datos.
                            $employeeData = is_array($employeeInfoResponse) ? $employeeInfoResponse[0] : $employeeInfoResponse;
                            $_SESSION['Employee'] = $employeeData;
                            if($employeeData==null){
                                $error_message = "No se pudo obtener la información del empleado." .$employeeInfoResponse['message'] ?? '';
                                break; // Salir del switch para evitar redirección si hay un error
                            }
                            
                            // Guardar la información específica del empleado en la sesión 
                            if (isset($employeeData['Name'])) {
                                $_SESSION['EmployeeName'] = $employeeData['Name'];
                            }
                            if (isset($employeeData['SecurityLevel'])) {
                                $_SESSION['SecurityLevel'] = $employeeData['SecurityLevel'];
                            }
                            if (isset($employeeData['Acces'])) {
                                $_SESSION['AccessLevel'] = $employeeData['Acces'];
                                
                                
                            }
                            if (isset($employeeData['Salesman'])) {
                                //  'Yes'/'No' segun el empleado
                                $_SESSION['IsSalesman'] = $employeeData['Salesman'];
                            }
                            $infoCompanyResponse = callAPI('InfoCompany', ['ID' => $userID]);
                            if ($infoCompanyResponse !== false && !empty($infoCompanyResponse) && is_array($infoCompanyResponse)) {
                                // Guardar la información de la empresa en la sesión
                                $_SESSION['InfoCompany'] = $infoCompanyResponse[0] ?? $infoCompanyResponse; // Asumimos que es un array con un solo elemento o un objeto
                            } else {
                                // Manejar el caso donde no se pudo obtener la información de la empresa
                                // Por ejemplo, loggear el error o establecer valores por defecto
                                error_log("No se pudo obtener la información de la empresa para UserID: " . $userID);
                                $error_message = "No se pudo obtener la información de la empresa. Por favor, inténtelo de nuevo más tarde.";
                                break; // Salir del switch para evitar redirección si hay un error
                            }
                            
                        } else {
                            // Manejar el caso donde no se pudo obtener la información detallada del empleado
                            // Por ejemplo, loggear el error o establecer valores por defecto
                            error_log("No se pudo obtener la información detallada del empleado para UserID: " . $userID);
                            // Opcional: podrías poner valores por defecto para evitar errores en otras páginas
                            $error_message = "No se pudo obtener la información del empleado. Por favor, inténtelo de nuevo más tarde.";
                            break; // Salir del switch para evitar redirección si hay un error
                        }
                        header("Location: ../index.php"); // Redirigir a la página principal del dashboard
                        exit();
                    case "Invalid":
                        $error_message = "Credenciales de inicio de sesión no válidas.";
                        break;
                    case "Inactive":
                        $error_message = "La cuenta del usuario está inactiva.";
                        break;
                    default:
                        // Manejar cualquier otra respuesta inesperada
                        $error_message = "Respuesta inesperada de la API: " . htmlspecialchars(print_r($response, true));
                        break;
                }
            }
        } else {
            // Manejar errores de la llamada a la API (conexión, etc.)
            $error_message = "Error al conectar con el servidor de la API. Por favor, inténtelo de nuevo más tarde.";
            if (DEBUG_MODE) {
                // Si el modo de depuración está activo, se mostrarán errores detallados desde la función callAPI
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retail Manager Dashboard - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --accent: #0d6efd;
            --accent-dark: #075fe8;
            --surface: #ffffff;
            --surface-soft: #f6f9ff;
            --line: #d9e3f0;
            --text: #14213d;
            --muted: #617089;
            --success: #16a34a;
            --danger: #dc3545;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 14px 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 8% 88%, rgba(13, 110, 253, 0.12) 0, rgba(13, 110, 253, 0.12) 120px, transparent 121px),
                radial-gradient(circle at 95% 36%, rgba(22, 163, 74, 0.10) 0, rgba(22, 163, 74, 0.10) 118px, transparent 119px),
                #f4f7fb;
            color: var(--text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .login-shell {
            width: min(100%, 780px);
            background: var(--surface);
            border: 1px solid rgba(217, 227, 240, 0.9);
            border-radius: 8px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
            padding: 22px 34px 26px;
        }

        .brand {
            text-align: center;
            margin-bottom: 18px;
        }

        .brand-logo {
            width: min(220px, 62vw);
            height: auto;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .welcome-title {
            margin: 0;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .welcome-copy {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 16px;
        }

        .login-form {
            max-width: 660px;
            margin: 0 auto;
        }

        .step-row {
            position: relative;
            display: grid;
            grid-template-columns: 46px 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .step-row::before {
            content: "";
            position: absolute;
            left: 22px;
            top: 40px;
            bottom: -14px;
            width: 1px;
            background: repeating-linear-gradient(to bottom, #cfd8e6 0 6px, transparent 6px 12px);
        }

        .step-row:last-of-type::before {
            display: none;
        }

        .step-number {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            margin: 0 auto;
            border: 2px solid #c9d7ea;
            border-radius: 999px;
            background: #fff;
            color: #0b53d7;
            font-size: 18px;
        }

        .step-row:first-of-type .step-number {
            background: #eaf2ff;
            border-color: #9ac0ff;
        }

        .step-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 8px;
        }

        .step-icon {
            color: var(--accent);
            font-size: 24px;
            line-height: 1;
        }

        .step-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
        }

        .step-copy {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .field-frame {
            display: grid;
            grid-template-columns: 52px 1fr auto;
            align-items: center;
            min-height: 52px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .field-frame:focus-within {
            border-color: #8ab8ff;
            box-shadow: 0 0 0 0.22rem rgba(13, 110, 253, 0.13);
        }

        .field-icon {
            display: grid;
            place-items: center;
            height: 100%;
            color: var(--accent);
            font-size: 22px;
            background: #fbfdff;
            border-right: 1px solid var(--line);
        }

        .field-frame select,
        .field-frame input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: 0;
            color: var(--text);
            background: transparent;
            font-size: 16px;
            padding: 7px 14px;
        }

        .field-frame select {
            appearance: none;
        }

        .backend-field {
            grid-template-columns: 52px 1fr auto auto;
        }

        .backend-summary {
            pointer-events: none;
            min-width: 0;
            padding: 6px 14px;
        }

        .backend-name {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }

        .backend-url {
            margin: 1px 0 0;
            color: var(--muted);
            font-size: 14px;
            word-break: break-word;
        }

        .backend-select {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .backend-select-wrap {
            position: relative;
            min-height: 52px;
        }

        .active-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-right: 12px;
            padding: 5px 12px;
            border-radius: 8px;
            background: #dcf8e8;
            color: #0f8a45;
            font-weight: 700;
            font-size: 14px;
            white-space: nowrap;
        }

        .chevron {
            color: #5c6b82;
            font-size: 18px;
            padding-right: 16px;
        }

        .info-box {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            padding: 10px 14px;
            border: 1px solid #dce9ff;
            border-radius: 8px;
            background: #f1f7ff;
            color: #164aa5;
            line-height: 1.35;
            font-size: 14px;
        }

        .password-toggle {
            border: 0;
            background: transparent;
            color: #5f6f86;
            font-size: 20px;
            padding: 0 14px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0 14px 60px;
            color: var(--text);
            font-size: 14px;
        }

        .remember-row input {
            width: 18px;
            height: 18px;
        }

        .login-button {
            width: calc(100% - 60px);
            min-height: 50px;
            margin-left: 60px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(180deg, var(--accent), var(--accent-dark));
            color: #fff;
            font-size: 19px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 12px 22px rgba(13, 110, 253, 0.22);
        }

        .divider {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 16px;
            width: calc(100% - 60px);
            margin: 12px 0 12px 60px;
            color: var(--muted);
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            background: var(--line);
        }

        .server-link {
            width: calc(100% - 60px);
            min-height: 46px;
            margin-left: 60px;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--accent);
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .server-link:hover {
            background: #f5f9ff;
            color: var(--accent-dark);
        }

        .login-alert {
            max-width: 660px;
            margin: 0 auto 12px;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
        }

        .support-box {
            display: flex;
            gap: 10px;
            width: calc(100% - 60px);
            margin: 16px 0 0 60px;
            padding: 11px 14px;
            border-radius: 8px;
            background: #f1f7ff;
            color: #164aa5;
            font-size: 14px;
        }

        .support-box p {
            margin: 2px 0 0;
            color: var(--muted);
        }

        @media (max-height: 760px) and (min-width: 721px) {
            body {
                align-items: flex-start;
            }

            .login-shell {
                padding-top: 16px;
                padding-bottom: 18px;
            }

            .brand-logo {
                width: 170px;
            }

            .welcome-title {
                font-size: 22px;
            }

            .welcome-copy,
            .support-box,
            .info-box {
                display: none;
            }
        }

        @media (max-width: 720px) {
            body {
                align-items: flex-start;
                padding: 10px;
            }

            .login-shell {
                padding: 18px 14px 22px;
            }

            .brand {
                margin-bottom: 16px;
            }

            .brand-logo {
                width: min(190px, 58vw);
            }

            .welcome-title {
                font-size: 23px;
            }

            .welcome-copy {
                font-size: 15px;
            }

            .step-row {
                grid-template-columns: 1fr;
                gap: 8px;
                margin-bottom: 16px;
            }

            .step-row::before,
            .step-number {
                display: none;
            }

            .login-button,
            .divider,
            .server-link,
            .support-box {
                width: 100%;
                margin-left: 0;
            }

            .remember-row {
                margin-left: 0;
                flex-wrap: wrap;
            }

            .backend-field {
                grid-template-columns: 54px 1fr auto;
            }

            .active-badge {
                display: none;
            }

            .field-frame {
                grid-template-columns: 54px 1fr auto;
            }

            .support-box {
                margin-top: 18px;
            }
        }

        @media (max-width: 420px) {
            .step-heading {
                gap: 10px;
            }

            .step-icon {
                font-size: 22px;
            }

            .step-title {
                font-size: 16px;
            }

            .field-frame {
                min-height: 50px;
            }

            .field-frame select,
            .field-frame input {
                font-size: 15px;
                padding-left: 12px;
                padding-right: 12px;
            }

            .info-box,
            .support-box {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <div class="brand">
            <img src="../images/retail_manager_logo.jpg" alt="Retail Manager Dashboard" class="brand-logo">
            <h1 class="welcome-title">Bienvenido</h1>
            <p class="welcome-copy">Inicia sesión para continuar</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger login-alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="login-form">
            <div class="step-row">
                <div class="step-number"><i class="bi bi-1-circle"></i></div>
                <div>
                    <div class="step-heading">
                        <i class="bi bi-shop step-icon"></i>
                        <div>
                            <h2 class="step-title">Selecciona la tienda (backend)</h2>
                            <p class="step-copy">Elige la tienda a la que deseas conectarte.</p>
                        </div>
                    </div>

                    <div class="field-frame backend-field">
                        <span class="field-icon"><i class="bi bi-shop"></i></span>
                        <div class="backend-select-wrap">
                            <div class="backend-summary">
                                <p class="backend-name" id="backend-display-name">
                                    <?php echo htmlspecialchars($selectedBackend['name'] ?? 'Backend activo'); ?>
                                </p>
                                <p class="backend-url" id="backend-display-url">
                                    <?php echo htmlspecialchars(($selectedBackend['backend_ip'] ?? '') . ':' . ($selectedBackend['backend_port'] ?? '8180')); ?>
                                </p>
                            </div>
                            <select id="backend-select" class="backend-select" aria-label="Selecciona la tienda o backend">
                                <?php if (!empty($configuredBackends)): ?>
                                    <?php foreach ($configuredBackends as $index => $backend): ?>
                                        <?php
                                            $backendName = htmlspecialchars($backend['name'] ?? ($backend['backend_ip'] ?? 'Backend'));
                                            $backendIp = htmlspecialchars($backend['backend_ip'] ?? '');
                                            $backendPort = htmlspecialchars($backend['backend_port'] ?? '8180');
                                            $selected = $index === $selectedBackendIndex ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $index; ?>" data-name="<?php echo $backendName; ?>" data-url="<?php echo $backendIp . ':' . $backendPort; ?>" <?php echo $selected; ?>>
                                            <?php echo $backendName . ' - ' . $backendIp . ':' . $backendPort; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No hay backends configurados</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <span class="active-badge"><i class="bi bi-check-circle-fill"></i> Activo</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </div>

                    <div class="info-box">
                        <i class="bi bi-info-circle"></i>
                        <span>La tienda seleccionada determina los datos e información que verás en el sistema.</span>
                    </div>
                </div>
            </div>

            <div class="step-row">
                <div class="step-number"><i class="bi bi-2-circle"></i></div>
                <div>
                    <div class="step-heading">
                        <i class="bi bi-person step-icon"></i>
                        <div>
                            <h2 class="step-title">Selecciona tu usuario</h2>
                        </div>
                    </div>

                    <div class="field-frame">
                        <span class="field-icon"><i class="bi bi-person"></i></span>
                        <select name="UserID" id="userID" required <?php echo $apiErrorDuringEmployeeFetch ? 'disabled' : ''; ?>>
                            <option value="">-- Seleccione un usuario --</option>
                        <?php
                        if (!empty($responseUsers) && is_array($responseUsers)) {
                            foreach ($responseUsers as $employee) {
                                
                                // Ajusta 'ID' y 'Name' si las claves de tu API son diferentes (ej. 'UserID', 'EmployeeName')
                                $employeeID = isset($employee['ID']) ? htmlspecialchars($employee['ID']) : '';
                                $employeeName = isset($employee['Name']) ? htmlspecialchars($employee['Name']) : '';
                                $employeeAccess = isset($employee['Acces']) ? htmlspecialchars($employee['Acces']) : '';
                                
                                if (!empty($employeeID) && !empty($employeeName) && !empty($employeeAccess) && $employeeAccess==1) {
                                    $selected = ($userID === $employeeID) ? 'selected' : '';
                                    echo "<option value=\"{$employeeID}\" {$selected}>{$employeeName} (ID: {$employeeID})</option>";
                                }
                            }
                        } else {
                            echo "<option value=\"\" disabled>No hay usuarios disponibles</option>";
                        }
                        ?>
                        </select>
                        <i class="bi bi-chevron-down chevron"></i>
                    </div>
                </div>
            </div>

            <div class="step-row">
                <div class="step-number"><i class="bi bi-3-circle"></i></div>
                <div>
                    <div class="step-heading">
                        <i class="bi bi-lock step-icon"></i>
                        <div>
                            <h2 class="step-title">Contraseña</h2>
                        </div>
                    </div>

                    <div class="field-frame">
                        <span class="field-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" name="UserPass" id="user-pass" placeholder="Ingresa tu contraseña" required>
                        <button type="button" class="password-toggle" id="toggle-password" aria-label="Mostrar contraseña">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <label class="remember-row">
                <input type="checkbox" id="remember-backend" checked>
                <span><i class="bi bi-check-square"></i> Recordar mi selección de tienda</span>
                <i class="bi bi-info-circle text-muted"></i>
            </label>

            <button type="submit" class="login-button">
                <i class="bi bi-box-arrow-in-right"></i>
                Iniciar sesión
            </button>

            <div class="divider">ó</div>

            <a href="../setup/setup-backend.php?edit=1" class="server-link">
                <i class="bi bi-hdd-network"></i>
                Cambiar IP y puerto del servidor
            </a>

            <div class="support-box">
                <i class="bi bi-info-circle"></i>
                <div>
                    <strong>¿No encuentras la tienda que necesitas?</strong>
                    <p>Contacta al administrador del sistema para solicitar acceso.</p>
                </div>
            </div>
        </form>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script>
        const backendSelect = document.getElementById('backend-select');
        const backendName = document.getElementById('backend-display-name');
        const backendUrl = document.getElementById('backend-display-url');
        const rememberBackend = document.getElementById('remember-backend');
        const passwordInput = document.getElementById('user-pass');
        const togglePassword = document.getElementById('toggle-password');
        const currentBackendIndex = "<?php echo (int) $selectedBackendIndex; ?>";

        if (backendSelect) {
            const rememberedBackend = localStorage.getItem('dashboardBackendIndex');

            if (rememberedBackend !== null && backendSelect.querySelector(`option[value="${rememberedBackend}"]`)) {
                if (rememberedBackend !== currentBackendIndex) {
                    backendSelect.value = rememberedBackend;
                    selectBackend(Number(rememberedBackend));
                }
            }

            backendSelect.addEventListener('change', function () {
                updateBackendDisplay();

                if (rememberBackend.checked) {
                    localStorage.setItem('dashboardBackendIndex', this.value);
                } else {
                    localStorage.removeItem('dashboardBackendIndex');
                }

                selectBackend(Number(this.value));
            });
        }

        if (togglePassword) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                this.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                this.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        }

        function updateBackendDisplay() {
            const option = backendSelect.options[backendSelect.selectedIndex];

            if (!option) {
                return;
            }

            backendName.textContent = option.dataset.name || 'Backend activo';
            backendUrl.textContent = option.dataset.url || '';
        }

        function selectBackend(index) {
            fetch("../setup/save_config.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    action: "select",
                    backend_index: index
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "ok") {
                    window.location.href = "login.php";
                } else {
                    alert(data.message || "No se pudo cambiar el backend activo.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Ocurrió un error de red al intentar cambiar el backend activo.");
            });
        }
    </script>
</body>
</html>
