<?php
$mapaComp       = $mapaComp       ?? [];
$compTotales    = $compTotales    ?? ['pagadas'=>0,'recuperadas'=>0,'total'=>0];
$compensaciones = $compensaciones ?? [];
$empleados      = $empleados      ?? [];
$smtpOk         = $smtpOk         ?? false;
$qs             = $qs             ?? ('desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta));

$okMsg = [
    'comp_add'  => 'Compensación de horas registrada.',
    'comp_del'  => 'Compensación eliminada.',
    'enviados'  => 'Informe enviado por correo a ' . (int)($n ?? 0) . ' trabajador(es).',
];
$errMsg = [
    'comp_datos'    => 'Revisa los datos de la compensación: empleado, fecha y horas (> 0).',
    'smtp'          => 'Configura el correo SMTP en Configuración → Sistema antes de enviar.',
    'vendor'        => 'Falta la librería de correo. Ejecuta «composer install».',
    'envio_parcial' => 'Envío finalizado con incidencias. Enviados: ' . (int)($n ?? 0) . '. Revisa el registro del servidor.',
];
?>
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Informes</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= fechaLarga($desde) ?> — <?= fechaLarga($hasta) ?></p></div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button type="button" class="btn btn-ghost" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-funnel"></i> Filtros
            </button>
            <div class="dropdown-menu dropdown-menu-end fp-filtros">
                <form method="GET" action="<?= url('informes') ?>">
                    <label class="lbl">Desde</label><input type="date" name="desde" value="<?= e($desde) ?>" class="inp mb-2">
                    <label class="lbl">Hasta</label><input type="date" name="hasta" value="<?= e($hasta) ?>" class="inp mb-3">
                    <button class="btn btn-teal w-100" style="justify-content:center;"><i class="bi bi-search me-1"></i>Generar</button>
                </form>
            </div>
        </div>

        <?php if ($smtpOk): ?>
        <form method="POST" action="<?= url('informes/enviar') ?>" style="margin:0;"
              onsubmit="return confirm('¿Enviar el informe del periodo (<?= fechaLarga($desde) ?> — <?= fechaLarga($hasta) ?>) por correo a cada trabajador con actividad?');">
            <input type="hidden" name="desde" value="<?= e($desde) ?>">
            <input type="hidden" name="hasta" value="<?= e($hasta) ?>">
            <button class="btn" style="background:#1E3A8A;color:#fff;"><i class="bi bi-envelope-paper"></i> Enviar por email</button>
        </form>
        <?php else: ?>
        <a href="<?= url('ajustes?tab=sistema') ?>" class="btn btn-ghost" title="Configura el correo SMTP para poder enviar informes">
            <i class="bi bi-envelope-exclamation"></i> Configurar envío
        </a>
        <?php endif; ?>

        <a href="<?= url('informes/pdf?'.$qs) ?>" target="_blank" class="btn" style="background:#dc2626;color:#fff;"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
    </div>
</div>

<?php if (($ok ?? null) && isset($okMsg[$ok])): ?>
<div class="cfg-alert cfg-alert-ok" style="margin-bottom:1rem;"><i class="bi bi-check-circle-fill"></i> <?= $okMsg[$ok] ?></div>
<?php endif; ?>
<?php if (($err ?? null) && isset($errMsg[$err])): ?>
<div class="cfg-alert cfg-alert-err" style="margin-bottom:1rem;"><i class="bi bi-exclamation-triangle-fill"></i> <?= $errMsg[$err] ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.75rem;">
    <div class="stat"><div class="ic"><i class="bi bi-calendar2-check"></i></div><div class="num"><?= (int)$totales['fichajes'] ?></div><div class="lbl">Fichajes</div></div>
    <div class="stat"><div class="ic"><i class="bi bi-people"></i></div><div class="num"><?= (int)$totales['empleados'] ?></div><div class="lbl">Empleados</div></div>
    <div class="stat"><div class="ic"><i class="bi bi-clock-history"></i></div><div class="num" style="font-size:1.4rem;"><?= horasLegibles($totales['horas']) ?></div><div class="lbl">Horas totales</div></div>
    <div class="stat" style="border-left-color:#f59e0b;"><div class="ic" style="background:#fff7ed;color:#c2410c;"><i class="bi bi-clock"></i></div><div class="num" style="font-size:1.4rem;"><?= horasLegibles($totales['extra']) ?></div><div class="lbl">Horas extra</div></div>
</div>

<div style="display:grid;grid-template-columns:3fr 2fr;gap:1.25rem;">
    <div class="card">
        <div class="card-head"><i class="bi bi-person-lines-fill teal"></i> Horas por empleado</div>
        <div style="overflow-x:auto;">
        <table class="tbl">
            <thead><tr><th>Empleado</th><th>Días</th><th>Horas</th><th>Extra</th><th>Compensadas</th><th>Saldo extra</th></tr></thead>
            <tbody>
            <?php foreach ($tabla as $r):
                $c    = $mapaComp[(int)$r['id']] ?? ['pagadas'=>0,'recuperadas'=>0,'total'=>0];
                $saldo = max(0, (float)$r['extra'] - $c['total']);
            ?>
            <tr>
                <td style="font-weight:800;color:#1E3A8A;"><?= e($r['nombre'].' '.$r['apellidos']) ?></td>
                <td><?= (int)$r['dias'] ?></td>
                <td style="font-weight:700;"><?= horasLegibles($r['horas']) ?></td>
                <td><?= $r['extra']>0 ? '<span style="color:#c2410c;font-weight:700;">'.horasLegibles($r['extra']).'</span>' : '—' ?></td>
                <td>
                    <?php if ($c['total']>0): ?>
                        <span style="font-weight:700;"><?= horasLegibles($c['total']) ?></span>
                        <span class="muted" style="font-size:.72rem;display:block;"><?= horasLegibles($c['pagadas']) ?> pag · <?= horasLegibles($c['recuperadas']) ?> rec</span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td style="font-weight:700;<?= $saldo>0?'color:#c2410c;':'color:#16a34a;' ?>"><?= horasLegibles($saldo) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><i class="bi bi-bar-chart teal"></i> Horas por día</div>
        <div style="padding:1.25rem;">
            <?php if (!empty($porDia)): $max = max(array_map(fn($d)=>(float)$d['horas'],$porDia)) ?: 1; ?>
            <div style="display:flex;align-items:flex-end;gap:3px;height:140px;">
                <?php foreach ($porDia as $d): $h=max(3,(int)round(($d['horas']/$max)*125)); ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;min-width:0;">
                    <div title="<?= fechaLarga($d['fecha']) ?>: <?= horasLegibles($d['horas']) ?>" style="width:100%;border-radius:4px 4px 0 0;background:#1A649C;height:<?= $h ?>px;cursor:help;"></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;gap:3px;margin-top:.35rem;">
                <?php foreach ($porDia as $d): ?><div style="flex:1;text-align:center;min-width:0;"><span style="font-size:8px;color:#64748B;font-weight:700;"><?= date('d/m',strtotime($d['fecha'])) ?></span></div><?php endforeach; ?>
            </div>
            <?php else: ?><p class="empty">Sin datos en el período.</p><?php endif; ?>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- COMPENSACIÓN DE HORAS (pagadas / recuperadas) -->
<!-- ============================================================ -->
<div style="display:grid;grid-template-columns:2fr 3fr;gap:1.25rem;margin-top:1.25rem;">

    <!-- Alta manual -->
    <div class="card">
        <div class="card-head"><i class="bi bi-cash-coin" style="color:#16a34a;"></i> Registrar compensación</div>
        <form method="POST" action="<?= url('informes/crearCompensacion') ?>" style="padding:1.3rem;">
            <input type="hidden" name="desde" value="<?= e($desde) ?>">
            <input type="hidden" name="hasta" value="<?= e($hasta) ?>">
            <label class="lbl">Empleado</label>
            <select name="id_empleado" class="inp mb-2" required>
                <option value="">— Selecciona —</option>
                <?php foreach ($empleados as $emp): ?>
                <option value="<?= (int)$emp['id'] ?>"><?= e($emp['nombre'].' '.$emp['apellidos']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="row g-2">
                <div class="col-6"><label class="lbl">Fecha</label><input type="date" name="fecha" value="<?= e(date('Y-m-d')) ?>" class="inp" required></div>
                <div class="col-6"><label class="lbl">Horas</label><input type="number" name="horas" min="0.25" max="999" step="0.25" class="inp" placeholder="Ej: 2.5" required></div>
            </div>
            <label class="lbl mt-2">Tipo</label>
            <select name="tipo" class="inp mb-2">
                <option value="pagada">Pagada (en nómina)</option>
                <option value="recuperada">Recuperada (descanso)</option>
            </select>
            <label class="lbl">Concepto (opcional)</label>
            <input name="concepto" class="inp mb-3" maxlength="255" placeholder="Ej: Horas extra de marzo">
            <button class="btn btn-teal w-100" style="justify-content:center;"><i class="bi bi-plus-lg"></i> Registrar compensación</button>
        </form>
    </div>

    <!-- Listado del periodo -->
    <div class="card">
        <div class="card-head" style="display:flex;justify-content:space-between;align-items:center;">
            <span><i class="bi bi-list-ul teal"></i> Compensaciones del periodo</span>
            <span class="badge" style="background:var(--teal-bg,#E0F9F8);color:var(--navy,#1E3A8A);">
                <?= horasLegibles($compTotales['pagadas']) ?> pag · <?= horasLegibles($compTotales['recuperadas']) ?> rec
            </span>
        </div>
        <div style="overflow-x:auto;">
        <?php if (empty($compensaciones)): ?>
            <div class="empty" style="padding:1.4rem;text-align:center;color:#94a3b8;"><i class="bi bi-inbox" style="font-size:1.6rem;display:block;margin-bottom:.3rem;"></i>Sin compensaciones registradas en este periodo.</div>
        <?php else: ?>
        <table class="tbl">
            <thead><tr><th>Empleado</th><th>Fecha</th><th>Tipo</th><th>Horas</th><th>Concepto</th><th style="text-align:right;">Acción</th></tr></thead>
            <tbody>
            <?php foreach ($compensaciones as $c): ?>
            <tr>
                <td style="font-weight:700;color:#1E3A8A;"><?= e($c['nombre'].' '.$c['apellidos']) ?></td>
                <td><?= fechaLarga($c['fecha']) ?></td>
                <td><?= $c['tipo']==='pagada'
                        ? badge('Pagada', '#dcfce7', '#166534')
                        : badge('Recuperada', '#e0f2fe', '#0369a1') ?></td>
                <td style="font-weight:700;"><?= horasLegibles($c['horas']) ?></td>
                <td class="muted"><?= e($c['concepto'] ?? '') ?: '—' ?></td>
                <td style="text-align:right;">
                    <a href="<?= url('informes/eliminarCompensacion/'.$c['id'].'?'.$qs) ?>"
                       class="btn btn-danger btn-sm" title="Eliminar"
                       onclick="return confirm('¿Eliminar esta compensación?');"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        </div>
    </div>
</div>
