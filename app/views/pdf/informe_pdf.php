<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><style>
@page{margin:1.2cm 1.4cm;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DejaVu Sans',sans-serif;color:#1E3A8A;font-size:11px;}
.header{background:#1E3A8A;color:#fff;padding:18px 22px;border-radius:8px;margin-bottom:18px;}
.brand{font-size:18px;font-weight:bold;}.brand .t{color:#1A649C;}
.sub{color:#9fb8cc;font-size:10px;margin-top:3px;}
.meta{text-align:right;font-size:10px;color:#9fb8cc;}
.cards{width:100%;margin-bottom:16px;}
.cards td{width:25%;padding:10px;border:1px solid #E2E8F0;border-radius:8px;text-align:center;}
.cards .n{font-size:18px;font-weight:bold;color:#1E3A8A;}
.cards .l{font-size:9px;color:#64748B;text-transform:uppercase;}
table.data{width:100%;border-collapse:collapse;margin-top:6px;}
table.data th{background:#1E3A8A;color:#fff;font-size:9px;text-transform:uppercase;padding:8px 10px;text-align:left;}
table.data th.r,table.data td.r{text-align:right;}
table.data td{padding:8px 10px;border-bottom:1px solid #eef2f6;}
.sect{font-size:12px;font-weight:bold;border-bottom:2px solid #1A649C;padding-bottom:5px;margin:16px 0 4px;}
.foot{margin-top:18px;text-align:center;color:#9fb8cc;font-size:8.5px;border-top:1px solid #E2E8F0;padding-top:8px;}
</style></head><body>

<table width="100%"><tr>
<td class="header" style="display:table-cell;">
    <table width="100%"><tr>
        <td><div class="brand">Ficha<span class="t">Pro</span></div><div class="sub"><?= e($empresa) ?></div></td>
        <td class="meta">Informe de horas<br><?= fechaLarga($desde) ?> — <?= fechaLarga($hasta) ?></td>
    </tr></table>
</td></tr></table>

<table class="cards"><tr>
    <td><div class="n"><?= (int)$totales['fichajes'] ?></div><div class="l">Fichajes</div></td>
    <td><div class="n"><?= (int)$totales['empleados'] ?></div><div class="l">Empleados</div></td>
    <td><div class="n"><?= horasLegibles($totales['horas']) ?></div><div class="l">Horas totales</div></td>
    <td><div class="n"><?= horasLegibles($totales['extra']) ?></div><div class="l">Horas extra</div></td>
</tr></table>

<?php $mapaComp = $mapaComp ?? []; ?>
<div class="sect">Horas por empleado</div>
<table class="data">
    <thead><tr><th>Empleado</th><th class="r">Días</th><th class="r">Horas</th><th class="r">Extra</th><th class="r">Compensadas</th><th class="r">Saldo extra</th></tr></thead>
    <tbody>
    <?php foreach ($tabla as $r):
        $c     = $mapaComp[(int)$r['id']] ?? ['pagadas'=>0,'recuperadas'=>0,'total'=>0];
        $saldo = max(0, (float)$r['extra'] - $c['total']);
    ?>
    <tr>
        <td style="font-weight:bold;"><?= e($r['nombre'].' '.$r['apellidos']) ?></td>
        <td class="r"><?= (int)$r['dias'] ?></td>
        <td class="r"><?= horasLegibles($r['horas']) ?></td>
        <td class="r"><?= $r['extra']>0 ? horasLegibles($r['extra']) : '—' ?></td>
        <td class="r"><?= $c['total']>0 ? horasLegibles($c['total']) : '—' ?></td>
        <td class="r"><?= horasLegibles($saldo) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php if (($compTotales['total'] ?? 0) > 0): ?>
<div class="sect">Resumen de compensación de horas</div>
<table class="data">
    <thead><tr><th>Concepto</th><th class="r">Horas</th></tr></thead>
    <tbody>
        <tr><td>Pagadas (en nómina)</td><td class="r"><?= horasLegibles($compTotales['pagadas']) ?></td></tr>
        <tr><td>Recuperadas (descanso)</td><td class="r"><?= horasLegibles($compTotales['recuperadas']) ?></td></tr>
        <tr><td style="font-weight:bold;">Total compensado</td><td class="r" style="font-weight:bold;"><?= horasLegibles($compTotales['total']) ?></td></tr>
    </tbody>
</table>
<?php endif; ?>

<div class="foot">Generado por FichaPro · <?= date('d/m/Y H:i') ?> · Documento interno</div>
</body></html>
