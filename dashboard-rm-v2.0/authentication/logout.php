<?php
/**
 * Script para cerrar la sesión del usuario.
 */

// Iniciar la sesión para poder acceder a las variables de sesión
// Esta línea es CRUCIAL y siempre debe ser la primera línea de código
session_start();
require_once '../config.php';
// Destruir todas las variables de sesión.
// Esto es una buena práctica para limpiar la sesión antes de destruirla.
session_unset();

// Destruir la sesión. Esto también elimina el cookie de sesión.
// Esta es la acción que realmente "cierra" la sesión en el servidor.
session_destroy();

// Redirigir al usuario a la página de inicio de sesión
header("Location: login.php");

// Asegurarse de que el script se detenga después de la redirección
// Esto previene que se ejecute cualquier código adicional.
exit;
?>