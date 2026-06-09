<?php
$saludoHora = (int)date('H');
$saludo = $saludoHora < 12 ? 'Buenos días' : ($saludoHora < 20 ? 'Buenas tardes' : 'Buenas noches');
$enCurso = (bool)$abierto;
?>

<!-- HEADER -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title"><?= $saludo ?>, <?= e($_SESSION['emp_nombre']) ?></h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= ucfirst(fechaCorta(date('Y-m-d'))) ?> · <?= e(ajuste('nombre_empresa','Mi Empresa')) ?></p>
    </div>
    <a href="<?= url('fichar') ?>" class="btn <?= $enCurso ? 'btn-danger-solid' : 'btn-success' ?>">
        <i class="bi bi-<?= $enCurso ? 'box-arrow-right' : 'box-arrow-in-right' ?>"></i>
        <?= $enCurso ? 'Fichar salida' : 'Fichar entrada' ?>
    </a>
</div>

<!-- estado banner -->
<div class="card" style="margin-bottom:1.25rem;border-left:4px solid <?= $enCurso ? 'var(--green)' : 'var(--border-strong)' ?>;">
    <div style="display:flex;align-items:center;gap:.8rem;padding:.9rem 1.25rem;">
        <i class="bi bi-<?= $enCurso ? 'broadcast' : 'moon-stars' ?>" style="font-size:1.4rem;color:<?= $enCurso ? 'var(--green)' : 'var(--muted)' ?>;"></i>
        <div style="flex:1;">
            <?php if ($enCurso): ?>
                <span style="font-weight:700;color:var(--text);">Estás trabajando</span>
                <span class="muted"> · entrada a las <?= hhmm($abierto['hora_entrada']) ?> h</span>
            <?php else: ?>
                <span style="font-weight:700;color:var(--text);">Fuera de turno</span>
                <span class="muted"> · aún no has fichado la entrada hoy</span>
            <?php endif; ?>
        </div>
        <a href="<?= url('fichar') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-clock-history"></i> Ir a Fichar</a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-icon"><i class="bi bi-clock-history"></i></div><div class="kpi-value"><?= horasLegibles($horasSemana) ?></div><div class="kpi-label">Horas esta semana</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-navy"><div class="kpi-icon"><i class="bi bi-calendar3"></i></div><div class="kpi-value"><?= horasLegibles($horasMes) ?></div><div class="kpi-label">Horas este mes</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-icon"><i class="bi bi-calendar2-check"></i></div><div class="kpi-value"><?= (int)$totalFichajes ?></div><div class="kpi-label">Fichajes (45 días)</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div><div class="kpi-value"><?= (int)$incPend ?></div><div class="kpi-label">Incidencias pendientes</div></div></div>
</div>

<!-- MIS FICHAJES -->
<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <span><i class="bi bi-calendar2-check teal"></i> Mis fichajes</span>
        <div class="dropdown">
            <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-funnel"></i> Filtros <span class="filtro-dot" data-fp-dot style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end fp-filtros" data-fp-target="#miFichBody">
                <label class="lbl">Buscar</label>
                <input class="inp mb-2" data-fp-key="search" placeholder="Fecha…">
                <label class="lbl">Estado</label>
                <select class="inp mb-3" data-fp-key="estado" data-fp-mode="eq"><option value="">Todos</option><option value="abierto">Abierto</option><option value="cerrado">Cerrado</option><option value="incidencia">Incidencia</option><option value="validado">Validado</option></select>
                <button type="button" class="btn btn-ghost w-100" style="justify-content:center;" data-fp-clear><i class="bi bi-x-circle"></i> Limpiar</button>
            </div>
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Estado</th><th style="text-align:right;">Acciones</th></tr></thead>
        <tbody id="miFichBody">
        <?php foreach ($fichajes as $f): ?>
        <tr data-row data-search="<?= e(strtolower(fechaLarga($f['fecha']).' '.$f['fecha'])) ?>" data-estado="<?= e($f['estado']) ?>">
            <td style="font-weight:800;color:#1E3A8A;white-space:nowrap;"><?= fechaLarga($f['fecha']) ?></td>
            <td><?= hhmm($f['hora_entrada']) ?></td>
            <td><?= hhmm($f['hora_salida']) ?></td>
            <td style="font-weight:700;"><?= horasLegibles($f['total_horas']) ?></td>
            <td><?= estadoFichajeBadge($f['estado']) ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <button class="btn btn-ghost btn-sm" title="Ver incidencias" onclick="verInc(<?= $f['id'] ?>)"><i class="bi bi-eye"></i></button>
                <button class="btn btn-ghost btn-sm" style="color:#c2410c;" title="Añadir incidencia" onclick="addInc(<?= $f['id'] ?>,'<?= e(fechaLarga($f['fecha'])) ?>')"><i class="bi bi-exclamation-triangle"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($fichajes)): ?><tr><td colspan="6" class="empty">No tienes fichajes en los últimos 45 días.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- MODAL: Nueva incidencia -->
