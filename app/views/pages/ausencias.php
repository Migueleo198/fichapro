<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Ausencias</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= $esAdmin ? 'Bajas y permisos de la plantilla' : 'Tus bajas y permisos' ?></p></div>
    <div style="display:flex;gap:.5rem;">
        <div class="dropdown">
            <button type="button" class="btn btn-ghost" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-funnel"></i> Filtros <span class="filtro-dot" data-fp-dot style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end fp-filtros" data-fp-target="#ausBody">
                <label class="lbl">Buscar</label>
                <input class="inp mb-2" data-fp-key="search" placeholder="<?= $esAdmin ? 'Empleado, tipo…' : 'Tipo…' ?>">
                <label class="lbl">Estado</label>
                <select class="inp mb-3" data-fp-key="estado" data-fp-mode="eq"><option value="">Todos</option><option value="pendiente">Pendiente</option><option value="aprobada">Aprobada</option><option value="rechazada">Rechazada</option></select>
                <button type="button" class="btn btn-ghost w-100" style="justify-content:center;" data-fp-clear><i class="bi bi-x-circle"></i> Limpiar</button>
            </div>
        </div>
        <?php if ($esAdmin): ?><a href="<?= url('ausencias/tipos') ?>" class="btn btn-ghost"><i class="bi bi-tags"></i> Tipos</a><?php endif; ?>
        <?php if (!$esAdmin): ?><button onclick="document.getElementById('aMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Solicitar</button><?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-clipboard2-pulse teal"></i> <?= $esAdmin ? 'Todas las ausencias' : 'Mis ausencias' ?></div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><?php if($esAdmin):?><th>Empleado</th><?php endif;?><th>Tipo</th><th>Desde</th><th>Hasta</th><th>Remunerada</th><th>Estado</th><th></th></tr></thead>
        <tbody id="ausBody">
        <?php foreach ($lista as $a): ?>
        <tr id="a-<?= $a['id'] ?>" data-row
            data-search="<?= e(strtolower(($a['nombre'] ?? '').' '.($a['apellidos'] ?? '').' '.($a['tipo_nombre'] ?? $a['motivo_personalizado'] ?? ''))) ?>"
            data-estado="<?= e($a['estado']) ?>">
            <?php if($esAdmin):?><td style="font-weight:800;color:#1E3A8A;"><?= e($a['nombre'].' '.$a['apellidos']) ?></td><?php endif;?>
            <td style="font-weight:700;"><?= e($a['tipo_nombre'] ?? $a['motivo_personalizado'] ?? '—') ?></td>
            <td><?= fechaLarga($a['fecha_inicio']) ?></td>
            <td><?= $a['fecha_fin'] ? fechaLarga($a['fecha_fin']) : '—' ?></td>
            <td><?= $a['remunerada'] ? badge('Sí','#dcfce7','#166534') : badge('No','#f1f5f9','#94a3b8') ?></td>
            <td><?= estadoSolicitudBadge($a['estado']) ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <?php if ($esAdmin && $a['estado']==='pendiente'): ?>
                    <button class="btn btn-ghost btn-sm" style="color:#16a34a;" onclick="setA(<?= $a['id'] ?>,'aprobada')"><i class="bi bi-check-lg"></i></button>
                    <button class="btn btn-danger btn-sm" onclick="setA(<?= $a['id'] ?>,'rechazada')"><i class="bi bi-x-lg"></i></button>
                <?php elseif (!$esAdmin && $a['estado']==='pendiente'): ?>
                    <button class="btn btn-danger btn-sm" onclick="delA(<?= $a['id'] ?>)"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lista)): ?><tr><td colspan="<?= $esAdmin?7:6 ?>" class="empty">Sin ausencias registradas.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php if (!$esAdmin): ?>
<div class="modal-bg" id="aMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-clipboard2-pulse"></i> Solicitar ausencia</span><button class="modal-x" onclick="document.getElementById('aMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveA(event)">
            <div><label class="lbl">Tipo *</label><select id="a_tipo" class="inp" required>
                <option value="">Selecciona…</option>
                <?php foreach ($tipos as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['nombre']) ?><?= $t['remunerada']?'':' (sin sueldo)' ?></option><?php endforeach; ?>
            </select></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Desde *</label><input id="a_ini" type="date" class="inp" required></div>
                <div><label class="lbl">Hasta</label><input id="a_fin" type="date" class="inp"></div>
            </div>
            <div><label class="lbl">Observaciones</label><textarea id="a_obs" class="inp" rows="2" style="resize:none;"></textarea></div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-send"></i> Enviar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('aMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>
<script>
async function saveA(ev){ ev.preventDefault();
    const r=await api('/ausencias/crear',{id_tipo:a_tipo.value,fecha_inicio:a_ini.value,fecha_fin:a_fin.value,observaciones:a_obs.value,motivo_personalizado:'',horas:''});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
async function delA(id){ if(!confirm('¿Cancelar esta solicitud?'))return; const r=await api('/ausencias/eliminar/'+id); if(r.success){document.getElementById('a-'+id).remove();toast('Cancelada','info');} }
</script>
<?php else: ?>
<script>
async function setA(id,est){ const r=await api('/ausencias/estado/'+id+'/'+est); if(r.success){toast(est==='aprobada'?'Aprobada ✓':'Rechazada','info');setTimeout(()=>location.reload(),500);} }
</script>
<?php endif; ?>
