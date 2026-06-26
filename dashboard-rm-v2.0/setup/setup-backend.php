<?php
require_once 'config_functions.php';
$configBackend = get_configBackend();
$selectedBackend = get_selectedBackend();
$editMode = isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retail Manager Dashboard - Configuración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --accent: #0d6efd;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --line: #dbe3ef;
            --text: #14213d;
            --muted: #64748b;
            --success: #12a150;
            --danger: #dc3545;
        }

        body {
            background: #f5f7fb;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
            padding: 16px 14px;
            color: var(--text);
        }

        .login-container {
            width: 100%;
            max-width: 1080px;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }

        .page-icon {
            display: grid;
            place-items: center;
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: #eaf2ff;
            color: var(--accent);
            font-size: 28px;
            font-weight: 700;
        }

        .page-title {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .page-subtitle,
        .section-subtitle {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        .card-config {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            padding: 22px;
            margin-bottom: 18px;
        }

        .form-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .form-icon {
            color: var(--accent);
            font-size: 26px;
            line-height: 1;
        }

        .form-title {
            margin: 0;
            color: var(--accent);
            font-size: 23px;
            font-weight: 700;
        }

        .form-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 24px;
            align-items: start;
        }

        .form-label {
            color: var(--text);
            font-weight: 500;
            margin-bottom: 5px;
        }

        .input-group-text {
            min-width: 52px;
            justify-content: center;
            background: var(--surface-soft);
            color: var(--accent);
            font-size: 19px;
            border-color: var(--line);
        }

        .form-control {
            min-height: 46px;
            border-color: var(--line);
            font-size: 16px;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
        }

        .help-panel {
            background: #f4f8ff;
            border: 1px solid #d9e7ff;
            border-radius: 8px;
            padding: 18px;
        }

        .help-title {
            color: var(--accent);
            font-weight: 700;
            margin-bottom: 14px;
        }

        .help-item {
            display: grid;
            grid-template-columns: 30px 1fr;
            gap: 10px;
            margin-bottom: 12px;
        }

        .help-item:last-child {
            margin-bottom: 0;
        }

        .help-icon {
            color: var(--accent);
            font-size: 22px;
            line-height: 1;
        }

        .help-label {
            color: var(--accent);
            display: block;
            font-weight: 700;
        }

        .help-copy {
            color: var(--muted);
            margin: 1px 0 0;
            line-height: 1.32;
            font-size: 14px;
        }

        .form-check {
            margin-top: 8px;
        }

        .form-check-input {
            width: 19px;
            height: 19px;
            margin-right: 8px;
        }

        .form-check-label {
            color: var(--text);
            font-size: 15px;
        }

        .form-grid .form-group.mb-4 {
            margin-bottom: 12px !important;
        }

        .button-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 1px solid var(--line);
        }

        .button-row .btn {
            min-height: 46px;
            font-size: 15px;
            font-weight: 700;
        }

        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin: 0 12px 12px;
        }

        .section-title {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
        }

        .refresh-button {
            border: 0;
            background: transparent;
            color: var(--accent);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
        }

        .backend-list {
            display: grid;
            gap: 12px;
        }

        .backend-card {
            position: relative;
            overflow: visible;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
            padding: 18px 24px 17px;
        }

        .backend-card.is-active {
            border-left: 6px solid var(--success);
        }

        .backend-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e7edf5;
        }

        .backend-name {
            margin: 0 0 5px;
            font-size: 22px;
            font-weight: 800;
        }

        .backend-url {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            border-radius: 8px;
            background: #eaf2ff;
            color: var(--accent);
            font-size: 15px;
            font-weight: 600;
            padding: 3px 10px;
            word-break: break-all;
        }

        .active-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
            background: #dcf8e8;
            color: #0f8a45;
            font-weight: 700;
            padding: 6px 14px;
            white-space: nowrap;
            font-size: 14px;
        }

        .active-dot {
            color: var(--success);
        }

        .backend-details {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            gap: 18px;
            align-items: end;
            padding-top: 14px;
        }

        .detail-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 4px;
        }

        .detail-icon {
            color: var(--accent);
            font-size: 20px;
        }

        .detail-value {
            color: #26344d;
            font-size: 15px;
            word-break: break-word;
        }

        .card-menu {
            justify-self: end;
        }

        .menu-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: transparent;
            color: #475569;
            font-size: 24px;
            line-height: 1;
            padding: 6px;
            border-radius: 8px;
        }

        .menu-button:hover,
        .menu-button:focus {
            background: #eef4ff;
            color: var(--accent);
        }

        .dropdown-menu {
            border-color: var(--line);
            box-shadow: 0 16px 34px rgba(15, 23, 42, 0.15);
            border-radius: 8px;
            padding: 6px;
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 8px 10px;
            font-weight: 600;
        }

        .dropdown-item.text-danger {
            color: var(--danger) !important;
        }

        .empty-state,
        .active-note {
            border-radius: 8px;
            background: #eef6ff;
            color: #164aa5;
            padding: 12px 18px;
        }

        .active-note {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 14px;
            font-weight: 600;
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .form-grid,
            .backend-details,
            .button-row {
                grid-template-columns: 1fr;
            }

            .help-panel {
                display: none;
            }

            .page-title {
                font-size: 32px;
            }
        }

        @media (max-width: 620px) {
            body {
                padding: 12px 10px;
            }

            .page-header {
                align-items: flex-start;
                margin-bottom: 12px;
            }

            .page-icon {
                width: 46px;
                height: 46px;
                font-size: 23px;
            }

            .page-title {
                font-size: 24px;
            }

            .card-config,
            .backend-card {
                padding: 16px;
            }

            .backend-card-header {
                flex-direction: column;
            }

            .section-header {
                margin-left: 0;
                margin-right: 0;
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>


<div class="login-container">
    <header class="page-header">
        <div class="page-icon"><i class="bi bi-server"></i></div>
        <div>
            <h1 class="page-title">Configuración de Backends</h1>
            <p class="page-subtitle">Administra y configura los backends que utiliza tu sistema.</p>
        </div>
    </header>

    <section class="card-config">
        <input type="hidden" id="backend-index" value="">
        <div class="form-heading">
            <div class="form-icon"><i class="bi bi-file-earmark-pen"></i></div>
            <div>
                <h2 class="form-title">Nuevo / Editar Backend</h2>
                <p class="section-subtitle">Completa los datos para agregar o actualizar un backend.</p>
            </div>
        </div>

        <div class="form-grid">
            <div>
                <div class="form-group mb-4">
                    <label for="backend-name" class="form-label d-block">Nombre del backend</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                        <input type="text" id="backend-name" class="form-control" placeholder="Tienda Nutribelleza" required>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label for="backend-ip" class="form-label d-block">IP del backend</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-globe2"></i></span>
                        <input type="text" id="backend-ip" class="form-control" placeholder="192.168.0.10" value="<?= htmlspecialchars($selectedBackend['backend_ip'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label for="backend-port" class="form-label d-block">Puerto del backend</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-plug"></i></span>
                        <input type="number" id="backend-port" class="form-control" placeholder="8180" value="8180" min="1" required>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label for="inventory-months" class="form-label d-block">Meses de cobertura de inventario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="number" id="inventory-months" class="form-control" placeholder="1.35" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="backend-selected" checked>
                    <label class="form-check-label" for="backend-selected">
                        Usar este backend actualmente
                    </label>
                </div>
            </div>

            <aside class="help-panel">
                <div class="help-title"><i class="bi bi-info-circle"></i> ¿Qué significa cada campo?</div>
                <div class="help-item">
                    <span class="help-icon"><i class="bi bi-tag"></i></span>
                    <div>
                        <span class="help-label">Nombre del backend</span>
                        <p class="help-copy">Identifica tu backend de forma fácil y descriptiva.</p>
                    </div>
                </div>
                <div class="help-item">
                    <span class="help-icon"><i class="bi bi-globe2"></i></span>
                    <div>
                        <span class="help-label">IP del backend</span>
                        <p class="help-copy">Dirección IP donde se encuentra tu servicio.</p>
                    </div>
                </div>
                <div class="help-item">
                    <span class="help-icon"><i class="bi bi-plug"></i></span>
                    <div>
                        <span class="help-label">Puerto del backend</span>
                        <p class="help-copy">Puerto utilizado para la comunicación.</p>
                    </div>
                </div>
                <div class="help-item">
                    <span class="help-icon"><i class="bi bi-calendar3"></i></span>
                    <div>
                        <span class="help-label">Meses de cobertura</span>
                        <p class="help-copy">Meses que tu inventario podrá proyectarse con este backend.</p>
                    </div>
                </div>
            </aside>
        </div>

        <div class="button-row">
            <button id="save-config" class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button>
            <button id="new-config" class="btn btn-outline-secondary"><i class="bi bi-plus-lg"></i> Nuevo backend</button>
        </div>
    </section>

    <section>
        <div class="section-header">
            <div>
                <h2 class="section-title">Backends configurados</h2>
                <p class="section-subtitle">Lista de backends disponibles en tu sistema.</p>
            </div>
            <button id="refresh-config" class="refresh-button" type="button"><i class="bi bi-arrow-clockwise"></i> Actualizar</button>
        </div>
        <div id="backend-list" class="backend-list"></div>
        <div class="active-note"><i class="bi bi-shield-check"></i> El backend activo es el que se utilizará para todas las operaciones del sistema.</div>
    </section>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function isEditMode() {
    return <?= $editMode ? 'true' : 'false' ?>;
}

document.getElementById("save-config").addEventListener("click", function () {
    const backend_index = document.getElementById("backend-index").value;
    const name = document.getElementById("backend-name").value.trim();
    const backend_ip = document.getElementById("backend-ip").value.trim();
    const backend_port = document.getElementById("backend-port").value.trim();
    const inventoryMonthsOfCover = document.getElementById("inventory-months").value.trim();
    const isSelected = document.getElementById("backend-selected").checked;

    if (name && backend_ip && backend_port && inventoryMonthsOfCover) {
        const config = {
            backend_ip,
            backend_port,
            inventoryMonthsOfCover,
            name,
            isSelected
        };

        if (backend_index !== "") {
            config.backend_index = Number(backend_index);
        }

        fetch("save_config.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(config)
        })
        .then(res => res.json())
        .then(async data => {
            if (data.status === "ok") {
                await showSuccess("Configuración guardada correctamente.");
                window.location.href = data.sessionClosed
                    ? "../authentication/login.php"
                    : "../index.php";
            } else {
                showError(data.message || "Error al guardar la configuración en el servidor.");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError("Ocurrió un error de red al intentar guardar la configuración.");
        });
    } else {
        showWarning("Por favor completa todos los campos.");
    }
});

document.getElementById("new-config").addEventListener("click", function () {
    clearForm();
});

document.getElementById("refresh-config").addEventListener("click", function () {
    loadBackends();
});

window.addEventListener('DOMContentLoaded', () => {
    loadBackends();
});

function loadBackends() {
    fetch("get_config.php")
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok" && data.config) {
                renderBackendList(data.backends || []);
                fillForm(data.config, getSelectedIndex(data.backends || []));

                if (!isEditMode()) {
                    window.location.href = "../authentication/login.php";
                }
            } else {
                clearForm();
            }
        })
        .catch(err => {
            console.error("Error al leer configuración del servidor:", err);
        });
}

