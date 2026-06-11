<?php
$mesesLabels   = array_column($fichajesMeses, 'mes_label');
$mesesFichajes = array_column($fichajesMeses, 'total_fichajes');
$mesesHoras    = array_column($fichajesMeses, 'total_horas');
$mesesRetrasos = array_column($fichajesMeses, 'total_retrasos');
$diasLabels    = array_column($diaSemana, 'dia');
$diasTotales   = array_column($diaSemana, 'total');
$topNombres    = array_map(fn($e) => $e['nombre'].' '.$e['apellidos'], $topEmpleados);
$topHoras      = array_column($topEmpleados, 'total_horas');
$r = $resumen;
?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title"><i class="bi bi-bar-chart-fill" style="color:var(--teal);"></i> Estadísticas</h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Panel de análisis y métricas de la empresa</p>
    </div>
    <span class="badge" style="background:var(--navy);color:#fff;font-size:.82rem;padding:.5rem 1rem;">
        <i class="bi bi-calendar3 me-1"></i><?= date('F Y') ?>
    </span>
</div>

<!-- NAV TABS -->
<ul class="stats-nav d-flex mb-4">
    <li><a class="nav-link active" href="<?= url('estadisticas/resumen') ?>"><i class="bi bi-grid-fill me-1"></i>Resumen</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/fichajes') ?>"><i class="bi bi-clock-history me-1"></i>Fichajes</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/horas') ?>"><i class="bi bi-hourglass-split me-1"></i>Horas</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/retrasos') ?>"><i class="bi bi-alarm me-1"></i>Retrasos</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/actividad') ?>"><i class="bi bi-activity me-1"></i>Actividad</a></li>
</ul>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-navy"><div class="kpi-icon"><i class="bi bi-people-fill"></i></div><div class="kpi-value"><?= $r['empleados_activos'] ?? 0 ?></div><div class="kpi-label">Empleados activos</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-icon"><i class="bi bi-clock-history"></i></div><div class="kpi-value"><?= $r['fichajes_hoy'] ?? 0 ?></div><div class="kpi-label">Fichajes hoy</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div><div class="kpi-value"><?= round($r['horas_hoy'] ?? 0, 1) ?>h</div><div class="kpi-label">Horas hoy</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-exclamation-triangle-fill"></i></div><div class="kpi-value"><?= $r['retrasos_hoy'] ?? 0 ?></div><div class="kpi-label">Retrasos hoy</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-icon"><i class="bi bi-calendar-check"></i></div><div class="kpi-value"><?= $r['fichajes_mes'] ?? 0 ?></div><div class="kpi-label">Fichajes este mes</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-purple"><div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div><div class="kpi-value"><?= round($r['horas_mes'] ?? 0) ?>h</div><div class="kpi-label">Horas este mes</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-red"><div class="kpi-icon"><i class="bi bi-alarm-fill"></i></div><div class="kpi-value"><?= $r['retrasos_mes'] ?? 0 ?></div><div class="kpi-label">Retrasos este mes</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-pink"><div class="kpi-icon"><i class="bi bi-plus-circle-fill"></i></div><div class="kpi-value"><?= round($r['horas_extra_mes'] ?? 0, 1) ?>h</div><div class="kpi-label">Horas extra mes</div></div></div>
</div>

<!-- CHARTS ROW 1 -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-graph-up me-2" style="color:var(--teal);"></i>Evolución últimos 6 meses</h6></div>
            <div class="chart-card-body"><canvas id="chartEvolucion" height="90"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-calendar-week me-2" style="color:var(--teal);"></i>Fichajes por día</h6></div>
            <div class="chart-card-body"><canvas id="chartDiaSemana" height="170"></canvas></div>
        </div>
    </div>
</div>

<!-- CHARTS ROW 2 -->
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-trophy-fill me-2 text-warning"></i>Top empleados (horas mes)</h6></div>
            <div class="chart-card-body"><canvas id="chartTopEmpleados" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="chart-card h-100">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-bar-chart-steps me-2 text-info"></i>Fichajes vs Retrasos (6 meses)</h6></div>
            <div class="chart-card-body"><canvas id="chartComparativa" height="140"></canvas></div>
        </div>
    </div>
</div>

