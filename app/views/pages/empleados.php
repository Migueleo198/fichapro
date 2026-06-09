<?php $yo = currentEmpId(); ?>

<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title">Empleados</h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Altas, roles y acceso al sistema</p>
    </div>
    <button onclick="openEmp()" class="btn btn-teal"><i class="bi bi-person-plus"></i> Nuevo empleado</button>
</div>

<div class="card">
    <div class="card-head" style="justify-content:space-between;">
        <span><i class="bi bi-people teal"></i> Plantilla</span>
        <span class="muted" style="font-size:.75rem;font-weight:700;"><?= count($empleados) ?> en total</span>
    </div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Empleado</th><th>DNI</th><th>Contacto</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($empleados as $e): ?>
        <tr id="emp-<?= $e['id'] ?>">
            <td>
                <div style="display:flex;align-items:center;gap:.7rem;">
                    <div style="width:2.1rem;height:2.1rem;border-radius:50%;background:<?= $e['rol']==='admin'?'#0A2E4E':'#3EC6C1' ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.8rem;flex-shrink:0;"><?= strtoupper(mb_substr($e['nombre'],0,1)) ?></div>
                    <div>
                        <div style="font-weight:800;color:#0A2E4E;"><?= e($e['nombre'].' '.$e['apellidos']) ?><?= $e['id']==$yo?' <span class="muted" style="font-size:.7rem;">(tú)</span>':'' ?></div>
                        <div class="muted" style="font-size:.76rem;">@<?= e($e['usuario']) ?></div>
                    </div>
                </div>
            </td>
            <td class="mid" style="font-family:monospace;font-size:.78rem;"><?= e($e['dni']) ?></td>
            <td class="mid" style="font-size:.8rem;"><?= e($e['email']) ?><?= $e['telefono']?'<br><span class="muted">'.e($e['telefono']).'</span>':'' ?></td>
            <td><?= rolBadge($e['rol']) ?></td>
            <td><?= $e['activo'] ? badge('Activo','#dcfce7','#166534') : badge('Inactivo','#f1f5f9','#94a3b8') ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <button class="btn btn-ghost btn-sm" onclick='openEmp(<?= json_encode($e, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="bi bi-pencil"></i></button>
                <?php if ($e['id']!=$yo): ?>
                <button class="btn <?= $e['activo']?'btn-danger':'btn-ghost' ?> btn-sm" onclick="toggleEmp(<?= $e['id'] ?>,<?= $e['activo']?0:1 ?>)">
                    <i class="bi bi-<?= $e['activo']?'person-dash':'person-check' ?>"></i>
                </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- modal -->
<div class="modal-bg" id="empModal">
    <div class="modal">
        <div class="modal-head"><span id="empTitle"><i class="bi bi-person-plus"></i> Nuevo empleado</span><button class="modal-x" onclick="closeEmp()">✕</button></div>
        <form id="empForm" style="padding:1.4rem;display:flex;flex-direction:column;gap:.85rem;" onsubmit="saveEmp(event)">
            <input type="hidden" id="e_id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Nombre *</label><input id="e_nombre" class="inp" required></div>
                <div><label class="lbl">Apellidos</label><input id="e_apellidos" class="inp"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Usuario *</label><input id="e_usuario" class="inp" required></div>
                <div><label class="lbl">DNI *</label><input id="e_dni" class="inp" required></div>
            </div>
            <div><label class="lbl">Email *</label><input id="e_email" type="email" class="inp" required></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Teléfono</label><input id="e_telefono" class="inp"></div>
                <div><label class="lbl">Fecha nacimiento</label><input id="e_fnac" type="date" class="inp"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div><label class="lbl">Rol *</label><select id="e_rol" class="inp"><option value="trabajador">Trabajador</option><option value="admin">Administrador</option></select></div>
                <div><label class="lbl" id="pwLabel">Contraseña *</label><input id="e_password" type="password" class="inp"><div id="pwHint" class="muted" style="font-size:.7rem;margin-top:.2rem;display:none;">Vacío = mantener actual</div></div>
            </div>
            <div id="activoRow" style="display:none;"><label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-weight:700;color:#0A2E4E;font-size:.85rem;"><input type="checkbox" id="e_activo" style="width:1.1rem;height:1.1rem;accent-color:#3EC6C1;"> Cuenta activa</label></div>
            <div id="empErr" style="display:none;padding:.6rem .8rem;border-radius:9px;background:#fff5f5;border:1px solid #fecaca;color:#dc2626;font-size:.82rem;font-weight:700;"></div>
            <div style="display:flex;gap:.5rem;margin-top:.25rem;">
                <button type="submit" class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-check-circle"></i> <span id="empBtn">Crear</span></button>
                <button type="button" class="btn btn-ghost" onclick="closeEmp()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
let editId=null;
const M=document.getElementById('empModal');
function openEmp(e=null){
    editId = e ? e.id : null;
    document.getElementById('empForm').reset();
    document.getElementById('empErr').style.display='none';
    document.getElementById('empTitle').innerHTML = '<i class="bi bi-'+(e?'pencil':'person-plus')+'"></i> '+(e?'Editar':'Nuevo')+' empleado';
    document.getElementById('empBtn').textContent = e?'Guardar':'Crear';
    document.getElementById('pwLabel').textContent = e?'Nueva contraseña':'Contraseña *';
    document.getElementById('pwHint').style.display = e?'block':'none';
    document.getElementById('e_password').required = !e;
    document.getElementById('activoRow').style.display = e?'block':'none';
    if(e){
        e_id.value=e.id; e_nombre.value=e.nombre; e_apellidos.value=e.apellidos||'';
        e_usuario.value=e.usuario; e_dni.value=e.dni; e_email.value=e.email;
        e_telefono.value=e.telefono||''; e_fnac.value=e.fecha_nacimiento||'';
        e_rol.value=e.rol; e_activo.checked=e.activo==1;
    }
    M.classList.add('open');
}
function closeEmp(){ M.classList.remove('open'); }
async function saveEmp(ev){
    ev.preventDefault();
    const body={ nombre:e_nombre.value, apellidos:e_apellidos.value, usuario:e_usuario.value,
        dni:e_dni.value, email:e_email.value, telefono:e_telefono.value, fecha_nacimiento:e_fnac.value,
        rol:e_rol.value, password:e_password.value, activo:e_activo.checked?1:0 };
    const r = await api(editId?('/empleados/actualizar/'+editId):'/empleados/crear', body);
    if(r.success){ location.reload(); }
    else { const x=document.getElementById('empErr'); x.textContent=r.message; x.style.display='block'; }
}
async function toggleEmp(id,activo){
    if(!confirm(activo?'¿Reactivar este empleado?':'¿Desactivar este empleado?')) return;
    const r = await api('/empleados/estado/'+id,{activo}); if(r.success) location.reload(); else toast(r.message,'err');
}
</script>
