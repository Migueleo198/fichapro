<?php
$rh         = $resumenHoras;
$diasLabels = array_column($horasDiarias, 'fecha');
$diasOrd    = array_column($horasDiarias, 'ordinarias');
$diasExtra  = array_column($horasDiarias, 'extra');
$diasTotal  = array_column($horasDiarias, 'total');
$empNombres = array_map(fn($e)=>$e['nombre'].' '.$e['apellidos'], $horasEmpleado);
$empTotal   = array_column($horasEmpleado, 'total');
$empOrd     = array_column($horasEmpleado, 'ordinarias');
$empExtra   = array_column($horasEmpleado, 'extra');
?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title"><i class="bi bi-hourglass-split" style="color:var(--teal);"></i> Estadísticas – Horas</h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Distribución de horas ordinarias y extras</p>
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
    <li><a class="nav-link active" href="<?= url('estadisticas/horas') ?>"><i class="bi bi-hourglass-split me-1"></i>Horas</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/retrasos') ?>"><i class="bi bi-alarm me-1"></i>Retrasos</a></li>
    <li><a class="nav-link" href="<?= url('estadisticas/actividad') ?>"><i class="bi bi-activity me-1"></i>Actividad</a></li>
</ul>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-navy"><div class="kpi-icon"><i class="bi bi-hourglass-bottom"></i></div><div class="kpi-value"><?= round($rh['total_horas'] ?? 0,1) ?>h</div><div class="kpi-label">Total horas</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-icon"><i class="bi bi-briefcase-fill"></i></div><div class="kpi-value"><?= round($rh['total_ordinarias'] ?? 0,1) ?>h</div><div class="kpi-label">Horas ordinarias</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-plus-lg"></i></div><div class="kpi-value"><?= round($rh['total_extra'] ?? 0,1) ?>h</div><div class="kpi-label">Horas extra</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-purple"><div class="kpi-icon"><i class="bi bi-calendar2-day"></i></div><div class="kpi-value"><?= round($rh['media_diaria'] ?? 0,1) ?>h</div><div class="kpi-label">Media diaria</div></div></div>
</div>

<!-- CHART DIARIO -->
<div class="chart-card mb-4">
    <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-graph-up me-2" style="color:var(--teal);"></i>Horas trabajadas por día</h6></div>
    <div class="chart-card-body"><canvas id="chartHorasDiarias" height="80"></canvas></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="chart-card h-100">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-bar-chart-steps me-2" style="color:var(--teal);"></i>Horas por empleado</h6></div>
            <div class="chart-card-body"><canvas id="chartHorasEmp" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-pie-chart me-2 text-warning"></i>Ordinarias vs Extra</h6></div>
            <div class="chart-card-body d-flex justify-content-center align-items-center"><canvas id="chartDonutHoras" height="200" style="max-width:200px;"></canvas></div>
        </div>
    </div>
</div>

<!-- TABLE -->
<div class="chart-card mb-4">
    <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-table me-2" style="color:var(--teal);"></i>Detalle por empleado</h6></div>
    <div class="chart-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover stats-table mb-0">
                <thead><tr><th>Empleado</th><th>Días</th><th>H. Ordinarias</th><th>H. Extra</th><th>Total</th><th>% Extra</th></tr></thead>
                <tbody>
                <?php foreach ($horasEmpleado as $emp): ?>
                <?php $pctExtra = $emp['total'] > 0 ? round($emp['extra']/$emp['total']*100,1) : 0; ?>
                <tr>
                    <td class="fw-semibold"><?= e($emp['nombre'].' '.$emp['apellidos']) ?></td>
                    <td><?= $emp['dias_trabajados'] ?></td>
                    <td><?= round($emp['ordinarias'],1) ?>h</td>
                    <td><?= $emp['extra']>0 ? '<span class="badge bg-warning text-dark">'.round($emp['extra'],1).'h</span>' : '<span class="text-muted">0h</span>' ?></td>
                    <td class="fw-bold"><?= round($emp['total'],1) ?>h</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar bg-warning" style="width:<?= min($pctExtra,100) ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $pctExtra ?>%</small>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($horasEmpleado)): ?><tr><td colspan="6" class="empty py-4">Sin datos disponibles</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family="'Inter',sans-serif";Chart.defaults.color='#64748B';
const DL=<?= json_encode($diasLabels) ?>;
const DO=<?= json_encode(array_map('floatval',$diasOrd)) ?>;
const DE=<?= json_encode(array_map('floatval',$diasExtra)) ?>;
const DT=<?= json_encode(array_map('floatval',$diasTotal)) ?>;
const EN=<?= json_encode($empNombres) ?>;
const ET=<?= json_encode(array_map('floatval',$empTotal)) ?>;
const EO=<?= json_encode(array_map('floatval',$empOrd)) ?>;
const EX=<?= json_encode(array_map('floatval',$empExtra)) ?>;
const totOrd=<?= round($rh['total_ordinarias'] ?? 0,1) ?>;
const totExt=<?= round($rh['total_extra'] ?? 0,1) ?>;
const TEAL='#2563EB',NAVY='#1E3A8A',ORANGE='#f59e0b';

new Chart(document.getElementById('chartHorasDiarias'),{type:'bar',data:{labels:DL,datasets:[
  {label:'Ordinarias',data:DO,backgroundColor:'rgba(15,23,42,.75)',borderRadius:3,stack:'h'},
  {label:'Extra',data:DE,backgroundColor:ORANGE,borderRadius:3,stack:'h'},
  {label:'Total',data:DT,type:'line',borderColor:TEAL,backgroundColor:'transparent',tension:.4,pointRadius:3,yAxisID:'yT'},
]},options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top'}},scales:{y:{stacked:true,beginAtZero:true},yT:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false}}}}});

new Chart(document.getElementById('chartHorasEmp'),{type:'bar',data:{labels:EN,datasets:[
  {label:'Ordinarias',data:EO,backgroundColor:NAVY,borderRadius:4,stack:'s'},
  {label:'Extra',data:EX,backgroundColor:ORANGE,borderRadius:4,stack:'s'},
]},options:{indexAxis:'y',responsive:true,plugins:{legend:{position:'top'}},scales:{x:{stacked:true,beginAtZero:true}}}});

new Chart(document.getElementById('chartDonutHoras'),{type:'doughnut',data:{labels:['Ordinarias','Extra'],datasets:[{data:[totOrd,totExt],backgroundColor:[NAVY,ORANGE],borderWidth:2,borderColor:'#fff',hoverOffset:6}]},options:{cutout:'65%',responsive:true,plugins:{legend:{position:'bottom'}}}});
</script>
