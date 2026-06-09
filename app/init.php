<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/helpers/functions.php';

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/lib/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
