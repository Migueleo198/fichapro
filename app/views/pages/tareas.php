<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Tareas</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= $esAdmin ? 'Tareas registradas por la plantilla' : 'Registra el trabajo de tu jornada' ?></p></div>
    <?php if (!$esAdmin): ?><button onclick="document.getElementById('tkMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Nueva tarea</button><?php endif; ?>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-list-task teal"></i> <?= $esAdmin ? 'Todas las tareas' : 'Mis tareas' ?></div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Fecha</th><?php if($esAdmin):?><th>Empleado</th><?php endif;?><th>Tarea</th><th>Tipo</th><th>Horario</th><th>Horas</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lista as $t): ?>
        <tr id="tk-<?= $t['id'] ?>">
            <td style="white-space:nowrap;font-weight:700;"><?= fechaLarga($t['fecha']) ?></td>
            <?php if($esAdmin):?><td><?= e($t['nombre'].' '.$t['apellidos']) ?></td><?php endif;?>
            <td style="font-weight:800;color:#0A2E4E;"><?= e($t['titulo']) ?><?php if($t['descripcion']):?><br><span class="muted" style="font-size:.76rem;font-weight:400;"><?= e($t['descripcion']) ?></span><?php endif;?></td>
            <td><?= $t['tipo_nombre'] ? badge($t['tipo_nombre'],'#e0f9f8','#0A2E4E') : '—' ?></td>
            <td class="mid" style="font-size:.8rem;"><?= hhmm($t['hora_inicio']) ?> – <?= hhmm($t['hora_fin']) ?></td>
            <td><?= horasLegibles($t['total_horas']) ?></td>
            <td><?php
                $b = match($t['estado']){'finalizada'=>badge('Finalizada','#dcfce7','#166534'),'en_progreso'=>badge('En progreso','#e0f2fe','#0369a1'),default=>badge('Pendiente','#fef9c3','#854d0e')};
                echo $b; ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <?php if (!$esAdmin): ?>
                <button class="btn btn-ghost btn-sm" onclick="cycleT(<?= $t['id'] ?>,'<?= $t['estado'] ?>')" title="Cambiar estado"><i class="bi bi-arrow-repeat"></i></button>
                <button class="btn btn-danger btn-sm" onclick="delTk(<?= $t['id'] ?>)"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lista)): ?><tr><td colspan="<?= $esAdmin?7:7 ?>" class="empty">Sin tareas registradas.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php if (!$esAdmin): ?>
<div class="modal-bg" id="tkMod">
    <div class="modal">
        <div class="modal-head"><span><i class="bi bi-list-task"></i> Nueva tarea</span><button class="modal-x" onclick="document.getElementById('tkMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveTk(event)">
            <div><label class="lbl">Título *</label><input id="tk_tit" class="inp" required></div>
            <div><label class="lbl">Descripción</label><textarea id="tk_desc" class="inp" rows="2" style="resize:none;"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Tipo</label><select id="tk_tipo" class="inp"><option value="">—</option><?php foreach($tipos as $tp):?><option value="<?= $tp['id'] ?>"><?= e($tp['nombre']) ?></option><?php endforeach;?></select></div>
                <div><label class="lbl">Estado</label><select id="tk_est" class="inp"><option value="pendiente">Pendiente</option><option value="en_progreso">En progreso</option><option value="finalizada">Finalizada</option></select></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Hora inicio</label><input id="tk_ini" type="time" class="inp"></div>
                <div><label class="lbl">Hora fin</label><input id="tk_fin" type="time" class="inp"></div>
            </div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-check-circle"></i> Guardar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('tkMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>
<script>
async function saveTk(ev){ ev.preventDefault();
    const r=await api('/tareas/crear',{titulo:tk_tit.value,descripcion:tk_desc.value,id_tipo:tk_tipo.value,estado:tk_est.value,hora_inicio:tk_ini.value,hora_fin:tk_fin.value});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
const order=['pendiente','en_progreso','finalizada'];
async function cycleT(id,cur){ const next=order[(order.indexOf(cur)+1)%order.length]; const r=await api('/tareas/estado/'+id+'/'+next); if(r.success){toast('Estado: '+next,'info');setTimeout(()=>location.reload(),400);} }
async function delTk(id){ if(!confirm('¿Eliminar tarea?'))return; const r=await api('/tareas/eliminar/'+id); if(r.success){document.getElementById('tk-'+id).remove();toast('Eliminada','info');} }
</script>
<?php endif; ?>