function fillForm(backend, index) {
    document.getElementById('backend-index').value = index !== null ? index : '';
    document.getElementById('backend-name').value = backend.name || '';
    document.getElementById('backend-ip').value = backend.backend_ip || '';
    document.getElementById('backend-port').value = backend.backend_port || '8180';
    document.getElementById('inventory-months').value = backend.inventoryMonthsOfCover || '';
    document.getElementById('backend-selected').checked = Boolean(backend.isSelected);
    document.getElementById('backend-name').focus();
}

function clearForm() {
    document.getElementById('backend-index').value = '';
    document.getElementById('backend-name').value = '';
    document.getElementById('backend-ip').value = '';
    document.getElementById('backend-port').value = '8180';
    document.getElementById('inventory-months').value = '';
    document.getElementById('backend-selected').checked = true;
    document.getElementById('backend-name').focus();
}

function getSelectedIndex(backends) {
    const selectedIndex = backends.findIndex(backend => backend.isSelected);
    return selectedIndex >= 0 ? selectedIndex : null;
}

function renderBackendList(backends) {
    const list = document.getElementById('backend-list');
    list.innerHTML = '';

    if (backends.length === 0) {
        list.innerHTML = '<div class="empty-state">No hay backends configurados todavía.</div>';
        return;
    }

    backends.forEach((backend, index) => {
        const item = document.createElement('article');
        item.className = `backend-card ${backend.isSelected ? 'is-active' : ''}`;
        item.innerHTML = `
            <div class="backend-card-header">
                <div>
                    <h3 class="backend-name">${escapeHtml(backend.name || backend.backend_ip)}</h3>
                    <span class="backend-url">${escapeHtml(backend.backend_ip)}:${escapeHtml(backend.backend_port || '8180')}</span>
                </div>
                ${backend.isSelected ? '<span class="active-badge"><i class="bi bi-check-circle-fill active-dot"></i>Activo</span>' : ''}
            </div>
            <div class="backend-details">
                <div>
                    <div class="detail-label"><span class="detail-icon"><i class="bi bi-globe2"></i></span> IP</div>
                    <div class="detail-value">${escapeHtml(backend.backend_ip)}</div>
                </div>
                <div>
                    <div class="detail-label"><span class="detail-icon"><i class="bi bi-plug"></i></span> Puerto</div>
                    <div class="detail-value">${escapeHtml(backend.backend_port || '8180')}</div>
                </div>
                <div>
                    <div class="detail-label"><span class="detail-icon"><i class="bi bi-calendar3"></i></span> Cobertura de inventario</div>
                    <div class="detail-value">${escapeHtml(backend.inventoryMonthsOfCover || '')} meses</div>
                </div>
                <div>
                    <div class="detail-label"><span class="detail-icon"><i class="bi bi-shield-check"></i></span> Estado</div>
                    <div class="detail-value">Disponible</div>
                </div>
                <div class="dropdown card-menu">
                    <button class="menu-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones de ${escapeHtml(backend.name || backend.backend_ip)}"><i class="bi bi-three-dots-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><button class="dropdown-item" type="button" data-action="edit" data-index="${index}"><i class="bi bi-file-earmark-pen"></i> Editar</button></li>
                        <li><button class="dropdown-item" type="button" data-action="select" data-index="${index}" ${backend.isSelected ? 'disabled' : ''}><i class="bi bi-check-circle"></i> Establecer como activo</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button class="dropdown-item text-danger" type="button" data-action="delete" data-index="${index}"><i class="bi bi-trash"></i> Eliminar</button></li>
                    </ul>
                </div>
            </div>
        `;
        list.appendChild(item);
    });

    list.querySelectorAll('[data-action]').forEach(button => {
        button.addEventListener('click', function () {
            const action = this.dataset.action;
            const index = Number(this.dataset.index);
            const backend = backends[index];

            if (action === 'edit') {
                fillForm(backend, index);
                return;
            }

            if (action === 'select') {
                selectBackend(index);
                return;
            }

            if (action === 'delete') {
                deleteBackend(index, backend.name || backend.backend_ip);
            }
        });
    });
}

