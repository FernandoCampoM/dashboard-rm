<?php
/**
 * Inclusión de scripts JavaScript
 * 
 * Este archivo define qué scripts se cargan en cada sección del dashboard
 */

// Scripts comunes para todas las páginas
$commonScripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js',
    // DataTables Extensions JS
    'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js',
    'https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/pdfmake.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/vfs_fonts.js',
    'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js',
    'https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js',
    'https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js',
    // Scripts personalizados
    'js/sidebar.js'
];

// Scripts específicos para mantenimiento de productos
$productMaintenanceScripts = [
    'js/maintenance.js'
];

// Scripts específicos para mantenimiento de clientes
$clientMaintenanceScripts = [
    'js/clients.js'
];

/**
 * Función para cargar scripts
 * 
 * @param array $scripts Lista de URLs o rutas de scripts a cargar
 * @return string Etiquetas script generadas
 */
function loadScripts($scripts) {
    $output = '';
    foreach ($scripts as $script) {
        $output .= '<script src="' . $script . '"></script>' . PHP_EOL;
    }
    return $output;
}

/**
 * Detecta qué sección está activa basada en un parámetro o URL
 * 
 * @return string Nombre de la sección activa
 */
function getActiveSection() {
    $section = $_GET['section'] ?? '';
    
    if (empty($section)) {
        // Si no hay parámetro section, intentar detectar por la URL
        $currentUrl = $_SERVER['REQUEST_URI'];
        if (strpos($currentUrl, 'products-maintenance') !== false) {
            return 'products-maintenance';
        } elseif (strpos($currentUrl, 'clients') !== false) {
            return 'clients';
        }
        
        // Por defecto, mostrar dashboard principal
        return 'overview';
    }
    
    return $section;
}

// Detectar la sección activa
$activeSection = getActiveSection();

// Determinar qué scripts adicionales cargar basados en la sección activa
$additionalScripts = [];

switch ($activeSection) {
    case 'products-maintenance':
        $additionalScripts = $productMaintenanceScripts;
        break;
    case 'clients':
        $additionalScripts = $clientMaintenanceScripts;
        break;
    default:
        // Para las secciones de panel principal, ventas, productos e inventario
        // no se cargan scripts adicionales específicos
        break;
}

// Combinar y cargar todos los scripts necesarios
$allScripts = array_merge($commonScripts, $additionalScripts);
echo loadScripts($allScripts);