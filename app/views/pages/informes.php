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
        <a href="<?= url('informes/pdf?desde='.$desde.'&hasta='.$hasta) ?>" target="_blank" class="btn" style="background:#dc2626;color:#fff;"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
    </div>
</div>

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
            <thead><tr><th>Empleado</th><th>Días</th><th>Horas</th><th>Extra</th></tr></thead>
            <tbody>
            <?php foreach ($tabla as $r): ?>
            <tr>
                <td style="font-weight:800;color:#1E3A8A;"><?= e($r['nombre'].' '.$r['apellidos']) ?></td>
                <td><?= (int)$r['dias'] ?></td>
                <td style="font-weight:700;"><?= horasLegibles($r['horas']) ?></td>
                <td><?= $r['extra']>0 ? '<span style="color:#c2410c;font-weight:700;">'.horasLegibles($r['extra']).'</span>' : '—' ?></td>
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
                    <div title="<?= fechaLarga($d['fecha']) ?>: <?= horasLegibles($d['horas']) ?>" style="width:100%;border-radius:4px 4px 0 0;background:#2563EB;height:<?= $h ?>px;cursor:help;"></div>
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