<!-- RANKING TABLE -->
<div class="chart-card mb-4">
    <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-people me-2" style="color:var(--teal);"></i>Ranking empleados – mes actual</h6></div>
    <div class="chart-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover stats-table mb-0">
                <thead><tr><th>#</th><th>Empleado</th><th>H. Ordinarias</th><th>H. Extra</th><th>Total</th><th>Progreso</th></tr></thead>
                <tbody>
                <?php
                $allHoras = array_column($topEmpleados, 'total_horas');
                $maxH = !empty($allHoras) ? (float)max($allHoras) : 1;
                $maxH = $maxH > 0 ? $maxH : 1;
                ?>
                <?php foreach ($topEmpleados as $i => $emp): ?>
                <?php $pct = (float)($emp['total_horas'] ?? 0) / $maxH * 100; ?>
                <tr>
                    <td>
                        <?php if($i===0): ?><span class="badge bg-warning text-dark"><i class="bi bi-trophy-fill"></i> 1</span>
                        <?php elseif($i===1): ?><span class="badge bg-secondary">2</span>
                        <?php elseif($i===2): ?><span class="badge" style="background:#cd7f32;color:#fff;">3</span>
                        <?php else: ?><span class="text-muted"><?= $i+1 ?></span><?php endif; ?>
                    </td>
                    <td class="fw-semibold"><?= e($emp['nombre'].' '.$emp['apellidos']) ?></td>
                    <td><?= round($emp['horas_ordinarias'], 1) ?>h</td>
                    <td><?= $emp['horas_extra'] > 0 ? '<span class="badge bg-warning text-dark">'.round($emp['horas_extra'],1).'h</span>' : '<span class="text-muted">—</span>' ?></td>
                    <td class="fw-bold"><?= round($emp['total_horas'], 1) ?>h</td>
                    <td style="width:140px;">
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar" style="width:<?= round($pct) ?>%;background:var(--teal);"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($topEmpleados)): ?><tr><td colspan="6" class="empty py-4">Sin datos disponibles</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ML=<?= json_encode($mesesLabels) ?>;
const MF=<?= json_encode(array_map('intval',$mesesFichajes)) ?>;
const MH=<?= json_encode(array_map('floatval',$mesesHoras)) ?>;
const MR=<?= json_encode(array_map('intval',$mesesRetrasos)) ?>;
const DL=<?= json_encode($diasLabels) ?>;
const DT=<?= json_encode(array_map('intval',$diasTotales)) ?>;
const TN=<?= json_encode($topNombres) ?>;
const TH=<?= json_encode(array_map('floatval',$topHoras)) ?>;
const TEAL='#1A649C', NAVY='#1E3A8A', ORANGE='#f59e0b', GREEN='#10b981';

Chart.defaults.font.family="'Inter',sans-serif";Chart.defaults.color='#64748B';

new Chart(document.getElementById('chartEvolucion'),{type:'line',data:{labels:ML,datasets:[
  {label:'Fichajes',data:MF,borderColor:NAVY,backgroundColor:'rgba(15,23,42,.08)',tension:.4,fill:true,pointRadius:4,borderWidth:2},
  {label:'Horas',data:MH,borderColor:TEAL,backgroundColor:'rgba(26,100,156,.08)',tension:.4,fill:true,yAxisID:'yH',pointRadius:4,borderWidth:2},
  {label:'Retrasos',data:MR,borderColor:ORANGE,backgroundColor:'rgba(245,158,11,.06)',tension:.4,fill:true,pointRadius:4,borderWidth:2},
]},options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true},yH:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false}}}}});

new Chart(document.getElementById('chartDiaSemana'),{type:'bar',data:{labels:DL,datasets:[{label:'Fichajes',data:DT,backgroundColor:TEAL,borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});

new Chart(document.getElementById('chartTopEmpleados'),{type:'bar',data:{labels:TN,datasets:[{label:'Total horas',data:TH,backgroundColor:NAVY,borderRadius:6}]},options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true}}}});

new Chart(document.getElementById('chartComparativa'),{type:'bar',data:{labels:ML,datasets:[
  {label:'Fichajes',data:MF,backgroundColor:TEAL,borderRadius:4},
  {label:'Retrasos',data:MR,backgroundColor:ORANGE,borderRadius:4},
]},options:{responsive:true,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true}}}});
</script>
