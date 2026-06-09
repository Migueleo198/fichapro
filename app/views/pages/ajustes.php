<?php
$tab     = $tab     ?? 'cuenta';
$perfil  = $perfil  ?? [];
$config  = $config  ?? [];
$tipos   = $tipos   ?? [];
$esAdmin = $esAdmin ?? false;

// usuarios no-admin sólo pueden ver "cuenta"
if (!$esAdmin && in_array($tab, ['sistema', 'tipos'], true)) $tab = 'cuenta';

$mensajesOk = [
    'perfil'    => 'Perfil actualizado correctamente.',
    'sistema'   => 'Configuración del sistema guardada.',
    'tipo_add'  => 'Tipo de tarea añadido.',
    'tipo_edit' => 'Tipo de tarea actualizado.',
    'tipo_del'  => 'Tipo de tarea eliminado.',
];
$mensajesErr = [
    'perfil'          => 'No se pudo guardar el perfil.',
    'nombre_vacio'    => 'El nombre del tipo no puede estar vacío.',
    'datos_invalidos' => 'Datos inválidos.',
];
?>

<!-- PAGE HEADER -->
<div style="margin-bottom:1.5rem;">
    <h1 class="page-title"><i class="bi bi-gear-fill" style="color:var(--teal);"></i> Configuración</h1>
    <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Gestiona tu cuenta<?= $esAdmin ? ' y los parámetros del sistema' : '' ?></p>
</div>

<?php if ($ok && isset($mensajesOk[$ok])): ?>
<div class="cfg-alert cfg-alert-ok"><i class="bi bi-check-circle-fill"></i> <?= $mensajesOk[$ok] ?></div>
<?php endif; ?>
<?php if ($error && isset($mensajesErr[$error])): ?>
<div class="cfg-alert cfg-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= $mensajesErr[$error] ?></div>
<?php endif; ?>

<!-- TABS -->
<div class="cfg-tabs">
    <a href="<?= url('ajustes?tab=cuenta') ?>"  class="cfg-tab <?= $tab==='cuenta'  ? 'active' : '' ?>"><i class="bi bi-person-fill"></i>Mi cuenta</a>
    <?php if ($esAdmin): ?>
    <a href="<?= url('ajustes?tab=sistema') ?>" class="cfg-tab <?= $tab==='sistema' ? 'active' : '' ?>"><i class="bi bi-sliders"></i>Sistema</a>
    <a href="<?= url('ajustes?tab=tipos') ?>"   class="cfg-tab <?= $tab==='tipos'   ? 'active' : '' ?>"><i class="bi bi-tags-fill"></i>Tipos de tarea</a>
    <?php endif; ?>
</div>

