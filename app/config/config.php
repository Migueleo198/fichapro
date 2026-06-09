<?php

// ── App ──────────────────────────────────────────────────────────────
define('APP_NAME', 'FichaPro');
define('RUTA_APP', dirname(__DIR__));

// URL base: '/fichapro/public' for local XAMPP subfolder, '' in production (docroot = public)
define('URL_BASE', '/fichapro/public');
define('RUTA_URL', 'http://localhost' . URL_BASE);

// ── Database ─────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'fichapro');
define('DB_USER', 'root');
define('DB_PASS', '');

// ── Business defaults (can be overridden from the ajustes table) ─────
define('DEF_HORAS_DIA',     7.5);
define('DEF_HORAS_SEMANA',  37.5);
define('DEF_UMBRAL_RETRASO', 30);      // minutes of grace before a clock-in counts as late
define('DEF_HORA_INICIO',   '08:00');

// ── SMTP (for password reset emails) — fill in to enable ─────────────
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM_NAME', 'FichaPro');
