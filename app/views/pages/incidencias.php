<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Incidencias</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;"><?= $esAdmin ? 'Reclamaciones sobre fichajes' : 'Reporta un problema con un fichaje' ?></p></div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button type="button" class="btn btn-ghost" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                <i class="bi bi-funnel"></i> Filtros <span class="filtro-dot" data-fp-dot style="display:none;"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end fp-filtros" data-fp-target="#incBody">
                <label class="lbl">Buscar</label>
                <input class="inp mb-2" data-fp-key="search" placeholder="<?= $esAdmin ? 'Empleado, mensaje…' : 'Mensaje…' ?>">
                <label class="lbl">Estado</label>
                <select class="inp mb-3" data-fp-key="estado" data-fp-mode="eq"><option value="">Todos</option><option value="pendiente">Pendiente</option><option value="resuelta">Resuelta</option></select>
                <button type="button" class="btn btn-ghost w-100" style="justify-content:center;" data-fp-clear><i class="bi bi-x-circle"></i> Limpiar</button>
            </div>
        </div>
        <?php if (!$esAdmin): ?><button onclick="document.getElementById('iMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Nueva incidencia</button><?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-exclamation-triangle teal"></i> <?= $esAdmin ? 'Todas las incidencias' : 'Mis incidencias' ?></div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><?php if($esAdmin):?><th>Empleado</th><?php endif;?><th>Fichaje</th><th>Mensaje</th><th>Respuesta</th><th>Estado</th><th></th></tr></thead>
        <tbody id="incBody">
        <?php foreach ($lista as $i): ?>
        <tr id="i-<?= $i['id'] ?>" data-row
            data-search="<?= e(strtolower(($i['nombre'] ?? '').' '.($i['apellidos'] ?? '').' '.($i['mensaje'] ?? ''))) ?>"
            data-estado="<?= e($i['estado']) ?>">
            <?php if($esAdmin):?><td style="font-weight:800;color:#1E3A8A;"><?= e($i['nombre'].' '.$i['apellidos']) ?></td><?php endif;?>
            <td style="white-space:nowrap;"><?= fechaLarga($i['fecha']) ?> · <?= hhmm($i['hora_entrada']) ?></td>
            <td class="mid" style="font-size:.82rem;max-width:240px;"><?= e($i['mensaje']) ?></td>
            <td class="mid" style="font-size:.82rem;"><?= e($i['respuesta']) ?: '—' ?></td>
            <td><?= $i['estado']==='resuelta' ? badge('Resuelta','#dcfce7','#166534') : badge('Pendiente','#fef9c3','#854d0e') ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <?php if ($esAdmin && $i['estado']==='pendiente'): ?>
                <button class="btn btn-teal btn-sm" onclick="openResp(this,<?= $i['id'] ?>)"
                        data-msg="<?= e($i['mensaje']) ?>"
                        data-meta="<?= e($i['nombre'].' '.$i['apellidos'].' · '.fechaLarga($i['fecha']).' · '.hhmm($i['hora_entrada'])) ?>"><i class="bi bi-reply"></i> Responder</button>
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
    <div class="modal-box">
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
<!-- MODAL: Responder incidencia -->
<div class="modal-bg" id="respMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-reply"></i> Responder incidencia</span><button class="modal-x" onclick="document.getElementById('respMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="sendResp(event)">
            <div style="background:#F8FAFC;border:1px solid var(--border);border-radius:9px;padding:.75rem .9rem;">
                <div class="muted" id="respMeta" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.3rem;"></div>
                <div id="respMsg" style="color:var(--text);font-weight:600;font-size:.88rem;"></div>
            </div>
            <div><label class="lbl">Tu respuesta *</label><textarea id="respTxt" class="inp" rows="3" style="resize:none;" required placeholder="Escribe la respuesta para el empleado…"></textarea></div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-send"></i> Enviar respuesta</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('respMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>
<script>
let respId=null;
function openResp(btn,id){
    respId=id;
    document.getElementById('respMsg').textContent  = btn.dataset.msg  || '';
    document.getElementById('respMeta').textContent = btn.dataset.meta || '';
    document.getElementById('respTxt').value = '';
    document.getElementById('respMod').classList.add('open');
    setTimeout(()=>document.getElementById('respTxt').focus(), 50);
}
async function sendResp(ev){ ev.preventDefault();
    const r=await api('/incidencias/responder/'+respId,{respuesta:document.getElementById('respTxt').value});
    if(r.success){toast('Respondida ✓');setTimeout(()=>location.reload(),600);} else toast(r.message||'Error','err');
}
async function delI(id){ if(!confirm('¿Eliminar incidencia?'))return; const r=await api('/incidencias/eliminar/'+id); if(r.success){document.getElementById('i-'+id).remove();toast('Eliminada','info');} }
</script>
<?php endif; ?>
