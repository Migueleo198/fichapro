<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Incidencias</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= $esAdmin ? 'Reclamaciones sobre fichajes' : 'Reporta un problema con un fichaje' ?></p></div>
    <?php if (!$esAdmin): ?><button onclick="document.getElementById('iMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Nueva incidencia</button><?php endif; ?>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-exclamation-triangle teal"></i> <?= $esAdmin ? 'Todas las incidencias' : 'Mis incidencias' ?></div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><?php if($esAdmin):?><th>Empleado</th><?php endif;?><th>Fichaje</th><th>Mensaje</th><th>Respuesta</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lista as $i): ?>
        <tr id="i-<?= $i['id'] ?>">
            <?php if($esAdmin):?><td style="font-weight:800;color:#0A2E4E;"><?= e($i['nombre'].' '.$i['apellidos']) ?></td><?php endif;?>
            <td style="white-space:nowrap;"><?= fechaLarga($i['fecha']) ?> · <?= hhmm($i['hora_entrada']) ?></td>
            <td class="mid" style="font-size:.82rem;max-width:240px;"><?= e($i['mensaje']) ?></td>
            <td class="mid" style="font-size:.82rem;"><?= e($i['respuesta']) ?: '—' ?></td>
            <td><?= $i['estado']==='resuelta' ? badge('Resuelta','#dcfce7','#166534') : badge('Pendiente','#fef9c3','#854d0e') ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <?php if ($esAdmin && $i['estado']==='pendiente'): ?>
                <button class="btn btn-teal btn-sm" onclick="openResp(<?= $i['id'] ?>)"><i class="bi bi-reply"></i> Responder</button>
                <?php endif; ?>
                <?php if ($esAdmin): ?><button class="btn btn-danger btn-sm" onclick="delI(<?= $i['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lista)): ?><tr><td colspan="<?= $esAdmin?6:5 ?>" class="empty">Sin incidencias.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php if (!$esAdmin): ?>
<div class="modal-bg" id="iMod">
    <div class="modal">
        <div class="modal-head"><span><i class="bi bi-exclamation-triangle"></i> Nueva incidencia</span><button class="modal-x" onclick="document.getElementById('iMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveI(event)">
            <div><label class="lbl">Fichaje afectado *</label><select id="i_fich" class="inp" required>
                <option value="">Selecciona…</option>
                <?php foreach ($fichajes as $f): ?><option value="<?= $f['id'] ?>"><?= fechaLarga($f['fecha']) ?> · <?= hhmm($f['hora_entrada']) ?>–<?= hhmm($f['hora_salida']) ?></option><?php endforeach; ?>
            </select></div>
            <div><label class="lbl">Describe la incidencia *</label><textarea id="i_msg" class="inp" rows="3" style="resize:none;" required placeholder="Ej: Olvidé fichar la salida ayer, salí a las 17:30"></textarea></div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-send"></i> Enviar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('iMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>
<script>
async function saveI(ev){ ev.preventDefault();
    const r=await api('/incidencias/crear',{id_fichaje:i_fich.value,mensaje:i_msg.value});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
</script>
<?php else: ?>
<script>
let respId=null;
function openResp(id){ respId=id; const t=prompt('Respuesta a la incidencia:'); if(t!==null) sendResp(t); }
async function sendResp(text){ const r=await api('/incidencias/responder/'+respId,{respuesta:text}); if(r.success){toast('Respondida ✓');setTimeout(()=>location.reload(),500);} }
async function delI(id){ if(!confirm('¿Eliminar incidencia?'))return; const r=await api('/incidencias/eliminar/'+id); if(r.success){document.getElementById('i-'+id).remove();toast('Eliminada','info');} }
</script>
<?php endif; ?>
