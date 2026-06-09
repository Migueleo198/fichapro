<?php
$enCurso    = (bool)$abierto;
$enDescanso = (bool)$descanso;
?>

<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">Fichar</h1>
    <p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= ucfirst(fechaCorta(date('Y-m-d'))) ?> · <?= e($_SESSION['emp_nombre']) ?></p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start;" id="ficharGrid">

    <!-- Clock panel -->
    <div class="card" style="text-align:center;padding:2rem 1.5rem;">
        <div id="clock" style="font-size:3rem;font-weight:900;color:#1E3A8A;letter-spacing:1px;font-variant-numeric:tabular-nums;">--:--:--</div>

        <div id="statusPill" style="display:inline-block;margin:.5rem 0 1.5rem;padding:.4rem 1.1rem;border-radius:99px;font-weight:800;font-size:.85rem;
             background:<?= $enDescanso ? '#fff7ed' : ($enCurso ? '#dcfce7' : '#f1f5f9') ?>;
             color:<?= $enDescanso ? '#c2410c' : ($enCurso ? '#166534' : '#64748b') ?>;">
            <?= $enDescanso ? '⏸ En descanso' : ($enCurso ? '🟢 Trabajando' : '⚪ Fuera de turno') ?>
        </div>

        <?php if ($enCurso): ?>
        <div style="font-size:.82rem;color:#64748B;font-weight:700;margin-bottom:1.25rem;">
            Entrada: <?= hhmm($abierto['hora_entrada']) ?> h
        </div>
        <?php endif; ?>

        <div style="display:flex;flex-direction:column;gap:.6rem;max-width:280px;margin:0 auto;">
            <?php if (!$enCurso): ?>
                <button onclick="entrada()" class="btn btn-success" style="justify-content:center;padding:.9rem;font-size:1rem;">
                    <i class="bi bi-box-arrow-in-right"></i> Fichar entrada
                </button>
            <?php else: ?>
                <?php if (!$enDescanso): ?>
                <div style="display:flex;gap:.5rem;">
                    <select id="motivoDescanso" class="inp" style="flex:1;">
                        <option>Descanso</option><option>Comida</option><option>Pausa personal</option>
                        <option>Fumar</option><option>Gestión laboral</option><option>Otro</option>
                    </select>
                    <button onclick="toggleDescanso()" class="btn btn-ghost" style="white-space:nowrap;"><i class="bi bi-pause-circle"></i> Pausa</button>
                </div>
                <?php else: ?>
                <button onclick="toggleDescanso()" class="btn btn-navy" style="justify-content:center;padding:.8rem;">
                    <i class="bi bi-play-circle"></i> Reanudar (<?= e($descanso['motivo']) ?>)
                </button>
                <?php endif; ?>
                <button onclick="salida()" class="btn btn-danger-solid" style="justify-content:center;padding:.9rem;font-size:1rem;" <?= $enDescanso ? 'disabled title="Reanuda antes de salir"' : '' ?>>
                    <i class="bi bi-box-arrow-right"></i> Fichar salida
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Today summary -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="stat">
                <div class="ic"><i class="bi bi-calendar-day"></i></div>
                <div class="num"><?= count($hoy) ?></div>
                <div class="lbl">Fichajes hoy</div>
            </div>
            <div class="stat">
                <div class="ic"><i class="bi bi-clock-history"></i></div>
                <div class="num" style="font-size:1.4rem;"><?= horasLegibles($horasMes) ?></div>
                <div class="lbl">Acumulado del mes</div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><i class="bi bi-list-ul teal"></i> Fichajes de hoy</div>
            <?php if (empty($hoy)): ?>
                <p class="empty">Aún no has fichado hoy.</p>
            <?php else: ?>
            <table class="tbl">
                <thead><tr><th>Entrada</th><th>Salida</th><th>Total</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($hoy as $f): ?>
                <tr>
                    <td style="font-weight:800;color:#1E3A8A;"><?= hhmm($f['hora_entrada']) ?></td>
                    <td><?= hhmm($f['hora_salida']) ?></td>
                    <td><?= horasLegibles($f['total_horas']) ?></td>
                    <td><?= estadoFichajeBadge($f['estado']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <?php if ($enCurso && !empty($descansos)): ?>
        <div class="card">
            <div class="card-head"><i class="bi bi-cup-hot teal"></i> Descansos de este turno</div>
            <table class="tbl">
                <thead><tr><th>Motivo</th><th>Inicio</th><th>Fin</th></tr></thead>
                <tbody>
                <?php foreach ($descansos as $d): ?>
                <tr><td style="font-weight:700;"><?= e($d['motivo']) ?></td><td><?= hhmm($d['hora_inicio']) ?></td>
                    <td><?= $d['hora_fin'] ? hhmm($d['hora_fin']) : '<span class="teal">en curso</span>' ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// live clock
function tick(){
    const n=new Date();
    document.getElementById('clock').textContent =
        String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0')+':'+String(n.getSeconds()).padStart(2,'0');
}
setInterval(tick,1000); tick();

async function entrada(){
    const r = await api('/fichar/entrada');
    if(r.success){ toast('Entrada registrada ✓'); setTimeout(()=>location.reload(),700); }
    else toast(r.message||'Error','err');
}
async function salida(){
    if(!confirm('¿Fichar la salida y cerrar el turno?')) return;
    const r = await api('/fichar/salida');
    if(r.success){ toast('Salida registrada ✓'); setTimeout(()=>location.reload(),700); }
    else toast(r.message||'Error','err');
}
async function toggleDescanso(){
    const sel = document.getElementById('motivoDescanso');
    const r = await api('/fichar/descanso', { motivo: sel ? sel.value : 'Descanso' });
    if(r.success){ toast(r.estado==='inicio'?'Descanso iniciado':'Descanso finalizado','info'); setTimeout(()=>location.reload(),600); }
    else toast(r.message||'Error','err');
}
</script>
