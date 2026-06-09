<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Vacaciones</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= $esAdmin ? 'Solicitudes de toda la plantilla' : 'Tus solicitudes de vacaciones' ?></p></div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button type="button" class="btn btn-ghost" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-funnel"></i> Filtros <span class="filtro-dot" data-fp-dot style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end fp-filtros" data-fp-target="#vacBody">
                <label class="lbl">Buscar</label>
                <input class="inp mb-2" data-fp-key="search" placeholder="<?= $esAdmin ? 'Empleado, comentario…' : 'Comentario…' ?>">
                <label class="lbl">Estado</label>
                <select class="inp mb-3" data-fp-key="estado" data-fp-mode="eq"><option value="">Todos</option><option value="pendiente">Pendiente</option><option value="aprobada">Aprobada</option><option value="rechazada">Rechazada</option></select>
                <button type="button" class="btn btn-ghost w-100" style="justify-content:center;" data-fp-clear><i class="bi bi-x-circle"></i> Limpiar</button>
            </div>
        </div>
        <?php if (!$esAdmin): ?><button onclick="document.getElementById('vMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Solicitar</button><?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-airplane teal"></i> <?= $esAdmin ? 'Todas las solicitudes' : 'Mis solicitudes' ?></div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><?php if($esAdmin):?><th>Empleado</th><?php endif;?><th>Desde</th><th>Hasta</th><th>Días</th><th>Comentario</th><th>Estado</th><th></th></tr></thead>
        <tbody id="vacBody">
        <?php foreach ($lista as $v): ?>
        <tr id="v-<?= $v['id'] ?>" data-row
            data-search="<?= e(strtolower(($v['nombre'] ?? '').' '.($v['apellidos'] ?? '').' '.($v['comentario'] ?? ''))) ?>"
            data-estado="<?= e($v['estado']) ?>">
            <?php if($esAdmin):?><td style="font-weight:800;color:#1E3A8A;"><?= e($v['nombre'].' '.$v['apellidos']) ?></td><?php endif;?>
            <td><?= fechaLarga($v['fecha_inicio']) ?></td>
            <td><?= fechaLarga($v['fecha_fin']) ?></td>
            <td style="font-weight:800;"><?= (int)$v['dias'] ?></td>
            <td class="mid" style="font-size:.82rem;"><?= e($v['comentario']) ?: '—' ?></td>
            <td><?= estadoSolicitudBadge($v['estado']) ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <?php if ($esAdmin && $v['estado']==='pendiente'): ?>
                    <button class="btn btn-ghost btn-sm" style="color:#16a34a;" onclick="setV(<?= $v['id'] ?>,'aprobada')"><i class="bi bi-check-lg"></i></button>
                    <button class="btn btn-danger btn-sm" onclick="setV(<?= $v['id'] ?>,'rechazada')"><i class="bi bi-x-lg"></i></button>
                <?php elseif (!$esAdmin && $v['estado']==='pendiente'): ?>
                    <button class="btn btn-danger btn-sm" onclick="delV(<?= $v['id'] ?>)"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lista)): ?><tr><td colspan="<?= $esAdmin?7:6 ?>" class="empty">Sin solicitudes.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php if (!$esAdmin): ?>
<div class="modal-bg" id="vMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-airplane"></i> Solicitar vacaciones</span><button class="modal-x" onclick="document.getElementById('vMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveV(event)">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Desde *</label><input id="v_ini" type="date" class="inp" required></div>
                <div><label class="lbl">Hasta *</label><input id="v_fin" type="date" class="inp" required></div>
            </div>
            <div><label class="lbl">Comentario</label><textarea id="v_com" class="inp" rows="2" style="resize:none;"></textarea></div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-send"></i> Enviar solicitud</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('vMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>
<script>
async function saveV(ev){ ev.preventDefault();
    const r=await api('/vacaciones/crear',{fecha_inicio:v_ini.value,fecha_fin:v_fin.value,comentario:v_com.value});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
async function delV(id){ if(!confirm('¿Cancelar esta solicitud?'))return; const r=await api('/vacaciones/eliminar/'+id); if(r.success){document.getElementById('v-'+id).remove();toast('Cancelada','info');} }
</script>
<?php else: ?>
<script>
async function setV(id,est){ const r=await api('/vacaciones/estado/'+id+'/'+est); if(r.success){toast(est==='aprobada'?'Aprobada ✓':'Rechazada','info');setTimeout(()=>location.reload(),500);} }
</script>
<?php endif; ?>
