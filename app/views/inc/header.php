<?php require_once RUTA_APP . '/helpers/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Panel') ?> · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= url('css/estilos.css') ?>" rel="stylesheet">
</head>
<body>

<!-- mobile top bar -->
<div class="topbar">
    <button onclick="toggleSidebar()" style="background:rgba(255,255,255,.12);border:none;color:#fff;width:2.4rem;height:2.4rem;border-radius:9px;cursor:pointer;font-size:1.3rem;display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-list"></i>
    </button>
    <span style="font-weight:900;color:#fff;letter-spacing:.03em;">⏱ <?= APP_NAME ?></span>
</div>
<div id="backdrop" onclick="closeSidebar()"></div>

<div class="layout">
<?php require_once RUTA_APP . '/views/inc/sidebar.php'; ?>
<main class="main">
