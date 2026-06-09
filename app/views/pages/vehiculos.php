<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Vehículos</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;">Matrículas registradas por empleado</p></div>
    <button onclick="document.getElementById('vhMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Añadir vehículo</button>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-car-front teal"></i> Vehículos</div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Empleado</th><th>Matrícula</th><th>Marca</th><th>Modelo</th><th>Color</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lista as $v): ?>
        <tr id="vh-<?= $v['id'] ?>">
            <td style="font-weight:800;color:#1E3A8A;"><?= e($v['nombre'].' '.$v['apellidos']) ?></td>
            <td><span style="font-family:monospace;font-weight:800;background:#1E3A8A;color:#fff;padding:2px 8px;border-radius:6px;letter-spacing:1px;"><?= e($v['matricula']) ?></span></td>
            <td><?= e($v['marca']) ?: '—' ?></td>
            <td><?= e($v['modelo']) ?: '—' ?></td>
            <td><?= e($v['color']) ?: '—' ?></td>
            <td style="text-align:right;"><button class="btn btn-danger btn-sm" onclick="delVh(<?= $v['id'] ?>)"><i class="bi bi-trash"></i></button></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lista)): ?><tr><td colspan="6" class="empty">Sin vehículos registrados.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="modal-bg" id="vhMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-car-front"></i> Añadir vehículo</span><button class="modal-x" onclick="document.getElementById('vhMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveVh(event)">
            <div><label class="lbl">Empleado *</label><select id="vh_emp" class="inp" required><option value="">Selecciona…</option>
                <?php foreach($empleados as $e):?><option value="<?= $e['id'] ?>"><?= e($e['nombre'].' '.$e['apellidos']) ?></option><?php endforeach;?></select></div>
            <div><label class="lbl">Matrícula *</label><input id="vh_mat" class="inp" required style="text-transform:uppercase;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Marca</label><input id="vh_marca" class="inp"></div>
                <div><label class="lbl">Modelo</label><input id="vh_modelo" class="inp"></div>
                <div><label class="lbl">Color</label><input id="vh_color" class="inp"></div>
            </div>
            <div style="display:flex;gap:.5rem;"><button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-check-circle"></i> Guardar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('vhMod').classList.remove('open')">Cancelar</button></div>
        </form>
    </div>
</div>

<script>
async function saveVh(ev){ ev.preventDefault();
    const r=await api('/vehiculos/crear',{id_empleado:vh_emp.value,matricula:vh_mat.value,marca:vh_marca.value,modelo:vh_modelo.value,color:vh_color.value});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
async function delVh(id){ if(!confirm('¿Eliminar vehículo?'))return; const r=await api('/vehiculos/eliminar/'+id); if(r.success){document.getElementById('vh-'+id).remove();toast('Eliminado','info');} }
</script>
