<?php
/**
 * read_cache.php — Serves cached section data to the browser
 * Returns: {success:true, data:{...}, generated_at:"...", needs_refresh:bool}
 *       or {success:false, reason:"no_cache"|"stale"}
 */
header('Content-Type: application/json');
header('Cache-Control: no-store');

$section = isset($_GET['section']) ? preg_replace('/[^a-z_]/', '', $_GET['section']) : '';
$td      = date('Y-m-d');
$file    = __DIR__ . "/../data/cache/{$section}_{$td}.json";

if (!$section || !file_exists($file)) {
    echo json_encode(['success' => false, 'reason' => 'no_cache']);
    exit;
}

$raw = file_get_contents($file);
if ($raw === false) {
    echo json_encode(['success' => false, 'reason' => 'read_error']);
    exit;
}

$data = json_decode($raw, true);
if (!$data || !isset($data['date']) || $data['date'] !== $td) {
    echo json_encode(['success' => false, 'reason' => 'stale']);
    exit;
}

$ageMinutes           = (time() - strtotime($data['generated_at'])) / 60;
$data['needs_refresh'] = $ageMinutes > 55;
$data['success']       = true;

echo json_encode($data);