function selectBackend(index) {
    fetch("save_config.php", {
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
            window.location.href = data.sessionClosed
                ? "../authentication/login.php"
                : "../index.php";
        } else {
            showError(data.message || "Error al establecer el backend como activo.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError("Ocurrió un error de red al intentar establecer el backend como activo.");
    });
}

async function deleteBackend(index, name) {
    const confirmed = await confirmDelete(name);

    if (!confirmed) {
        return;
    }

    fetch("save_config.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            action: "delete",
            backend_index: index
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "ok") {
            clearForm();
            loadBackends();
            showSuccess("Backend eliminado correctamente.");
        } else {
            showError(data.message || "Error al eliminar el backend.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError("Ocurrió un error de red al intentar eliminar el backend.");
    });
}

function showSuccess(message) {
    return Swal.fire({
        icon: "success",
        title: "Listo",
        text: message,
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#0d6efd"
    });
}

function showError(message) {
    return Swal.fire({
        icon: "error",
        title: "No se pudo completar la acción",
        text: message,
        confirmButtonText: "Entendido",
        confirmButtonColor: "#0d6efd"
    });
}

function showWarning(message) {
    return Swal.fire({
        icon: "warning",
        title: "Campos incompletos",
        text: message,
        confirmButtonText: "Revisar",
        confirmButtonColor: "#0d6efd"
    });
}

async function confirmDelete(name) {
    const result = await Swal.fire({
        icon: "warning",
        title: "Eliminar backend",
        text: `¿Quieres eliminar el backend "${name}"?`,
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#64748b",
        reverseButtons: true
    });

    return result.isConfirmed;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>


</body>
</html>
