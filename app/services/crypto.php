<?php

/**
 * Cifrado simétrico para las copias de seguridad de la base de datos.
 *
 * Esquema: AES-256-CBC con IV aleatorio + autenticación HMAC-SHA256
 * (encrypt-then-MAC). De la passphrase del .env (BACKUP_KEY) se derivan
 * dos subclaves independientes (una para cifrar, otra para el MAC).
 *
 * Formato del blob:  "FPB1" + IV(16) + MAC(32) + ciphertext   (binario)
 */

/** Passphrase de copia desde el .env (aborta si falta). */
function fp_backup_pass(): string {
    $pass = (string) env('BACKUP_KEY', '');
    if ($pass === '') {
        fwrite(STDERR, "Falta BACKUP_KEY en el .env.\n");
        fwrite(STDERR, "Genera una con:  php -r \"echo bin2hex(random_bytes(32));\"\n");
        exit(1);
    }
    return $pass;
}

/** Cifra texto y devuelve el blob binario autenticado. */
function fp_encrypt(string $plain, string $pass): string {
    $ek = hash('sha256', 'fp-enc|' . $pass, true);   // clave de cifrado
    $mk = hash('sha256', 'fp-mac|' . $pass, true);   // clave de MAC
    $iv = random_bytes(16);
    $ct = openssl_encrypt($plain, 'aes-256-cbc', $ek, OPENSSL_RAW_DATA, $iv);
    if ($ct === false) {
        fwrite(STDERR, "Error al cifrar.\n");
        exit(1);
    }
    $mac = hash_hmac('sha256', $iv . $ct, $mk, true);
    return 'FPB1' . $iv . $mac . $ct;
}

/** Descifra un blob; devuelve null si la clave es incorrecta o está manipulado. */
function fp_decrypt(string $blob, string $pass): ?string {
    if (substr($blob, 0, 4) !== 'FPB1' || strlen($blob) < 4 + 16 + 32) return null;
    $ek  = hash('sha256', 'fp-enc|' . $pass, true);
    $mk  = hash('sha256', 'fp-mac|' . $pass, true);
    $iv  = substr($blob, 4, 16);
    $mac = substr($blob, 20, 32);
    $ct  = substr($blob, 52);
    if (!hash_equals($mac, hash_hmac('sha256', $iv . $ct, $mk, true))) return null; // integridad
    $pt = openssl_decrypt($ct, 'aes-256-cbc', $ek, OPENSSL_RAW_DATA, $iv);
    return $pt === false ? null : $pt;
}
