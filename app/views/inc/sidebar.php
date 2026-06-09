<nav class="sidebar" id="sidebar">

    <!-- brand -->
    <div style="padding:1.25rem 1.25rem 1rem;border-bottom:1px solid rgba(255,255,255,.1);">
        <div style="display:flex;align-items:center;gap:.7rem;">
            <div style="width:2.4rem;height:2.4rem;border-radius:10px;background:var(--teal);display:flex;align-items:center;justify-content:center;font-size:1.2rem;box-shadow:0 3px 10px rgba(62,198,193,.4);">⏱</div>
            <div>
                <div style="font-weight:900;font-size:1.05rem;letter-spacing:.06em;color:#fff;line-height:1;">Ficha<span style="color:var(--teal);">Pro</span></div>
                <div style="font-size:.62rem;font-weight:700;letter-spacing:.12em;color:rgba(255,255,255,.4);margin-top:2px;">CONTROL HORARIO</div>
            </div>
        </div>
    </div>

    <!-- nav -->
    <ul style="flex:1;padding:.75rem .6rem;list-style:none;margin:0;display:flex;flex-direction:column;gap:2px;">
        <?php
        $items = [
            ['fichar',      'bi-clock',            'Fichar',        false],
            ['home',        'bi-speedometer2',     'Dashboard',     true],
            ['empleados',   'bi-people',           'Empleados',     true],
            ['fichajes',    'bi-calendar2-check',  'Fichajes',      true],
            ['jornadas',    'bi-calendar-range',   'Jornadas',      true],
            ['vacaciones',  'bi-airplane',         'Vacaciones',    false],
            ['ausencias',   'bi-clipboard2-pulse', 'Ausencias',     false],
            ['tareas',      'bi-list-task',        'Tareas',        false],
            ['incidencias', 'bi-exclamation-triangle', 'Incidencias', false],
            ['vehiculos',   'bi-car-front',        'Vehículos',     true],
            ['informes',    'bi-bar-chart-line',   'Informes',      true],
            ['ajustes',     'bi-gear',             'Ajustes',       true],
            ['auditoria',   'bi-shield-check',     'Auditoría',     true],
        ];
        foreach ($items as [$key, $icon, $label, $adminOnly]):
            if ($adminOnly && !isAdmin()) continue;
        ?>
        <li>
            <a href="<?= url($key) ?>" class="nav-link <?= navActive($key) ? 'active' : '' ?>">
                <i class="bi <?= $icon ?>" style="font-size:1rem;"></i><?= $label ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <!-- user -->
    <div style="padding:.75rem;border-top:1px solid rgba(255,255,255,.1);">
        <div style="display:flex;align-items:center;gap:.6rem;padding:.55rem .7rem;border-radius:9px;background:rgba(255,255,255,.07);margin-bottom:.4rem;">
            <div style="width:2rem;height:2rem;border-radius:50%;background:var(--teal);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:900;color:#fff;flex-shrink:0;">
                <?= strtoupper(mb_substr($_SESSION['emp_nombre'] ?? 'U', 0, 1)) ?>
            </div>
            <div style="overflow:hidden;">
                <div style="color:#fff;font-size:.78rem;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($_SESSION['emp_nombre'] ?? 'Usuario') ?></div>
                <div style="color:rgba(255,255,255,.4);font-size:.66rem;text-transform:capitalize;"><?= e($_SESSION['emp_rol'] ?? '') ?></div>
            </div>
        </div>
        <a href="<?= url('login/logout') ?>" class="nav-link" style="color:rgba(255,255,255,.4);font-size:.78rem;">
            <i class="bi bi-box-arrow-left"></i>Cerrar sesión
        </a>
    </div>
</nav>
