<?php
$ra         = $resumenActividad;
$av         = $ausenciasVacas;
$diasLabels = array_column($actividadDiaria, 'fecha');
$diasEmp    = array_column($actividadDiaria, 'empleados_activos');
$diasFich   = array_column($actividadDiaria, 'total_fichajes');
$diasHoras  = array_column($actividadDiaria, 'horas_totales');
$horasMap   = [];
foreach ($porHora as $h) { $horasMap[(int)$h['hora']] = (int)$h['entradas']; }
$horasLabels = [];
$horasData   = [];
for ($i = 6; $i <= 21; $i++) {
    $horasLabels[] = sprintf('%02d:00', $i);
    $horasData[]   = $horasMap[$i] ?? 0;
}
$maxHoraEntradas = max($horasData ?: [1]) ?: 1;
?>

<!-- PAGE HEADER -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title"><i class="bi bi-activity" style="color:var(--teal);"></i> Estadísticas – Actividad</h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Presencia, ausencias y distribución de actividad</p>
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
    <li><a class="nav-link" href="<?= url('estadisticas/retrasos') ?>"><i class="bi bi-alarm me-1"></i>Retrasos</a></li>
    <li><a class="nav-link active" href="<?= url('estadisticas/actividad') ?>"><i class="bi bi-activity me-1"></i>Actividad</a></li>
</ul>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-navy"><div class="kpi-icon"><i class="bi bi-person-check-fill"></i></div><div class="kpi-value"><?= $ra['empleados_registrados'] ?? 0 ?></div><div class="kpi-label">Con actividad</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-icon"><i class="bi bi-calendar2-check-fill"></i></div><div class="kpi-value"><?= $ra['dias_con_actividad'] ?? 0 ?></div><div class="kpi-label">Días con actividad</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-calendar-x-fill"></i></div><div class="kpi-value"><?= $av['total_ausencias'] ?? 0 ?></div><div class="kpi-label">Ausencias</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-umbrella-fill"></i></div><div class="kpi-value"><?= $av['vacaciones_aprobadas'] ?? 0 ?></div><div class="kpi-label">Vacaciones aprobadas</div></div></div>
</div>

<!-- CHART ACTIVIDAD DIARIA -->
<div class="chart-card mb-4">
    <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-people-fill me-2" style="color:var(--teal);"></i>Empleados activos por día</h6></div>
    <div class="chart-card-body"><canvas id="chartActDiaria" height="75"></canvas></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="chart-card h-100">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-clock-fill me-2 text-warning"></i>Distribución de entradas por hora</h6></div>
            <div class="chart-card-body"><canvas id="chartHoraEntrada" height="110"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="chart-card h-100">
            <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-pie-chart-fill me-2" style="color:var(--teal);"></i>Ausencias y vacaciones</h6></div>
            <div class="chart-card-body d-flex flex-column justify-content-center gap-3 py-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="activity-icon-box" style="background:#fee2e2;color:#b91c1c;"><i class="bi bi-calendar-x-fill fs-4"></i></div>
                    <div><div class="fw-bold fs-4"><?= $av['total_ausencias'] ?? 0 ?></div><div class="text-muted small">Ausencias registradas</div></div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="activity-icon-box" style="background:#d1fae5;color:#065f46;"><i class="bi bi-umbrella-fill fs-4"></i></div>
                    <div><div class="fw-bold fs-4"><?= $av['vacaciones_aprobadas'] ?? 0 ?></div><div class="text-muted small">Vacaciones aprobadas</div></div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="activity-icon-box" style="background:#fef9c3;color:#854d0e;"><i class="bi bi-hourglass-split fs-4"></i></div>
                    <div><div class="fw-bold fs-4"><?= $av['vacaciones_pendientes'] ?? 0 ?></div><div class="text-muted small">Vacaciones pendientes</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLE PRESENCIA -->
<div class="chart-card mb-4">
    <div class="chart-card-header"><h6 class="mb-0 fw-bold" style="color:var(--navy);"><i class="bi bi-table me-2" style="color:var(--teal);"></i>Presencia y actividad por empleado</h6></div>
    <div class="chart-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover stats-table mb-0">
                <thead><tr><th>Empleado</th><th>Días presentes</th><th>Horas totales</th><th>Retrasos</th><th>Índice actividad</th></tr></thead>
                <tbody>
                <?php
                $maxDias = max(array_column($presencia, 'dias_presentes') ?: [1]);
                foreach ($presencia as $emp):
                    $pct = $maxDias > 0 ? ($emp['dias_presentes'] / $maxDias * 100) : 0;
                ?>
                <tr>
                    <td class="fw-semibold"><?= e($emp['nombre'].' '.$emp['apellidos']) ?></td>
                    <td><span class="badge" style="background:var(--navy);color:#fff;"><?= $emp['dias_presentes'] ?> días</span></td>
                    <td><?= round($emp['horas_totales'],1) ?>h</td>
                    <td><?= $emp['retrasos']>0 ? '<span class="badge bg-warning text-dark">'.$emp['retrasos'].'</span>' : '<span style="color:#059669;font-weight:700;"><i class="bi bi-check-circle-fill"></i> 0</span>' ?></td>
                    <td style="width:160px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;">
                                <div class="progress-bar" style="width:<?= round($pct) ?>%;background:var(--teal);"></div>
                            </div>
                            <small class="text-muted"><?= round($pct) ?>%</small>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($presencia)): ?><tr><td colspan="5" class="empty py-4">Sin datos disponibles</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family="'Inter',sans-serif";Chart.defaults.color='#64748B';
const DL=<?= json_encode($diasLabels) ?>;
const DE=<?= json_encode(array_map('intval',$diasEmp)) ?>;
const DF=<?= json_encode(array_map('intval',$diasFich)) ?>;
const HL=<?= json_encode($horasLabels) ?>;
const HD=<?= json_encode($horasData) ?>;
const maxH=Math.max(<?= $maxHoraEntradas ?>,1);
const TEAL='#2563EB',NAVY='#1E3A8A';

new Chart(document.getElementById('chartActDiaria'),{type:'line',data:{labels:DL,datasets:[
  {label:'Empleados activos',data:DE,borderColor:NAVY,backgroundColor:'rgba(15,23,42,.08)',tension:.4,fill:true,pointRadius:3,borderWidth:2},
  {label:'Fichajes',data:DF,borderColor:TEAL,backgroundColor:'rgba(37,99,235,.08)',tension:.4,fill:true,pointRadius:3,yAxisID:'yF',borderWidth:2},
]},options:{responsive:true,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true,title:{display:true,text:'Empleados'}},yF:{beginAtZero:true,position:'right',title:{display:true,text:'Fichajes'},grid:{drawOnChartArea:false}}}}});

new Chart(document.getElementById('chartHoraEntrada'),{type:'bar',data:{labels:HL,datasets:[{label:'Entradas',data:HD,backgroundColor:HD.map(v=>{ const i=v/maxH; return `rgba(37,99,235,${0.2+i*0.8})`; }),borderRadius:4}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,title:{display:true,text:'Nº entradas'}}}}});
</script>
