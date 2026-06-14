<?php

/**
 * Restaura / descifra una copia de seguridad de la base de datos.
 *
 * Uso:
 *   php app/services/restore_db.php <archivo.sql.enc>            (descifra a .sql)
 *   php app/services/restore_db.php <archivo.sql.enc> --import   (¡importa a la BD!)
 *
 * <archivo> puede ser solo el nombre (se busca en storage/backups) o una ruta.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/crypto.php';

$pass   = fp_backup_pass();
$arg    = $argv[1] ?? '';
$import = in_array('--import', $argv, true);

if ($arg === '') {
    fwrite(STDERR, "Indica el archivo .enc.  Ej: php app/services/restore_db.php fichapro_20260612_101500.sql.enc\n");
    exit(1);
}

// Resuelve la ruta: tal cual, o dentro de la carpeta de copias
$dir  = env('BACKUP_DIR', RUTA_APP . '/../storage/backups');
$ruta = is_file($arg) ? $arg : rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $arg;
if (!is_file($ruta)) {
    fwrite(STDERR, "No se encontró el archivo: {$ruta}\n");
    exit(1);
}

$sql = fp_decrypt(file_get_contents($ruta), $pass);
if ($sql === null) {
    fwrite(STDERR, "No se pudo descifrar: clave incorrecta o archivo manipulado.\n");
    exit(1);
}

if (!$import) {
    // Solo descifrar a un .sql junto al original
    $salida = preg_replace('/\.enc$/', '', $ruta);
    if ($salida === $ruta) $salida .= '.sql';
    file_put_contents($salida, $sql);
    printf("Descifrado en: %s  (%d bytes)\n", $salida, strlen($sql));
    echo "Para importarlo: vuelve a ejecutar con --import, o impórtalo desde phpMyAdmin.\n";
    exit(0);
}

// Importar a la BD (sobrescribe los datos actuales)
$mysql = env('MYSQL_BIN', 'C:/xampp/mysql/bin/mysql.exe');
if (!is_file($mysql)) {
    fwrite(STDERR, "No se encontró mysql en: {$mysql}\nDefine MYSQL_BIN en el .env.\n");
    exit(1);
}

$cnf = tempnam(sys_get_temp_dir(), 'fpcnf');
file_put_contents($cnf,
    "[client]\nhost=" . DB_HOST . "\nport=" . DB_PORT .
    "\nuser=" . DB_USER . "\npassword=\"" . DB_PASS . "\"\n"
);
$sqlTmp = tempnam(sys_get_temp_dir(), 'fpsql');
file_put_contents($sqlTmp, $sql);

$cmd = '"' . $mysql . '" --defaults-extra-file="' . $cnf . '" ' . escapeshellarg(DB_NAME)
     . ' < "' . $sqlTmp . '" 2>&1';
exec($cmd, $out, $code);
@unlink($cnf);
@unlink($sqlTmp);

if ($code !== 0) {
    fwrite(STDERR, "Error al importar (código {$code}): " . implode("\n", $out) . "\n");
    exit(1);
}
echo "Base de datos restaurada correctamente desde la copia cifrada.\n";
