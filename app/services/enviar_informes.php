<?php

/**
 * Núcleo de envío de informes individuales por correo.
 *
 * Este archivo SÓLO define funciones; no ejecuta nada al incluirse.
 * Lo usan tanto los scripts programados (enviar_informes_semanal.php /
 * enviar_informes_mensual.php) como el botón "Enviar por email" de Informes.
 *
 * Requisitos previos al llamar a fp_enviar_informes():
 *   - constante RUTA_APP definida
 *   - autoload de Composer cargado (PHPMailer + Dompdf)
 *   - helpers cargados (ajuste(), fechaLarga(), horasLegibles()…)
 */

require_once RUTA_APP . '/models/EmpleadoModel.php';
require_once RUTA_APP . '/models/InformeModel.php';
require_once RUTA_APP . '/models/CompensacionModel.php';
require_once RUTA_APP . '/services/Mailer.php';
require_once RUTA_APP . '/services/ReportGenerator.php';

/** Calcula [desde, hasta] del periodo: 'semanal' (semana pasada) o 'mensual' (mes pasado). */
function fp_rango_periodo(string $periodo): array {
    if ($periodo === 'mensual') {
        $desde = date('Y-m-01', strtotime('first day of last month'));
        $hasta = date('Y-m-t',  strtotime('last day of last month'));
    } else { // semanal: lunes a domingo de la semana anterior
        $desde = date('Y-m-d', strtotime('monday last week'));
        $hasta = date('Y-m-d', strtotime('sunday last week'));
    }
    return [$desde, $hasta];
}

/**
 * Envía a cada empleado activo (con email) su informe individual del periodo.
 *
 * @return array ['enviados'=>int, 'omitidos'=>int, 'errores'=>string[]]
 */
function fp_enviar_informes(PDO $db, string $desde, string $hasta, string $etiqueta = 'Informe de horas'): array {
    $empModel  = new EmpleadoModel($db);
    $infModel  = new InformeModel($db);
    $compModel = new CompensacionModel($db);
    $empresa   = ajuste('nombre_empresa', 'Mi Empresa');

    $enviados = 0; $omitidos = 0; $invalidos = 0; $errores = [];
    $mail = null;   // una sola conexión SMTP reutilizada para todos los envíos

    foreach ($empModel->getAll(true) as $emp) {           // sólo activos
        $empId   = (int) $emp['id'];
        $email   = trim($emp['email'] ?? '');
        $resumen = $infModel->resumenEmpleado($empId, $desde, $hasta);
        $comps   = $compModel->getByEmpleado($empId, $desde, $hasta);

        // Sin actividad ni compensaciones en el periodo → no se envía nada.
        if ((int)($resumen['dias'] ?? 0) === 0 && empty($comps)) { $omitidos++; continue; }

        // Email ausente o con formato incorrecto → no se intenta enviar.
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $invalidos++;
            $errores[] = trim(($emp['nombre'] ?? '') . ' ' . ($emp['apellidos'] ?? '')) . ': email no válido (' . ($email ?: 'vacío') . ')';
            continue;
        }

        try {
            if ($mail === null) {
                $mail = Mailer::crear();
                $mail->SMTPKeepAlive = true;          // mantiene la conexión abierta entre envíos
            } else {
                $mail->clearAllRecipients();
                $mail->clearAttachments();
            }

            $pdf = ReportGenerator::pdfEmpleado([
                'empresa'        => $empresa,
                'empleado'       => $emp,
                'desde'          => $desde,
                'hasta'          => $hasta,
                'resumen'        => $resumen,
                'dias'           => $infModel->fichajesEmpleado($empId, $desde, $hasta),
                'compensaciones' => $comps,
            ]);

            $mail->addAddress($email, trim(($emp['nombre'] ?? '') . ' ' . ($emp['apellidos'] ?? '')));
            $mail->isHTML(true);
            $mail->Subject = "$etiqueta · " . fechaLarga($desde) . ' — ' . fechaLarga($hasta);
            $mail->Body    = fp_cuerpo_informe($emp, $empresa, $desde, $hasta, $resumen, $comps);
            $mail->AltBody = "Adjuntamos tu informe de horas del periodo {$desde} a {$hasta}.";
            $mail->addStringAttachment($pdf, "informe_{$empId}_{$desde}_{$hasta}.pdf");
            $mail->send();
            $enviados++;
        } catch (\Throwable $e) {
            $errores[] = $email . ': ' . ($mail ? $mail->ErrorInfo : $e->getMessage());
        }
    }

    if ($mail !== null) { try { $mail->smtpClose(); } catch (\Throwable $e) {} }

    return ['enviados' => $enviados, 'omitidos' => $omitidos, 'invalidos' => $invalidos, 'errores' => $errores];
}

/** Cuerpo HTML del correo. */
function fp_cuerpo_informe(array $emp, string $empresa, string $desde, string $hasta, array $resumen, array $comps): string {
    $nombre      = e($emp['nombre'] ?? '');
    $pagadas     = 0; $recuperadas = 0;
    foreach ($comps as $c) {
        if ($c['tipo'] === 'pagada') $pagadas += (float)$c['horas'];
        else                         $recuperadas += (float)$c['horas'];
    }
    $extra = (float)($resumen['extra'] ?? 0);
    $saldo = $extra - $pagadas - $recuperadas;

    $lineaComp = '';
    if ($pagadas > 0 || $recuperadas > 0) {
        $lineaComp =
            '<li>Horas compensadas: <b>' . horasLegibles($pagadas + $recuperadas) . '</b>'
            . ' (' . horasLegibles($pagadas) . ' pagadas · ' . horasLegibles($recuperadas) . ' recuperadas)</li>'
            . '<li>Saldo de horas extra pendiente: <b>' . horasLegibles(max(0, $saldo)) . '</b></li>';
    }

    return '
        <div style="font-family:Arial,sans-serif;color:#1E3A8A;">
            <p>Hola <b>' . $nombre . '</b>,</p>
            <p>Adjuntamos tu informe de horas de <b>' . e($empresa) . '</b> correspondiente al periodo
               <b>' . fechaLarga($desde) . '</b> — <b>' . fechaLarga($hasta) . '</b>.</p>
            <ul>
                <li>Días trabajados: <b>' . (int)($resumen['dias'] ?? 0) . '</b></li>
                <li>Horas totales: <b>' . horasLegibles($resumen['horas'] ?? 0) . '</b></li>
                <li>Horas extra: <b>' . horasLegibles($extra) . '</b></li>
                ' . $lineaComp . '
            </ul>
            <p style="color:#64748B;font-size:13px;">Tienes el detalle completo en el PDF adjunto.</p>
            <p style="color:#64748B;font-size:13px;">— ' . e($empresa) . ' · FichaPro</p>
        </div>';
}
