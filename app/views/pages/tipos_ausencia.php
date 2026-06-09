<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Tipos de ausencia</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;">Catálogo de motivos de baja y permiso</p></div>
    <div style="display:flex;gap:.5rem;">
        <a href="<?= url('ausencias') ?>" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Volver</a>
        <button onclick="document.getElementById('tMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Nuevo tipo</button>
    </div>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-tags teal"></i> Catálogo</div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Nombre</th><th>Categoría</th><th>Remunerada</th><th>Días est.</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tipos as $t): ?>
        <tr id="t-<?= $t['id'] ?>">
            <td style="font-weight:800;color:#1E3A8A;"><?= e($t['nombre']) ?><?php if($t['descripcion']):?><br><span class="muted" style="font-size:.76rem;font-weight:400;"><?= e($t['descripcion']) ?></span><?php endif;?></td>
            <td><?= badge(ucfirst($t['tipo']),'#EFF6FF','#1E3A8A') ?></td>
            <td><?= $t['remunerada'] ? badge('Sí','#dcfce7','#166534') : badge('No','#f1f5f9','#94a3b8') ?></td>
            <td><?= (int)$t['dias_estimados'] ?></td>
            <td><?= $t['activo'] ? badge('Activo','#dcfce7','#166534') : badge('Inactivo','#f1f5f9','#94a3b8') ?></td>
            <td style="text-align:right;"><?php if($t['activo']):?><button class="btn btn-danger btn-sm" onclick="delT(<?= $t['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif;?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="modal-bg" id="tMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-tags"></i> Nuevo tipo</span><button class="modal-x" onclick="document.getElementById('tMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveT(event)">
            <div><label class="lbl">Nombre *</label><input id="t_nombre" class="inp" required></div>
            <div><label class="lbl">Descripción</label><input id="t_desc" class="inp"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Categoría</label><select id="t_tipo" class="inp"><option value="permiso">Permiso</option><option value="baja">Baja</option><option value="personal">Personal</option><option value="otro">Otro</option></select></div>
                <div><label class="lbl">Remunerada</label><select id="t_rem" class="inp"><option value="1">Sí</option><option value="0">No</option></select></div>
                <div><label class="lbl">Días est.</label><input id="t_dias" type="number" min="0" value="0" class="inp"></div>
            </div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-check-circle"></i> Guardar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('tMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>

<script>
async function saveT(ev){ ev.preventDefault();
    const r=await api('/ausencias/crearTipo',{nombre:t_nombre.value,descripcion:t_desc.value,tipo:t_tipo.value,remunerada:t_rem.value,dias_estimados:t_dias.value});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
async function delT(id){ if(!confirm('¿Desactivar este tipo?'))return; const r=await api('/ausencias/eliminarTipo/'+id); if(r.success){toast('Desactivado','info');setTimeout(()=>location.reload(),400);} }
</script>
