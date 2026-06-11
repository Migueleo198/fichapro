<?php
$rr          = $resumenRetrasos;
$diasLabels  = array_column($retrasosDiarios, 'fecha');
$diasRet     = array_column($retrasosDiarios, 'retrasos');
$diasTotal   = array_column($retrasosDiarios, 'total_fichajes');
$empNombres  = array_map(fn($e)=>$e['nombre'].' '.$e['apellidos'], $retrasosEmpleado);
$empRetrasos = array_column($retrasosEmpleado, 'retrasos');
?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title"><i class="bi bi-alarm" style="color:var(--teal);"></i> Estadísticas – Retrasos</h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Análisis de puntualidad en los fichajes</p>
    </div>
    <div class="dropdown">
        <button type="button" class="btn btn-ghost" data-bs-toggle="dropdown" data-bs-auto-close="outside">
            <i class="bi bi-funnel"></i> Filtros
        </button>
        <div class="dropdown-menu dropdown-menu-end fp-filtros">
            <form method="GET">
                <label class="lbl">Desde</label>
                <input type="date" name="desde" value="<?= e($desde) ?>" class="inp mb-2">
                <label class="lbl">Hasta</label>
                <input type="date" name="hasta" value="<?= e($hasta) ?>" class="inp mb-3">
                <button class="btn btn-teal w-100" style="justify-content:center;"><i class="bi bi-funnel me-1"></i>Aplicar</button>
            </form>
        </div>
    </div>
</div>

<!-- NAV TABS -->
<ul class="stats-nav d-flex mb-4">
    <li><a class="nav-link" href="<?= url('estadisticas/resumen') ?>"><i class="bi bi-grid-fill me-1"></i>Resumen</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/fichajes') ?>"><i class="bi bi-clock-history me-1"></i>Fichajes</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/horas') ?>"><i class="bi bi-hourglass-split me-1"></i>Horas</a></li>
    <li><a class="nav-link active" href="<?= url('estadisticas/retrasos') ?>"><i class="bi bi-alarm me-1"></i>Retrasos</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/actividad') ?>"><i class="bi bi-activity me-1"></i>Actividad</a></li>
</ul>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-alarm-fill"></i></div><div class="kpi-value"><?= $rr['total_retrasos'] ?? 0 ?></div><div class="kpi-label">Total retrasos</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-navy"><div class="kpi-icon"><i class="bi bi-clock-history"></i></div><div class="kpi-value"><?= $rr['total_fichajes'] ?? 0 ?></div><div class="kpi-label">Total fichajes</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-red"><div class="kpi-icon"><i class="bi bi-percent"></i></div><div class="kpi-value"><?= $rr['pct_retraso'] ?? 0 ?>%</div><div class="kpi-label">Tasa de retraso</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-purple"><div class="kpi-icon"><i class="bi bi-person-fill-exclamation"></i></div><div class="kpi-value" style="font-size:1rem;line-height:1.3;"><?= e($rr['empleado_mas_retrasos'] ?? '—') ?></div><div class="kpi-label">Mayor número retrasos</div></div></div>
</div>

<!-- CHARTS -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-bar-chart me-2 text-danger"></i>Retrasos por día vs total fichajes</h6></div>
            <div class="chart-card-body"><canvas id="chartRetrasosDia" height="90"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-bar-chart-fill me-2 text-warning"></i>Retrasos por empleado</h6></div>
            <div class="chart-card-body"><canvas id="chartRetrasosEmp" height="170"></canvas></div>
        </div>
    </div>
</div>