<?php if ($tab === 'cuenta'): ?>
<!-- ============================================================ -->
<!-- TAB: MI CUENTA -->
<!-- ============================================================ -->
<div class="row g-4">

    <!-- PERFIL -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-head"><i class="bi bi-person-vcard teal"></i> Información personal</div>
            <form method="POST" action="<?= url('ajustes/actualizarPerfil') ?>" style="padding:1.4rem;">
                <div class="row g-3">
                    <div class="col-6"><label class="lbl">Nombre</label><input name="nombre" class="inp" value="<?= e($perfil['nombre'] ?? '') ?>" required></div>
                    <div class="col-6"><label class="lbl">Apellidos</label><input name="apellidos" class="inp" value="<?= e($perfil['apellidos'] ?? '') ?>"></div>
                    <div class="col-12"><label class="lbl">Email</label><input type="email" name="email" class="inp" value="<?= e($perfil['email'] ?? '') ?>" required></div>
                    <div class="col-6"><label class="lbl">Teléfono</label><input name="telefono" class="inp" value="<?= e($perfil['telefono'] ?? '') ?>"></div>
                    <div class="col-6"><label class="lbl">Fecha nacimiento</label><input type="date" name="fecha_nacimiento" class="inp" value="<?= e($perfil['fecha_nacimiento'] ?? '') ?>"></div>
                    <div class="col-6"><label class="lbl">Usuario</label><input class="inp" value="<?= e($perfil['usuario'] ?? '') ?>" disabled style="background:#f1f5f9;cursor:not-allowed;"></div>
                    <div class="col-6"><label class="lbl">DNI</label><input class="inp" value="<?= e($perfil['dni'] ?? '') ?>" disabled style="background:#f1f5f9;cursor:not-allowed;"></div>
                </div>
                <div style="margin-top:1.3rem;"><button class="btn btn-teal"><i class="bi bi-save"></i> Guardar cambios</button></div>
            </form>
        </div>
    </div>

    <!-- CONTRASEÑA + SESIÓN -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-head"><i class="bi bi-shield-lock" style="color:#d97706;"></i> Cambiar contraseña</div>
            <form id="formPass" style="padding:1.4rem;" novalidate>
                <div style="margin-bottom:1rem;">
                    <label class="lbl">Contraseña actual</label>
                    <div class="cfg-input-group">
                        <input type="password" name="password_actual" id="passActual" class="inp" autocomplete="current-password">
                        <button type="button" class="cfg-eye" data-target="passActual"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div style="margin-bottom:.5rem;">
                    <label class="lbl">Nueva contraseña</label>
                    <div class="cfg-input-group">
                        <input type="password" name="password_nueva" id="passNueva" class="inp" autocomplete="new-password">
                        <button type="button" class="cfg-eye" data-target="passNueva"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <!-- strength -->
                <div style="margin-bottom:1rem;">
                    <div class="d-flex justify-content-between mb-1"><small class="muted">Fortaleza</small><small id="strLabel" class="muted">—</small></div>
                    <div class="progress" style="height:5px;"><div id="strBar" class="progress-bar" style="width:0%;"></div></div>
                </div>
                <div style="margin-bottom:1.2rem;">
                    <label class="lbl">Confirmar contraseña</label>
                    <div class="cfg-input-group">
                        <input type="password" name="password_confirma" id="passConfirma" class="inp" autocomplete="new-password">
                        <button type="button" class="cfg-eye" data-target="passConfirma"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div id="passAlert" class="cfg-alert" style="display:none;margin-bottom:1rem;"></div>
                <button class="btn" style="background:#f59e0b;color:#fff;"><i class="bi bi-lock-fill"></i> Cambiar contraseña</button>
            </form>
        </div>

        <div class="card" style="margin-top:1rem;">
            <div class="card-head"><i class="bi bi-info-circle" style="color:#0ea5e9;"></i> Información de la cuenta</div>
            <div style="padding:1.1rem 1.4rem;display:flex;flex-direction:column;gap:.6rem;">
                <div class="d-flex justify-content-between"><span class="muted">Usuario</span><span class="fw-bold"><?= e($perfil['usuario'] ?? '') ?></span></div>
                <div class="d-flex justify-content-between"><span class="muted">Rol</span><span><?= rolBadge($perfil['rol'] ?? 'trabajador') ?></span></div>
                <div class="d-flex justify-content-between"><span class="muted">Email</span><span class="fw-bold"><?= e($perfil['email'] ?? '') ?></span></div>
                <div class="d-flex justify-content-between"><span class="muted">Alta</span><span class="fw-bold"><?= fechaLarga($perfil['created_at'] ?? null) ?></span></div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($tab === 'sistema' && $esAdmin): ?>
