<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">Auditoría</h1>
    <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Registro de actividad del sistema</p>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-shield-check teal"></i> Últimos movimientos</div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>Detalle</th></tr></thead>
        <tbody>
        <?php foreach ($lista as $a): ?>
        <tr>
            <td style="white-space:nowrap;font-weight:700;"><?= date('d/m/Y H:i', strtotime($a['fecha'])) ?></td>
            <td><?= $a['nombre'] ? e($a['nombre'].' '.$a['apellidos']) : '<span class="muted">Sistema</span>' ?></td>
            <td><?= badge($a['accion'],'#e0f9f8','#0A2E4E') ?></td>
            <td class="mid" style="font-family:monospace;font-size:.78rem;"><?= e($a['tabla']) ?></td>
            <td class="mid" style="font-size:.82rem;"><?= e($a['detalle']) ?: '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lista)): ?><tr><td colspan="5" class="empty">Sin actividad registrada todavía.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
