<?php
$hora = (int)date('H');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 20 ? 'Buenas tardes' : 'Buenas noches');
$totalPend = (int)$pendientes['vacaciones'] + (int)$pendientes['ausencias'] + (int)$pendientes['incidencias'];
?>

<div style="margin-bottom:1.75rem;">
    <h1 class="page-title"><?= $saludo ?>, <?= e($_SESSION['emp_nombre']) ?></h1>
    <p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= ucfirst(fechaCorta(date('Y-m-d'))) ?> · <?= e(ajuste('nombre_empresa','Mi Empresa')) ?></p>
</div>

<!-- stats -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.75rem;">
    <div class="stat">
        <div class="ic"><i class="bi bi-person-workspace"></i></div>
        <div class="num"><?= (int)$hoy['activos'] ?></div>
        <div class="lbl">Trabajando ahora</div>
    </div>
    <div class="stat">
        <div class="ic"><i class="bi bi-calendar2-check"></i></div>
        <div class="num"><?= (int)$hoy['fichajes_hoy'] ?></div>
        <div class="lbl">Fichajes hoy</div>
    </div>
    <div class="stat">
        <div class="ic"><i class="bi bi-clock-history"></i></div>
        <div class="num" style="font-size:1.5rem;"><?= horasLegibles($hoy['horas_hoy']) ?></div>
        <div class="lbl">Horas hoy</div>
    </div>
    <div class="stat">
        <div class="ic"><i class="bi bi-people"></i></div>
        <div class="num"><?= (int)$emp['activos'] ?></div>
        <div class="lbl">Empleados activos</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:3fr 2fr;gap:1.25rem;">

    <!-- working now -->
    <div class="card">
        <div class="card-head" style="justify-content:space-between;">
            <span><i class="bi bi-broadcast teal"></i> En activo ahora</span>
            <span class="muted" style="font-size:.75rem;font-weight:700;"><?= count($trabajando) ?> persona<?= count($trabajando)!=1?'s':'' ?></span>
        </div>
        <?php if (empty($trabajando)): ?>
            <p class="empty">Nadie está fichado en este momento.</p>
        <?php else: ?>
        <table class="tbl">
            <thead><tr><th>Empleado</th><th>Entrada</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($trabajando as $t): ?>
            <tr>
                <td style="font-weight:800;color:#0A2E4E;"><?= e($t['nombre'].' '.$t['apellidos']) ?></td>
                <td><?= hhmm($t['hora_entrada']) ?> h</td>
                <td><?= $t['en_descanso'] ? badge('En descanso','#fff7ed','#c2410c') : badge('Trabajando','#dcfce7','#166534') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- pending approvals -->
    <div class="card">
        <div class="card-head"><i class="bi bi-inbox teal"></i> Pendiente de revisar</div>
        <div style="padding:.5rem 0;">
            <?php
            $rows = [
                ['vacaciones',  'bi-airplane',         'Vacaciones',  (int)$pendientes['vacaciones']],
                ['ausencias',   'bi-clipboard2-pulse', 'Ausencias',   (int)$pendientes['ausencias']],
                ['incidencias', 'bi-exclamation-triangle','Incidencias',(int)$pendientes['incidencias']],
            ];
            foreach ($rows as [$k,$ic,$lbl,$n]): ?>
            <a href="<?= url($k) ?>" style="display:flex;align-items:center;gap:.8rem;padding:.7rem 1.25rem;text-decoration:none;border-top:1px solid #f1f5f9;">
                <div style="width:2.1rem;height:2.1rem;border-radius:9px;background:#e8f9f8;color:#3EC6C1;display:flex;align-items:center;justify-content:center;"><i class="bi <?= $ic ?>"></i></div>
                <span style="flex:1;font-weight:700;color:#0A2E4E;font-size:.88rem;"><?= $lbl ?></span>
                <?php if ($n>0): ?>
                <span style="background:#ef4444;color:#fff;font-size:.72rem;font-weight:800;padding:2px 9px;border-radius:99px;"><?= $n ?></span>
                <?php else: ?>
                <span class="muted" style="font-size:.78rem;">—</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPend === 0): ?>
        <p style="text-align:center;padding:.5rem 0 1.25rem;color:#16a34a;font-weight:700;font-size:.82rem;"><i class="bi bi-check-circle"></i> Todo al día</p>
        <?php endif; ?>
    </div>
</div>
