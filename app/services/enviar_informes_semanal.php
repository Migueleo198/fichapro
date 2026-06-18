<?php

/**
 * Informe SEMANAL por correo a cada trabajador (semana anterior, lun–dom).
 *
 * Ejecutar manualmente:
 *   php app/services/enviar_informes_semanal.php
 *
 * Programar (Windows · Programador de tareas, cada lunes):
 *   C:\xampp\php\php.exe C:\xampp\htdocs\fichapro\app\services\enviar_informes_semanal.php
 */

require_once __DIR__ . '/_bootstrap_cli.php';

[$desde, $hasta] = fp_rango_periodo('semanal');
$db = (new Database())->conectar();

$r = fp_enviar_informes($db, $desde, $hasta, 'Informe semanal de horas');

echo "Informe SEMANAL · periodo {$desde} a {$hasta}\n";
echo "Enviados: {$r['enviados']} · Omitidos (sin actividad): {$r['omitidos']} · Emails inválidos: " . ($r['invalidos'] ?? 0) . "\n";
foreach ($r['errores'] as $err) echo "  AVISO: {$err}\n";
echo "Proceso finalizado.\n";
