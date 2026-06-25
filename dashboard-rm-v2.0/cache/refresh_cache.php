<?php
/**
 * refresh_cache.php — Pre-fetches today's data for Home & Sales sections
 * Called by: browser auto-refresh (setInterval), Actualize button, or manually
 * Usage: cache/refresh_cache.php?section=home|sales_today|all (default: all)
 */
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$td  = date('Y-m-d');
$lw  = date('Y-m-d', strtotime('-7 days'));
$m12 = date('Y-m-d', strtotime('first day of -11 months'));

function fetchEndpoint($endpoint, $params = []) {
    $result = callAPI($endpoint, $params);
    return ($result !== false && $result !== null) ? $result : [];
}

function writeCache($section, $date, $data) {
    $dir = __DIR__ . '/../data/cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $file = "{$dir}/{$section}_{$date}.json";
    file_put_contents($file, json_encode([
        'section'      => $section,
        'date'         => $date,
        'generated_at' => date('Y-m-d H:i:s'),
        'data'         => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

$section = isset($_GET['section']) ? $_GET['section'] : 'all';
$errors  = [];

if ($section === 'home' || $section === 'all') {
    try {
        writeCache('home', $td, [
            'SalesTotals_today'     => fetchEndpoint('SalesTotals',      ['DateFrom' => $td,  'DateTo' => $td]),
            'SalesTotals_lw'        => fetchEndpoint('SalesTotals',      ['DateFrom' => $lw,  'DateTo' => $lw]),
            'SaleTrendByMonth'      => fetchEndpoint('SaleTrendByMonth', ['DateFrom' => $m12, 'DateTo' => $td]),
            'SalesByMethod_today'   => fetchEndpoint('SalesByMethod',    ['DateFrom' => $td,  'DateTo' => $td]),
            'TopSellProducts_today' => fetchEndpoint('TopSellProducts',  ['DateFrom' => $td,  'DateTo' => $td]),
            'LowLevelItems'         => fetchEndpoint('LowLevelItems',    ['Active'   => 1]),
            'SalesByHour_today'     => fetchEndpoint('SalesByHour',      ['DateFrom' => $td,  'DateTo' => $td]),
        ]);
    } catch (Exception $e) {
        $errors[] = 'home: ' . $e->getMessage();
    }
}

if ($section === 'sales_today' || $section === 'all') {
    try {
        writeCache('sales_today', $td, [
            'SalesTotals'        => fetchEndpoint('SalesTotals',       ['DateFrom' => $td,  'DateTo' => $td]),
            'SaleTrendByMonth'   => fetchEndpoint('SaleTrendByMonth',  ['DateFrom' => $m12, 'DateTo' => $td]),
            'SalesByDepartment'  => fetchEndpoint('SalesByDepartment', ['DateFrom' => $td,  'DateTo' => $td]),
            'SalesByCategory'    => fetchEndpoint('SalesByCategory',   ['DateFrom' => $td,  'DateTo' => $td]),
            'SalesByMethod'      => fetchEndpoint('SalesByMethod',     ['DateFrom' => $td,  'DateTo' => $td]),
            'TopSellProducts'    => fetchEndpoint('TopSellProducts',   ['DateFrom' => $td,  'DateTo' => $td]),
        ]);
    } catch (Exception $e) {
        $errors[] = 'sales_today: ' . $e->getMessage();
    }
}

echo json_encode([
    'success'      => empty($errors),
    'generated_at' => date('Y-m-d H:i:s'),
    'section'      => $section,
    'errors'       => $errors,
]);