<!-- TABLE HORA MEDIA -->
<div class="chart-card mb-4">
    <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-clock-fill me-2" style="color:var(--teal);"></i>Hora media de entrada por empleado</h6></div>
    <div class="chart-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover stats-table mb-0">
                <thead><tr><th>Empleado</th><th>Hora media entrada</th><th>Retrasos registrados</th><th>Puntualidad</th></tr></thead>
                <tbody>
                <?php foreach ($horaEntrada as $emp): ?>
                <tr>
                    <td class="fw-semibold"><?= e($emp['nombre'].' '.$emp['apellidos']) ?></td>
                    <td><span class="badge" style="background:#f8f9fa;color:var(--navy);font-size:.85rem;"><i class="bi bi-clock me-1"></i><?= $emp['hora_media_entrada'] ?></span></td>
                    <td><?= $emp['retrasos']>0 ? '<span class="badge bg-danger">'.$emp['retrasos'].' retraso'.($emp['retrasos']>1?'s':'').'</span>' : '<span class="badge" style="background:var(--teal);color:#fff;">Sin retrasos</span>' ?></td>
                    <td>
                        <?php if($emp['retrasos']==0): ?>
                            <span class="fw-semibold" style="color:#059669;"><i class="bi bi-check-circle-fill me-1"></i>Excelente</span>
                        <?php elseif($emp['retrasos']<=2): ?>
                            <span class="fw-semibold text-warning"><i class="bi bi-dash-circle-fill me-1"></i>Regular</span>
                        <?php else: ?>
                            <span class="fw-semibold text-danger"><i class="bi bi-x-circle-fill me-1"></i>Mejorable</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($horaEntrada)): ?><tr><td colspan="4" class="empty py-4">Sin datos disponibles</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TABLE RANKING -->
<div class="chart-card mb-4">
    <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-people me-2 text-danger"></i>Ranking retrasos por empleado</h6></div>
    <div class="chart-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover stats-table mb-0">
                <thead><tr><th>Empleado</th><th>Fichajes</th><th>Retrasos</th><th>% Retraso</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($retrasosEmpleado as $emp):
                    $pct = (float)($emp['pct_retraso'] ?? 0);
                    $ret = (int)($emp['retrasos'] ?? 0);
                ?>
                <tr>
                    <td class="fw-semibold"><?= e($emp['nombre'].' '.$emp['apellidos']) ?></td>
                    <td><?= (int)$emp['total_fichajes'] ?></td>
                    <td><?= $ret>0 ? '<span class="badge bg-danger">'.$ret.'</span>' : '<span class="text-muted">0</span>' ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar <?= $pct>20?'bg-danger':($pct>10?'bg-warning':'') ?>" style="width:<?= min($pct,100) ?>%;<?= $pct<=10?'background:var(--teal);':'' ?>"></div>
                            </div>
                            <small class="text-muted"><?= $pct ?>%</small>
                        </div>
                    </td>
                    <td>
                        <?php if($ret==0): ?><span class="badge" style="background:#d1fae5;color:#065f46;">Puntual</span>
                        <?php elseif($ret<=2): ?><span class="badge bg-warning text-dark">Ocasional</span>
                        <?php else: ?><span class="badge bg-danger">Frecuente</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($retrasosEmpleado)): ?><tr><td colspan="5" class="empty py-4">Sin datos disponibles</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family="'Inter',sans-serif";Chart.defaults.color='#64748B';
const DL=<?= json_encode($diasLabels) ?>;
const DR=<?= json_encode(array_map('intval',$diasRet)) ?>;
const DT=<?= json_encode(array_map('intval',$diasTotal)) ?>;
const EN=<?= json_encode($empNombres) ?>;
const ER=<?= json_encode(array_map('intval',$empRetrasos)) ?>;
const TEAL='#1A649C',NAVY='#1E3A8A';

new Chart(document.getElementById('chartRetrasosDia'),{type:'bar',data:{labels:DL,datasets:[
  {label:'Total fichajes',data:DT,backgroundColor:'rgba(15,23,42,.3)',borderRadius:4},
  {label:'Retrasos',data:DR,backgroundColor:'rgba(239,68,68,.85)',borderRadius:4},
]},options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true}}}});

new Chart(document.getElementById('chartRetrasosEmp'),{type:'bar',data:{labels:EN,datasets:[{label:'Retrasos',data:ER,backgroundColor:ER.map(v=>v===0?TEAL:v<=2?'#f59e0b':'#ef4444'),borderRadius:6}]},options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true}}}});
</script>
