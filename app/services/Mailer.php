<?php

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Fábrica de PHPMailer configurado a partir de los ajustes SMTP
 * (tabla `ajustes`, con respaldo en las constantes SMTP_* de config.php).
 *
 * Requiere que el autoload de Composer ya esté cargado.
 */
class Mailer {

    /** ¿Hay credenciales SMTP suficientes para enviar? */
    public static function configurado(): bool {
        return trim((string) ajuste('smtp_user', defined('SMTP_USER') ? SMTP_USER : '')) !== ''
            && trim((string) ajuste('smtp_host', defined('SMTP_HOST') ? SMTP_HOST : '')) !== '';
    }

    /** Devuelve un PHPMailer listo (remitente y SMTP ya configurados). */
    public static function crear(): PHPMailer {
        $host     = ajuste('smtp_host', defined('SMTP_HOST') ? SMTP_HOST : 'localhost');
        $port     = (int) ajuste('smtp_port', defined('SMTP_PORT') ? SMTP_PORT : 587);
        $secure   = strtolower((string) ajuste('smtp_secure', $port === 465 ? 'ssl' : 'tls'));
        $user     = (string) ajuste('smtp_user', defined('SMTP_USER') ? SMTP_USER : '');
        $pass     = (string) ajuste('smtp_pass', defined('SMTP_PASS') ? SMTP_PASS : '');
        $fromName = (string) ajuste('smtp_from_name', defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'FichaPro');

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->Port       = $port;
        $mail->SMTPSecure = $secure === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';

        if ($user !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
        }

        $mail->setFrom($user !== '' ? $user : 'no-reply@fichapro.local', $fromName);
        return $mail;
    }
}