<!-- ============================================================ -->
<!-- TAB: SISTEMA -->
<!-- ============================================================ -->
<?php
$horaInicio = $config['hora_inicio_jornada'] ?? '08:00';
$umbral     = (int)($config['umbral_retraso_minutos'] ?? 30);
$limite     = date('H:i', strtotime($horaInicio . ' +' . $umbral . ' minutes'));
?>
<form method="POST" action="<?= url('ajustes/guardarSistema') ?>">
    <div class="row g-4">

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-head"><i class="bi bi-building teal"></i> Empresa</div>
                <div style="padding:1.4rem;">
                    <label class="lbl">Nombre de la empresa</label>
                    <input name="nombre_empresa" class="inp" value="<?= e($config['nombre_empresa'] ?? 'Mi Empresa') ?>" required>
                    <p class="muted" style="font-size:.78rem;margin:.5rem 0 0;">Aparecerá en informes y correos.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-head"><i class="bi bi-clock" style="color:#16a34a;"></i> Jornada laboral</div>
                <div style="padding:1.4rem;">
                    <div style="margin-bottom:1.1rem;">
                        <label class="lbl">Hora oficial de inicio</label>
                        <input type="time" name="hora_inicio_jornada" class="inp" value="<?= e($horaInicio) ?>" required>
                    </div>
                    <label class="lbl">Umbral de retraso <span class="badge" id="umbralBadge" style="background:#fef9c3;color:#854d0e;"><?= $umbral ?> min</span></label>
                    <input type="range" name="umbral_retraso_minutos" min="0" max="60" step="5" value="<?= $umbral ?>" class="cfg-range" id="umbralRange"
                           oninput="document.getElementById('umbralBadge').textContent=this.value+' min';">
                    <div class="d-flex justify-content-between"><small class="muted">0 min</small><small class="muted">60 min</small></div>
                    <p class="muted" style="font-size:.78rem;margin:.5rem 0 0;">Margen desde la hora de inicio antes de marcar un fichaje como retraso.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-head"><i class="bi bi-calendar-check" style="color:#2563EB;"></i> Valores por defecto</div>
                <div style="padding:1.4rem;">
                    <div class="row g-3">
                        <div class="col-6"><label class="lbl">Horas / día</label><input type="number" name="horas_jornada_defecto" class="inp" min="0" max="24" step="0.5" value="<?= e($config['horas_jornada_defecto'] ?? '7.5') ?>" required></div>
                        <div class="col-6"><label class="lbl">Horas / semana</label><input type="number" name="horas_semana_defecto" class="inp" min="0" max="168" step="0.5" value="<?= e($config['horas_semana_defecto'] ?? '37.5') ?>" required></div>
                    </div>
                    <p class="muted" style="font-size:.78rem;margin:.7rem 0 0;">Sugerencia al crear una jornada nueva.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-head"><i class="bi bi-info-circle" style="color:#0ea5e9;"></i> Cómo se calculan los retrasos</div>
                <div style="padding:1.4rem;">
                    <p class="muted" style="font-size:.85rem;margin:0 0 1rem;">Un fichaje es <strong>retraso</strong> cuando la entrada supera la <em>hora de inicio + umbral</em>.</p>
                    <div class="cfg-formula">
                        <div class="cfg-formula-row"><span>Hora inicio</span><strong><?= e($horaInicio) ?></strong></div>
                        <div class="cfg-formula-row"><span>Umbral</span><strong>+ <?= $umbral ?> min</strong></div>
                        <div class="cfg-formula-row cfg-formula-total"><span>Límite puntualidad</span><strong style="color:#dc2626;"><?= $limite ?></strong></div>
                    </div>
                    <p class="muted" style="font-size:.78rem;margin:.8rem 0 0;">Fichajes antes de las <?= $limite ?> no cuentan como retraso.</p>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1.3rem;"><button class="btn btn-teal" style="padding:.6rem 1.6rem !important;"><i class="bi bi-save"></i> Guardar configuración del sistema</button></div>
</form>

