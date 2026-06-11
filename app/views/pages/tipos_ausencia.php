<?php
// Separar catálogo en remunerados / no remunerados
$tRem = $tNo = [];
foreach ($tipos as $t) { if ((int)$t['remunerada'] === 1) $tRem[] = $t; else $tNo[] = $t; }

$renderTipo = function(array $t) {
    $remunerada = (int)$t['remunerada'] === 1;
    ob_start(); ?>
    <tr id="t-<?= $t['id'] ?>">
        <td style="font-weight:800;color:#1E3A8A;"><?= e($t['nombre']) ?><?php if($t['descripcion']):?><br><span class="muted" style="font-size:.76rem;font-weight:400;"><?= e($t['descripcion']) ?></span><?php endif;?></td>
        <td><?= badge(ucfirst($t['tipo']),'#EFF6FF','#1E3A8A') ?></td>
        <td><?= (int)$t['dias_estimados'] ?></td>
        <td>
            <?php if ($remunerada): ?>
            <form style="display:flex;gap:.3rem;align-items:center;margin:0;" onsubmit="saveLimite(event,<?= $t['id'] ?>)">
                <input id="lim-<?= $t['id'] ?>" type="number" min="0" step="1" value="<?= (int)$t['limite_horas_anual'] ?>"
                       class="inp" style="width:5.5rem;padding:.3rem .5rem;" title="0 = sin límite">
                <span class="muted" style="font-size:.72rem;">h/año</span>
                <button class="btn btn-ghost btn-sm" title="Guardar límite"><i class="bi bi-save"></i></button>
            </form>
            <?php else: ?><span class="muted">—</span><?php endif; ?>
        </td>
        <td><?= $t['activo'] ? badge('Activo','#dcfce7','#166534') : badge('Inactivo','#f1f5f9','#94a3b8') ?></td>
        <td style="text-align:right;"><?php if($t['activo']):?><button class="btn btn-danger btn-sm" onclick="delT(<?= $t['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif;?></td>
    </tr>
    <?php return ob_get_clean();
};
?>
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Tipos de ausencia</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;">Catálogo de motivos de baja y permiso · límite anual de horas remuneradas</p></div>
    <div style="display:flex;gap:.5rem;">
        <a href="<?= url('ausencias') ?>" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Volver</a>
        <button onclick="document.getElementById('tMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Nuevo tipo</button>
    </div>
</div>

<!-- ── REMUNERADOS ─────────────────────────────────────────────── -->
<div class="card" style="border-top:3px solid #16a34a;">
    <div class="card-head" style="display:flex;justify-content:space-between;align-items:center;">
        <span><i class="bi bi-cash-coin" style="color:#16a34a;"></i> Tipos remunerados</span>
        <span class="badge" style="background:#dcfce7;color:#166534;"><?= count($tRem) ?></span>
    </div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Nombre</th><th>Categoría</th><th>Días est.</th><th>Límite anual</th><th>Estado</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($tRem as $t) echo $renderTipo($t); ?>
            <?php if (empty($tRem)): ?><tr><td colspan="6" class="empty">Sin tipos remunerados.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ── NO REMUNERADOS ──────────────────────────────────────────── -->
<div class="card" style="margin-top:1.25rem;border-top:3px solid #94a3b8;">
    <div class="card-head" style="display:flex;justify-content:space-between;align-items:center;">
        <span><i class="bi bi-slash-circle" style="color:#94a3b8;"></i> Tipos no remunerados</span>
        <span class="badge" style="background:#f1f5f9;color:#64748b;"><?= count($tNo) ?></span>
    </div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Nombre</th><th>Categoría</th><th>Días est.</th><th>Límite anual</th><th>Estado</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($tNo as $t) echo $renderTipo($t); ?>
            <?php if (empty($tNo)): ?><tr><td colspan="6" class="empty">Sin tipos no remunerados.</td></tr><?php endif; ?>
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
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Categoría</label><select id="t_tipo" class="inp"><option value="permiso">Permiso</option><option value="baja">Baja</option><option value="personal">Personal</option><option value="otro">Otro</option></select></div>
                <div><label class="lbl">Remunerada</label><select id="t_rem" class="inp" onchange="toggleLimite()"><option value="1">Sí</option><option value="0">No</option></select></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Días est.</label><input id="t_dias" type="number" min="0" value="0" class="inp"></div>
                <div id="t_lim_wrap"><label class="lbl">Límite anual (h) <span class="muted" style="font-weight:400;">0 = sin límite</span></label><input id="t_lim" type="number" min="0" value="0" class="inp"></div>
            </div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-check-circle"></i> Guardar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('tMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>

<script>
function toggleLimite(){ document.getElementById('t_lim_wrap').style.display = t_rem.value==='1' ? '' : 'none'; }
async function saveT(ev){ ev.preventDefault();
    const r=await api('/ausencias/crearTipo',{nombre:t_nombre.value,descripcion:t_desc.value,tipo:t_tipo.value,remunerada:t_rem.value,dias_estimados:t_dias.value,limite_horas_anual:t_lim.value});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
async function saveLimite(ev,id){ ev.preventDefault();
    const v=document.getElementById('lim-'+id).value;
    const r=await api('/ausencias/actualizarLimite/'+id,{horas:v});
    toast(r.success?'Límite actualizado':'No se pudo guardar', r.success?'info':'err');
}
async function delT(id){ if(!confirm('¿Desactivar este tipo?'))return; const r=await api('/ausencias/eliminarTipo/'+id); if(r.success){toast('Desactivado','info');setTimeout(()=>location.reload(),400);} }
</script>
