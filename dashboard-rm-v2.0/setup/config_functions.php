<?php
const DEFAULT_BACKEND_PORT = '8180';

function get_configBackend() {
    $file = __DIR__ . '/backend_config.json';

    if (!file_exists($file)) {
        return null;
    }

    $json = file_get_contents($file);
    $config = json_decode($json, true);

    if (!is_array($config)) {
        return null;
    }

    return normalize_backend_config($config);
}

function get_selectedBackend() {
    $config = get_configBackend();

    if (!$config || empty($config['backends'])) {
        return null;
    }

    foreach ($config['backends'] as $backend) {
        if (!empty($backend['isSelected'])) {
            return $backend;
        }
    }

    return null;
}

function save_configBackend($backend_ip, $backend_port = DEFAULT_BACKEND_PORT, $name = '', $inventoryMonthsOfCover = '', $isSelected = true, $backend_index = null) {
    $file_path = __DIR__ . '/backend_config.json';
    $config = load_backend_config_for_write($file_path);
    $backends = $config['backends'];
    $target_index = null;
    $backend_port = trim((string) $backend_port) !== '' ? trim((string) $backend_port) : DEFAULT_BACKEND_PORT;
    $name = trim((string) $name) !== '' ? trim((string) $name) : trim((string) $backend_ip);

    $backend = [
        'backend_ip' => trim((string) $backend_ip),
        'backend_port' => $backend_port,
        'inventoryMonthsOfCover' => trim((string) $inventoryMonthsOfCover),
        'name' => $name,
        'isSelected' => filter_var($isSelected, FILTER_VALIDATE_BOOLEAN)
    ];

    if ($backend_index !== null && isset($backends[(int) $backend_index])) {
        $target_index = (int) $backend_index;
        $existing = $backends[(int) $backend_index];
        $backend['inventoryMonthsOfCover'] = $backend['inventoryMonthsOfCover'] !== ''
            ? $backend['inventoryMonthsOfCover']
            : ($existing['inventoryMonthsOfCover'] ?? '');
        $backends[$target_index] = $backend;
    } else {
        $existing_index = find_backend_index($backends, $backend['backend_ip'], $backend['backend_port']);

        if ($existing_index !== null) {
            $target_index = $existing_index;
            $existing = $backends[$existing_index];
            $backend['inventoryMonthsOfCover'] = $backend['inventoryMonthsOfCover'] !== ''
                ? $backend['inventoryMonthsOfCover']
                : ($existing['inventoryMonthsOfCover'] ?? '');
            $backends[$existing_index] = $backend;
        } else {
            $backends[] = $backend;
            $target_index = count($backends) - 1;
        }
    }

    if ($backend['isSelected']) {
        $backends = mark_only_one_backend_as_selected($backends, $target_index);
    } elseif (!has_selected_backend($backends) && count($backends) > 0) {
        $backends[0]['isSelected'] = true;
    }

    save_backend_config_file($file_path, ['backends' => array_values($backends)]);
}

function delete_configBackend($backend_index) {
    $file_path = __DIR__ . '/backend_config.json';
    $config = load_backend_config_for_write($file_path);
    $index = (int) $backend_index;

    if (!isset($config['backends'][$index])) {
        return false;
    }

    $was_selected = !empty($config['backends'][$index]['isSelected']);
    array_splice($config['backends'], $index, 1);

    if ($was_selected && count($config['backends']) > 0) {
        $config['backends'][0]['isSelected'] = true;
    }

    $config['backends'] = ensure_one_backend_selected(array_values($config['backends']));
    save_backend_config_file($file_path, $config);

    return true;
}

function save_inventoryMonthsOfCover($months) {
    $file_path = __DIR__ . '/backend_config.json';
    $config = load_backend_config_for_write($file_path);
    $backends = $config['backends'];
    $selected_index = get_selected_backend_index($backends);

    if ($selected_index === null) {
        return;
    }

    $backends[$selected_index]['inventoryMonthsOfCover'] = trim((string) $months);
    save_backend_config_file($file_path, ['backends' => array_values($backends)]);
}

function select_configBackend($backend_index) {
    $file_path = __DIR__ . '/backend_config.json';
    $config = load_backend_config_for_write($file_path);
    $index = (int) $backend_index;

    if (!isset($config['backends'][$index])) {
        return false;
    }

    foreach ($config['backends'] as $key => $backend) {
        $config['backends'][$key]['isSelected'] = $key === $index;
    }

    save_backend_config_file($file_path, $config);
    return true;
}

function normalize_backend_config($config) {
    if (isset($config['backends']) && is_array($config['backends'])) {
        $backends = array_map('normalize_backend_item', $config['backends']);
        $backends = ensure_one_backend_selected(array_values($backends));

        return ['backends' => $backends];
    }

    $legacy_backend = normalize_backend_item([
        'backend_ip' => $config['backend_ip'] ?? '',
        'backend_port' => $config['backend_port'] ?? DEFAULT_BACKEND_PORT,
        'inventoryMonthsOfCover' => $config['inventoryMonthsOfCover'] ?? '',
        'name' => $config['name'] ?? ($config['backend_ip'] ?? 'Backend principal'),
        'isSelected' => true
    ]);

    return ['backends' => [$legacy_backend]];
}

function normalize_backend_item($backend) {
    return [
        'backend_ip' => trim((string) ($backend['backend_ip'] ?? '')),
        'backend_port' => trim((string) ($backend['backend_port'] ?? DEFAULT_BACKEND_PORT)) ?: DEFAULT_BACKEND_PORT,
        'inventoryMonthsOfCover' => trim((string) ($backend['inventoryMonthsOfCover'] ?? '')),
        'name' => trim((string) ($backend['name'] ?? ($backend['backend_ip'] ?? 'Backend'))),
        'isSelected' => filter_var($backend['isSelected'] ?? false, FILTER_VALIDATE_BOOLEAN)
    ];
}

function load_backend_config_for_write($file_path) {
    if (!file_exists($file_path)) {
        return ['backends' => []];
    }

    $current_content = file_get_contents($file_path);
    $existing_config = json_decode($current_content, true);

    if (!is_array($existing_config)) {
        return ['backends' => []];
    }

    return normalize_backend_config($existing_config);
}

function save_backend_config_file($file_path, $config) {
    file_put_contents($file_path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function find_backend_index($backends, $backend_ip, $backend_port) {
    foreach ($backends as $index => $backend) {
        if ($backend['backend_ip'] === $backend_ip && $backend['backend_port'] === $backend_port) {
            return $index;
        }
    }

    return null;
}

function get_selected_backend_index($backends) {
    foreach ($backends as $index => $backend) {
        if (!empty($backend['isSelected'])) {
            return $index;
        }
    }

    return null;
}

function has_selected_backend($backends) {
    return get_selected_backend_index($backends) !== null;
}

function ensure_one_backend_selected($backends) {
    $selected_found = false;

    foreach ($backends as $index => $backend) {
        if (!empty($backend['isSelected']) && !$selected_found) {
            $backends[$index]['isSelected'] = true;
            $selected_found = true;
        } else {
            $backends[$index]['isSelected'] = false;
        }
    }

    if (!$selected_found && count($backends) > 0) {
        $backends[0]['isSelected'] = true;
    }

    return $backends;
}

function mark_only_one_backend_as_selected($backends, $selected_index) {
    foreach ($backends as $index => $backend) {
        $backends[$index]['isSelected'] = $index === $selected_index;
    }

    return $backends;
}

?>
