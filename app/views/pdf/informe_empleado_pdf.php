<?php
/**
 * Informe individual de un empleado (PDF adjunto en los correos).
 * Variables: $empresa, $empleado, $desde, $hasta, $resumen, $dias, $compensaciones
 */
$pagadas = 0; $recuperadas = 0;
foreach ($compensaciones as $c) {
    if ($c['tipo'] === 'pagada') $pagadas += (float)$c['horas'];
    else                         $recuperadas += (float)$c['horas'];
}
$compTotal = $pagadas + $recuperadas;
$extra     = (float)($resumen['extra'] ?? 0);
$saldo     = max(0, $extra - $compTotal);
$nombreEmp = trim(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellidos'] ?? ''));
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><style>
@page{margin:1.2cm 1.4cm;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DejaVu Sans',sans-serif;color:#1E3A8A;font-size:11px;}
.header{background:#1E3A8A;color:#fff;padding:18px 22px;border-radius:8px;margin-bottom:16px;}
.brand{font-size:18px;font-weight:bold;}.brand .t{color:#1A649C;}
.sub{color:#9fb8cc;font-size:10px;margin-top:3px;}
.meta{text-align:right;font-size:10px;color:#9fb8cc;}
.who{font-size:13px;font-weight:bold;margin:0 0 12px;}
.who small{display:block;font-weight:normal;color:#64748B;font-size:10px;margin-top:2px;}
.cards{width:100%;margin-bottom:14px;border-collapse:separate;border-spacing:6px 0;}
.cards td{width:20%;padding:9px 6px;border:1px solid #E2E8F0;border-radius:8px;text-align:center;}
.cards .n{font-size:15px;font-weight:bold;color:#1E3A8A;}
.cards .l{font-size:8px;color:#64748B;text-transform:uppercase;}
.sect{font-size:12px;font-weight:bold;border-bottom:2px solid #1A649C;padding-bottom:5px;margin:16px 0 4px;}
table.data{width:100%;border-collapse:collapse;margin-top:6px;}
table.data th{background:#1E3A8A;color:#fff;font-size:9px;text-transform:uppercase;padding:7px 9px;text-align:left;}
table.data th.r,table.data td.r{text-align:right;}
table.data td{padding:7px 9px;border-bottom:1px solid #eef2f6;}
.badge{display:inline-block;padding:1px 7px;border-radius:99px;font-size:8.5px;font-weight:bold;}
.b-pag{background:#dcfce7;color:#166534;}
.b-rec{background:#e0f2fe;color:#0369a1;}
.empty{color:#94a3b8;font-style:italic;padding:8px 2px;font-size:10px;}
.foot{margin-top:18px;text-align:center;color:#9fb8cc;font-size:8.5px;border-top:1px solid #E2E8F0;padding-top:8px;}
</style></head><body>

<table width="100%"><tr>
<td class="header" style="display:table-cell;">
    <table width="100%"><tr>
        <td><div class="brand">Ficha<span class="t">Pro</span></div><div class="sub"><?= e($empresa) ?></div></td>
        <td class="meta">Informe individual de horas<br><?= fechaLarga($desde) ?> — <?= fechaLarga($hasta) ?></td>
    </tr></table>
</td></tr></table>

<p class="who"><?= e($nombreEmp) ?><small><?= e($empleado['email'] ?? '') ?></small></p>

<table class="cards"><tr>
    <td><div class="n"><?= (int)($resumen['dias'] ?? 0) ?></div><div class="l">Días</div></td>
    <td><div class="n"><?= horasLegibles($resumen['horas'] ?? 0) ?></div><div class="l">Horas totales</div></td>
    <td><div class="n"><?= horasLegibles($extra) ?></div><div class="l">Horas extra</div></td>
    <td><div class="n"><?= horasLegibles($compTotal) ?></div><div class="l">Compensadas</div></td>
    <td><div class="n"><?= horasLegibles($saldo) ?></div><div class="l">Saldo extra</div></td>
</tr></table>

<div class="sect">Fichajes del periodo</div>
<table class="data">
    <thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th class="r">Horas</th><th class="r">Extra</th></tr></thead>
    <tbody>
    <?php if (empty($dias)): ?>
        <tr><td colspan="5" class="empty">Sin fichajes registrados en el periodo.</td></tr>
    <?php else: foreach ($dias as $d): ?>
        <tr>
            <td><?= fechaLarga($d['fecha']) ?></td>
            <td><?= hhmm($d['hora_entrada']) ?></td>
            <td><?= hhmm($d['hora_salida']) ?></td>
            <td class="r"><?= horasLegibles($d['total_horas']) ?></td>
            <td class="r"><?= (float)$d['horas_extra'] > 0 ? horasLegibles($d['horas_extra']) : '—' ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<div class="sect">Compensación de horas</div>
<table class="data">
    <thead><tr><th>Fecha</th><th>Tipo</th><th class="r">Horas</th><th>Concepto</th></tr></thead>
    <tbody>
    <?php if (empty($compensaciones)): ?>
        <tr><td colspan="4" class="empty">Sin compensaciones registradas en el periodo.</td></tr>
    <?php else: foreach ($compensaciones as $c): ?>
        <tr>
            <td><?= fechaLarga($c['fecha']) ?></td>
            <td><span class="badge <?= $c['tipo']==='pagada'?'b-pag':'b-rec' ?>"><?= $c['tipo']==='pagada'?'Pagada':'Recuperada' ?></span></td>
            <td class="r"><?= horasLegibles($c['horas']) ?></td>
            <td><?= e($c['concepto'] ?? '') ?: '—' ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<div class="foot">Generado por FichaPro · <?= date('d/m/Y H:i') ?> · <?= e($empresa) ?></div>
</body></html>
