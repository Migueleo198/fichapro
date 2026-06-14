<?php

/**
 * Copia de seguridad CIFRADA de la base de datos.
 *
 * Hace un mysqldump y guarda el resultado cifrado (AES-256) en
 * storage/backups/ — carpeta fuera de /public y bloqueada por .htaccess,
 * así que NO es accesible desde el navegador y, además, va cifrada.
 *
 * Uso:   php app/services/backup_db.php
 *
 * Programar (Windows · Programador de tareas, p.ej. a diario):
 *   C:\xampp\php\php.exe C:\xampp\htdocs\fichapro\app\services\backup_db.php
 */

require_once __DIR__ . '/../config/config.php';      // carga .env
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/crypto.php';

$pass = fp_backup_pass();

// Localiza mysqldump (env o ruta XAMPP por defecto)
$mysqldump = env('MYSQLDUMP_BIN', 'C:/xampp/mysql/bin/mysqldump.exe');
if (!is_file($mysqldump)) {
    fwrite(STDERR, "No se encontró mysqldump en: {$mysqldump}\nDefine MYSQLDUMP_BIN en el .env.\n");
    exit(1);
}

// Carpeta de destino (fuera del docroot)
$dir = env('BACKUP_DIR', RUTA_APP . '/../storage/backups');
if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
    fwrite(STDERR, "No se pudo crear la carpeta de copias: {$dir}\n");
    exit(1);
}

// Fichero temporal con las credenciales (evita pasar la contraseña por la línea de comandos)
$cnf = tempnam(sys_get_temp_dir(), 'fpcnf');
file_put_contents($cnf,
    "[client]\nhost=" . DB_HOST . "\nport=" . DB_PORT .
    "\nuser=" . DB_USER . "\npassword=\"" . DB_PASS . "\"\n"
);
$sqlTmp = tempnam(sys_get_temp_dir(), 'fpsql');

// Ejecuta el volcado a un fichero temporal
$cmd = '"' . $mysqldump . '" --defaults-extra-file="' . $cnf . '"'
     . ' --single-transaction --routines --events --add-drop-table'
     . ' --result-file="' . $sqlTmp . '" ' . escapeshellarg(DB_NAME) . ' 2>&1';
exec($cmd, $out, $code);
@unlink($cnf);   // las credenciales no deben quedar en disco

if ($code !== 0 || !is_file($sqlTmp) || filesize($sqlTmp) === 0) {
    @unlink($sqlTmp);
    fwrite(STDERR, "Error en mysqldump (código {$code}): " . implode("\n", $out) . "\n");
    exit(1);
}

$sql = file_get_contents($sqlTmp);
@unlink($sqlTmp);

// Cifra y guarda
$blob = fp_encrypt($sql, $pass);
$nombre = 'fichapro_' . date('Ymd_His') . '.sql.enc';
$ruta   = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $nombre;
if (file_put_contents($ruta, $blob) === false) {
    fwrite(STDERR, "No se pudo escribir la copia en: {$ruta}\n");
    exit(1);
}

printf("Copia cifrada creada: %s\n", $ruta);
printf("  SQL original: %s · cifrado: %s\n", fp_bytes(strlen($sql)), fp_bytes(strlen($blob)));
printf("  Restaurar con: php app/services/restore_db.php \"%s\"\n", $nombre);

function fp_bytes(int $n): string {
    return $n >= 1048576 ? round($n / 1048576, 2) . ' MB'
         : ($n >= 1024 ? round($n / 1024, 1) . ' KB' : $n . ' B');
}
