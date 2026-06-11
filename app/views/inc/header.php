<?php require_once RUTA_APP . '/helpers/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Panel') ?> · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= url('css/estilos.css') ?>?v=<?= @filemtime(RUTA_APP . '/../public/css/estilos.css') ?: '1' ?>" rel="stylesheet">
</head>
<body>
<script>const BASE = "<?= URL_BASE ?>";</script>

<div class="layout">
<?php require_once RUTA_APP . '/views/inc/sidebar.php'; ?>
<main class="main">