<?php elseif ($tab === 'tipos' && $esAdmin): ?>
<!-- ============================================================ -->
<!-- TAB: TIPOS DE TAREA -->
<!-- ============================================================ -->
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-head"><i class="bi bi-plus-circle" style="color:#16a34a;"></i> Nuevo tipo de tarea</div>
            <form method="POST" action="<?= url('ajustes/addTipo') ?>" style="padding:1.4rem;">
                <label class="lbl">Nombre</label>
                <input name="nombre" class="inp" placeholder="Ej: Desarrollo, Reunión…" required maxlength="100">
                <div style="margin-top:1rem;"><button class="btn btn-teal" style="width:100%;justify-content:center;"><i class="bi bi-plus-lg"></i> Añadir tipo</button></div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-head" style="display:flex;justify-content:space-between;align-items:center;">
                <span><i class="bi bi-list-task teal"></i> Tipos registrados</span>
                <span class="badge" style="background:var(--teal-bg);color:var(--navy);"><?= count($tipos) ?> tipo<?= count($tipos)!==1?'s':'' ?></span>
            </div>
            <div style="overflow-x:auto;">
                <?php if (empty($tipos)): ?>
                <div class="empty"><i class="bi bi-inbox" style="font-size:1.8rem;display:block;margin-bottom:.4rem;"></i>No hay tipos de tarea todavía.</div>
                <?php else: ?>
                <table class="tbl">
                    <thead><tr><th>#</th><th>Nombre</th><th style="text-align:right;">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($tipos as $i => $t): ?>
                    <tr>
                        <td class="muted"><?= $i + 1 ?></td>
                        <td>
                            <form method="POST" action="<?= url('ajustes/editTipo') ?>" id="ft<?= $t['id'] ?>" style="margin:0;">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <input name="nombre" class="inp" style="padding:.4rem .7rem;" value="<?= e($t['nombre']) ?>" required maxlength="100">
                            </form>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <button type="submit" form="ft<?= $t['id'] ?>" class="btn btn-ghost btn-sm" title="Guardar"><i class="bi bi-save"></i></button>
                            <a href="<?= url('ajustes/deleteTipo/' . $t['id']) ?>" class="btn btn-danger btn-sm" title="Eliminar"
                               onclick="return confirm('¿Eliminar el tipo «<?= e($t['nombre']) ?>»? Las tareas asociadas perderán su tipo.')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// eye toggles
document.querySelectorAll('.cfg-eye').forEach(b=>b.addEventListener('click',()=>{
    const i=document.getElementById(b.dataset.target), ic=b.querySelector('i');
    if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}
    else{i.type='password';ic.className='bi bi-eye';}
}));

// password strength
const pn=document.getElementById('passNueva');
if(pn){pn.addEventListener('input',()=>{
    const v=pn.value;let s=0;
    if(v.length>=6)s++; if(v.length>=10)s++; if(/[A-Z]/.test(v))s++; if(/[0-9]/.test(v))s++; if(/[^A-Za-z0-9]/.test(v))s++;
    const L=[{p:0,c:'#e5e7eb',t:'—'},{p:20,c:'#ef4444',t:'Muy débil'},{p:40,c:'#f59e0b',t:'Débil'},{p:60,c:'#0ea5e9',t:'Aceptable'},{p:80,c:'#2563EB',t:'Fuerte'},{p:100,c:'#16a34a',t:'Muy fuerte'}][Math.min(s,5)];
    const bar=document.getElementById('strBar'),lab=document.getElementById('strLabel');
    bar.style.width=L.p+'%';bar.style.background=L.c;lab.textContent=L.t;lab.style.color=L.c;
});}

// change password AJAX
const fp=document.getElementById('formPass');
if(fp){fp.addEventListener('submit',async e=>{
    e.preventDefault();
    const btn=fp.querySelector('[type="submit"]'),al=document.getElementById('passAlert'),orig=btn.innerHTML;
    btn.disabled=true;btn.innerHTML='<i class="bi bi-hourglass-split"></i> Guardando…';al.style.display='none';
    try{
        const res=await fetch('<?= url('ajustes/cambiarPassword') ?>',{method:'POST',body:new FormData(fp)});
        const d=await res.json();
        al.style.display='';al.className='cfg-alert '+(d.ok?'cfg-alert-ok':'cfg-alert-err');
        al.innerHTML='<i class="bi bi-'+(d.ok?'check-circle-fill':'exclamation-triangle-fill')+'"></i> '+d.msg;
        if(d.ok){fp.reset();document.getElementById('strBar').style.width='0%';document.getElementById('strLabel').textContent='—';}
    }catch{al.style.display='';al.className='cfg-alert cfg-alert-err';al.textContent='Error de conexión.';}
    finally{btn.disabled=false;btn.innerHTML=orig;}
});}
</script>
