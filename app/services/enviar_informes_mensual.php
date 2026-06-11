<?php

/**
 * Informe MENSUAL por correo a cada trabajador (mes anterior completo).
 *
 * Ejecutar manualmente:
 *   php app/services/enviar_informes_mensual.php
 *
 * Programar (Windows · Programador de tareas, día 1 de cada mes):
 *   C:\xampp\php\php.exe C:\xampp\htdocs\fichapro\app\services\enviar_informes_mensual.php
 */

require_once __DIR__ . '/_bootstrap_cli.php';

[$desde, $hasta] = fp_rango_periodo('mensual');
$db = (new Database())->conectar();

$r = fp_enviar_informes($db, $desde, $hasta, 'Informe mensual de horas');

echo "Informe MENSUAL · periodo {$desde} a {$hasta}\n";
echo "Enviados: {$r['enviados']} · Omitidos (sin email/actividad): {$r['omitidos']}\n";
foreach ($r['errores'] as $err) echo "  ERROR: {$err}\n";
echo "Proceso finalizado.\n";
