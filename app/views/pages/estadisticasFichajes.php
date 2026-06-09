<?php
$rf         = $resumenFichajes;
$diasLabels = array_column($diarios, 'fecha');
$diasTotal  = array_column($diarios, 'total_fichajes');
$diasRet    = array_column($diarios, 'retrasos');
$diasComp   = array_column($diarios, 'completados');
$estadoLabels = [];
$estadoData   = [];
foreach ($porEstado as $e) { $estadoLabels[] = ucfirst($e['estado']); $estadoData[] = (int)$e['total']; }
?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title"><i class="bi bi-clock-history" style="color:var(--teal);"></i> Estadísticas – Fichajes</h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Análisis de registros de entrada y salida</p>
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
    <li><a class="nav-link active" href="<?= url('estadisticas/fichajes') ?>"><i class="bi bi-clock-history me-1"></i>Fichajes</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/horas') ?>"><i class="bi bi-hourglass-split me-1"></i>Horas</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/retrasos') ?>"><i class="bi bi-alarm me-1"></i>Retrasos</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/actividad') ?>"><i class="bi bi-activity me-1"></i>Actividad</a></li>
</ul>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-navy"><div class="kpi-icon"><i class="bi bi-clock-history"></i></div><div class="kpi-value"><?= $rf['total'] ?? 0 ?></div><div class="kpi-label">Total fichajes</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div><div class="kpi-value"><?= $rf['normales'] ?? 0 ?></div><div class="kpi-label">Completados</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-exclamation-triangle-fill"></i></div><div class="kpi-value"><?= $rf['retrasos'] ?? 0 ?></div><div class="kpi-label">Con retraso</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-icon"><i class="bi bi-stopwatch-fill"></i></div><div class="kpi-value"><?= round($rf['duracion_media_horas'] ?? 0, 1) ?>h</div><div class="kpi-label">Duración media</div></div></div>
</div>

<!-- CHARTS -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-bar-chart me-2" style="color:var(--teal);"></i>Fichajes diarios</h6></div>
            <div class="chart-card-body"><canvas id="chartDiarios" height="90"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-pie-chart me-2" style="color:var(--teal);"></i>Por estado</h6></div>
            <div class="chart-card-body d-flex justify-content-center"><canvas id="chartEstado" height="180" style="max-width:220px;"></canvas></div>
        </div>
    </div>
</div>

<!-- TABLE POR EMPLEADO -->
<div class="chart-card mb-4">
    <div class="chart-card-header">
        <h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-people me-2" style="color:var(--teal);"></i>Fichajes por empleado</h6>
        <span class="badge" style="background:var(--bg);color:var(--mid);"><?= e($desde) ?> → <?= e($hasta) ?></span>
    </div>
    <div class="chart-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover stats-table mb-0">
                <thead><tr><th>Empleado</th><th>Total</th><th>Retrasos</th><th>Horas</th><th>% Retraso</th></tr></thead>
                <tbody>
                <?php foreach ($porEmpleado as $emp): ?>
                <?php $pct = $emp['total_fichajes'] > 0 ? round($emp['retrasos']/$emp['total_fichajes']*100,1) : 0; ?>
                <tr>
                    <td class="fw-semibold"><?= e($emp['nombre'].' '.$emp['apellidos']) ?></td>
                    <td><span class="badge" style="background:var(--navy);color:#fff;"><?= $emp['total_fichajes'] ?></span></td>
                    <td><?= $emp['retrasos']>0 ? '<span class="badge bg-warning text-dark">'.$emp['retrasos'].'</span>' : '<span class="text-muted">0</span>' ?></td>
                    <td><?= round($emp['total_horas'],1) ?>h</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar <?= $pct>20?'bg-danger':($pct>10?'bg-warning':'') ?>" style="width:<?= min($pct,100) ?>%;<?= $pct<=10?'background:var(--teal);':'' ?>"></div>
                            </div>
                            <small class="text-muted"><?= $pct ?>%</small>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($porEmpleado)): ?><tr><td colspan="5" class="empty py-4">Sin datos para el período</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family="'Inter',sans-serif";Chart.defaults.color='#64748B';
const DL=<?= json_encode($diasLabels) ?>;
const DT=<?= json_encode(array_map('intval',$diasTotal)) ?>;
const DR=<?= json_encode(array_map('intval',$diasRet)) ?>;
const EL=<?= json_encode($estadoLabels) ?>;
const ED=<?= json_encode($estadoData) ?>;
const TEAL='#2563EB',NAVY='#1E3A8A';

new Chart(document.getElementById('chartDiarios'),{type:'bar',data:{labels:DL,datasets:[
  {label:'Total',data:DT,backgroundColor:'rgba(15,23,42,.7)',borderRadius:4},
  {label:'Retrasos',data:DR,backgroundColor:'rgba(245,158,11,.85)',borderRadius:4},
]},options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true}}}});

new Chart(document.getElementById('chartEstado'),{type:'doughnut',data:{labels:EL,datasets:[{data:ED,backgroundColor:[TEAL,NAVY,'#f59e0b','#ef4444','#8b5cf6'],hoverOffset:6,borderWidth:2,borderColor:'#fff'}]},options:{responsive:true,cutout:'65%',plugins:{legend:{position:'bottom'}}}});
</script>
