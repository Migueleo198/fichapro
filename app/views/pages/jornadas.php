<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div><h1 class="page-title">Jornadas</h1><p class="muted" style="font-weight:600;margin:.3rem 0 0;">Horario contratado por empleado</p></div>
    <button onclick="document.getElementById('jMod').classList.add('open')" class="btn btn-teal"><i class="bi bi-plus-lg"></i> Asignar jornada</button>
</div>

<div class="card">
    <div class="card-head"><i class="bi bi-calendar-range teal"></i> Jornadas asignadas</div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Empleado</th><th>Horas/día</th><th>Horas/semana</th><th>Desde</th><th>Hasta</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($jornadas as $j): ?>
        <tr id="j-<?= $j['id'] ?>">
            <td style="font-weight:800;color:#1E3A8A;"><?= e($j['nombre'].' '.$j['apellidos']) ?></td>
            <td><?= rtrim(rtrim(number_format($j['horas_dia'],2),'0'),'.') ?> h</td>
            <td><?= rtrim(rtrim(number_format($j['horas_semana'],2),'0'),'.') ?> h</td>
            <td><?= fechaLarga($j['fecha_inicio']) ?></td>
            <td><?= $j['fecha_fin'] ? fechaLarga($j['fecha_fin']) : '<span class="teal">Vigente</span>' ?></td>
            <td style="text-align:right;"><button class="btn btn-danger btn-sm" onclick="delJ(<?= $j['id'] ?>)"><i class="bi bi-trash"></i></button></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($jornadas)): ?><tr><td colspan="6" class="empty">Sin jornadas asignadas.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="modal-bg" id="jMod">
    <div class="modal-box">
        <div class="modal-head"><span><i class="bi bi-calendar-range"></i> Asignar jornada</span><button class="modal-x" onclick="document.getElementById('jMod').classList.remove('open')">✕</button></div>
        <form style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveJ(event)">
            <div><label class="lbl">Empleado *</label><select id="j_emp" class="inp" required>
                <option value="">Selecciona…</option>
                <?php foreach ($empleados as $e): ?><option value="<?= $e['id'] ?>"><?= e($e['nombre'].' '.$e['apellidos']) ?></option><?php endforeach; ?>
            </select></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Horas/día *</label><input id="j_dia" type="number" step="0.25" min="0" value="7.5" class="inp" required></div>
                <div><label class="lbl">Horas/semana *</label><input id="j_sem" type="number" step="0.25" min="0" value="37.5" class="inp" required></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Desde</label><input id="j_ini" type="date" class="inp"></div>
                <div><label class="lbl">Hasta</label><input id="j_fin" type="date" class="inp"></div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:.25rem;">
                <button class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-check-circle"></i> Guardar</button>
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('jMod').classList.remove('open')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
async function saveJ(ev){ ev.preventDefault();
    const r=await api('/jornadas/crear',{id_empleado:j_emp.value,horas_dia:j_dia.value,horas_semana:j_sem.value,fecha_inicio:j_ini.value,fecha_fin:j_fin.value});
    if(r.success) location.reload(); else toast(r.message||'Error','err');
}
async function delJ(id){ if(!confirm('¿Eliminar jornada?'))return; const r=await api('/jornadas/eliminar/'+id); if(r.success){document.getElementById('j-'+id).remove();toast('Eliminada','info');} }
</script>
