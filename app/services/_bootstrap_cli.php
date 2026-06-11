<?php

/**
 * Bootstrap mínimo para los scripts de línea de comandos (sin sesión web).
 * Carga configuración, BD, helpers, el autoload de Composer y el núcleo de envío.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

$autoload = RUTA_APP . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Falta la carpeta vendor/. Ejecuta \"composer install\" en el proyecto.\n");
    exit(1);
}
require_once $autoload;

require_once RUTA_APP . '/services/enviar_informes.php';
