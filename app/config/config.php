<?php

// ── Carga del .env ───────────────────────────────────────────────────
// Lee el archivo .env de la raíz del proyecto (si existe) y vuelca sus
// valores al entorno. Las variables de entorno reales tienen prioridad.
(function () {
    $envPath = dirname(__DIR__, 2) . '/.env';
    if (!is_file($envPath)) return;
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // quita comillas envolventes opcionales
        if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && $v[strlen($v) - 1] === $v[0]) {
            $v = substr($v, 1, -1);
        }
        if ($k !== '' && getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
})();

/** Lee una variable de entorno con valor por defecto. */
function env(string $clave, $defecto = null) {
    $v = getenv($clave);
    return $v === false ? $defecto : $v;
}

// ── App ──────────────────────────────────────────────────────────────
define('APP_NAME', env('APP_NAME', 'FichaPro'));
define('RUTA_APP', dirname(__DIR__));

// URL base: '/fichapro/public' en XAMPP local, '' en producción (docroot = public)
define('URL_BASE', env('URL_BASE', '/fichapro/public'));
define('RUTA_URL', env('APP_URL', 'http://localhost' . URL_BASE));

// ── Base de datos ────────────────────────────────────────────────────
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'fichapro'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// ── Valores de negocio por defecto (se pueden sobreescribir en ajustes) ─
define('DEF_HORAS_DIA',      (float) env('DEF_HORAS_DIA', 7.5));
define('DEF_HORAS_SEMANA',   (float) env('DEF_HORAS_SEMANA', 37.5));
define('DEF_UMBRAL_RETRASO', (int)   env('DEF_UMBRAL_RETRASO', 30));   // minutos de cortesía antes de marcar retraso
define('DEF_HORA_INICIO',    env('DEF_HORA_INICIO', '08:00'));

// ── SMTP (envío de correos) — configurable también desde Ajustes → Sistema ─
define('SMTP_HOST',      env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT',      (int) env('SMTP_PORT', 587));
define('SMTP_SECURE',    env('SMTP_SECURE', 'tls'));      // 'tls' (587) o 'ssl' (465)
define('SMTP_USER',      env('SMTP_USER', ''));
define('SMTP_PASS',      env('SMTP_PASS', ''));
define('SMTP_FROM_NAME', env('SMTP_FROM_NAME', 'FichaPro'));