<div class="modal-bg" id="incMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-exclamation-triangle"></i> Nueva incidencia</span><button class="modal-x" onclick="document.getElementById('incMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveInc(event)">
            <div style="background:#F8FAFC;border:1px solid var(--border);border-radius:9px;padding:.6rem .9rem;font-size:.85rem;">
                <span class="muted">Fichaje del</span> <strong id="incFecha" style="color:var(--text);"></strong>
            </div>
            <input type="hidden" id="incFichaje">
            <div><label class="lbl">Describe la incidencia *</label><textarea id="incMsg" class="inp" rows="3" style="resize:none;" required placeholder="Ej: Olvidé fichar la salida, salí a las 17:30"></textarea></div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-send"></i> Enviar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('incMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>

<!-- MODAL: Ver incidencias -->
<div class="modal-bg" id="verMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-eye"></i> Incidencias del fichaje</span><button class="modal-x" onclick="document.getElementById('verMod').classList.remove('open')">✕</button></div>
        <div id="verLista" style="padding:1.2rem;max-height:60vh;overflow-y:auto;"></div>
    </div>
</div>

<script>
function addInc(id, fecha){
    document.getElementById('incFichaje').value = id;
    document.getElementById('incFecha').textContent = fecha;
    document.getElementById('incMsg').value = '';
    document.getElementById('incMod').classList.add('open');
}
async function saveInc(ev){ ev.preventDefault();
    const r = await api('/incidencias/crear', { id_fichaje: document.getElementById('incFichaje').value, mensaje: document.getElementById('incMsg').value });
    if (r.success){ toast('Incidencia enviada ✓'); setTimeout(()=>location.reload(), 800); }
    else toast(r.message||'Error','err');
}
async function verInc(id){
    const cont = document.getElementById('verLista');
    cont.innerHTML = '<p class="muted" style="text-align:center;margin:1rem 0;">Cargando…</p>';
    document.getElementById('verMod').classList.add('open');
    try{
        const res = await fetch(BASE + '/incidencias/porFichaje/' + id);
        const d = await res.json();
        if (!d.success || !d.items.length){
            cont.innerHTML = '<p class="empty" style="padding:1.5rem;">Este fichaje no tiene incidencias.</p>';
            return;
        }
        cont.innerHTML = d.items.map(i => {
            const resuelta = i.estado === 'resuelta';
            const col = resuelta ? '#16a34a' : '#b45309';
            const bg  = resuelta ? '#F0FDF4' : '#FFFBEB';
            return `<div style="border:1px solid var(--border);border-left:3px solid ${col};border-radius:10px;padding:.8rem 1rem;margin-bottom:.7rem;background:${bg};">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
                    <span class="badge" style="background:${col};color:#fff;">${resuelta?'Resuelta':'Pendiente'}</span>
                    <span class="muted" style="font-size:.74rem;">${(i.created_at||'').substring(0,16).replace('T',' ')}</span>
                </div>
                <div style="font-size:.86rem;color:var(--text);font-weight:600;margin-bottom:.3rem;">${(i.mensaje||'').replace(/</g,'&lt;')}</div>
                ${i.respuesta ? `<div style="font-size:.82rem;color:var(--mid);background:#fff;border:1px solid var(--border);border-radius:7px;padding:.5rem .7rem;"><i class="bi bi-reply"></i> ${(i.respuesta||'').replace(/</g,'&lt;')}</div>` : ''}
            </div>`;
        }).join('');
    }catch{ cont.innerHTML = '<p class="empty">Error al cargar.</p>'; }
}
</script>
