<?php
$urlActual        = $_GET['url'] ?? '';
$jornadasActivo   = str_starts_with($urlActual, 'jornadas');
$ausenciasActivo  = str_starts_with($urlActual, 'ausencias');
$estadActivo      = str_starts_with($urlActual, 'estadisticas');

function fpActive(string $key): string {
    $url   = $_GET['url'] ?? '';
    $first = strtolower(explode('/', trim($url, '/'))[0] ?? '');
    return strtolower($key) === $first ? 'active' : '';
}
?>

<!-- ====================================================
     DESKTOP SIDEBAR  (azul oscuro)
     ==================================================== -->
<nav class="sidebar d-none d-lg-flex flex-column" id="sidebar">

    <!-- brand -->
    <div class="sidebar-brand">
        <div class="sidebar-logo"><i class="bi bi-stopwatch-fill"></i></div>
        <div>
            <div class="sidebar-name">Ficha<span>Pro</span></div>
            <div class="sidebar-sub">CONTROL HORARIO</div>
        </div>
    </div>

    <!-- links -->
    <div class="sidebar-nav" style="flex:1;overflow-y:auto;">

        <a href="<?= url('home') ?>" class="sidebar-link <?= fpActive('home') ?>">
            <i class="bi bi-grid-1x2"></i>Inicio
        </a>

        <a href="<?= url('fichar') ?>" class="sidebar-link <?= fpActive('fichar') ?>">
            <i class="bi bi-clock-history"></i>Fichar
        </a>

        <?php if (isAdmin()): ?>
        <a href="<?= url('fichajes') ?>" class="sidebar-link <?= fpActive('fichajes') ?>">
            <i class="bi bi-list-check"></i>Detalle fichajes
        </a>
        <a href="<?= url('empleados') ?>" class="sidebar-link <?= fpActive('empleados') ?>">
            <i class="bi bi-people"></i>Empleados
        </a>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
        <!-- JORNADAS (collapsible, solo admin) -->
        <a href="<?= url('jornadas') ?>"
           class="sidebar-link sidebar-collapse-toggle <?= $jornadasActivo ? 'parent-active' : '' ?>"
           <?= !$jornadasActivo ? 'data-bs-toggle="collapse" data-bs-target="#menuJornadas" aria-expanded="false"' : 'aria-expanded="true"' ?>>
            <i class="bi bi-kanban"></i>Jornadas
            <i class="bi bi-chevron-down ms-auto chevron"></i>
        </a>
        <div class="collapse <?= $jornadasActivo ? 'show' : '' ?>" id="menuJornadas">
            <a href="<?= url('jornadas') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'jornadas' ? 'active' : '' ?>">Gestión de jornadas</a>
        </div>
        <?php endif; ?>

        <a href="<?= url('vacaciones') ?>" class="sidebar-link <?= fpActive('vacaciones') ?>">
            <i class="bi bi-airplane"></i>Vacaciones
        </a>

        <!-- AUSENCIAS (collapsible) -->
        <a href="javascript:void(0)"
           class="sidebar-link sidebar-collapse-toggle <?= $ausenciasActivo ? 'parent-active' : '' ?>"
           data-bs-toggle="collapse" data-bs-target="#menuAusencias"
           aria-expanded="<?= $ausenciasActivo ? 'true' : 'false' ?>">
            <i class="bi bi-person-dash"></i>Ausencias
            <i class="bi bi-chevron-down ms-auto chevron"></i>
        </a>
        <div class="collapse <?= $ausenciasActivo ? 'show' : '' ?>" id="menuAusencias">
            <a href="<?= url('ausencias') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'ausencias' ? 'active' : '' ?>">Ausencias</a>
            <?php if (isAdmin()): ?>
            <a href="<?= url('ausencias/tipos') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'ausencias/tipos' ? 'active' : '' ?>">Tipos / Gestión</a>
            <?php endif; ?>
        </div>

        <a href="<?= url('tareas') ?>" class="sidebar-link <?= fpActive('tareas') ?>">
            <i class="bi bi-check2-square"></i>Tareas
        </a>

        <a href="<?= url('incidencias') ?>" class="sidebar-link <?= fpActive('incidencias') ?>">
            <i class="bi bi-exclamation-triangle"></i>Incidencias
        </a>

        <?php if (isAdmin()): ?>
        <a href="<?= url('vehiculos') ?>" class="sidebar-link <?= fpActive('vehiculos') ?>">
            <i class="bi bi-car-front"></i>Vehículos
        </a>

        <!-- ESTADÍSTICAS (collapsible, solo admin) -->
        <a href="javascript:void(0)"
           class="sidebar-link sidebar-collapse-toggle <?= $estadActivo ? 'parent-active' : '' ?>"
           data-bs-toggle="collapse" data-bs-target="#menuEstadisticas"
           aria-expanded="<?= $estadActivo ? 'true' : 'false' ?>">
            <i class="bi bi-bar-chart"></i>Estadísticas
            <i class="bi bi-chevron-down ms-auto chevron"></i>
        </a>
        <div class="collapse <?= $estadActivo ? 'show' : '' ?>" id="menuEstadisticas">
            <a href="<?= url('estadisticas/resumen') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/resumen' ? 'active' : '' ?>">Resumen</a>
            <a href="<?= url('estadisticas/fichajes') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/fichajes' ? 'active' : '' ?>">Fichajes</a>
            <a href="<?= url('estadisticas/horas') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/horas' ? 'active' : '' ?>">Horas</a>
            <a href="<?= url('estadisticas/retrasos') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/retrasos' ? 'active' : '' ?>">Retrasos</a>
            <a href="<?= url('estadisticas/actividad') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/actividad' ? 'active' : '' ?>">Actividad</a>
        </div>

        <a href="<?= url('informes') ?>" class="sidebar-link <?= fpActive('informes') ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>Informes
        </a>

        <a href="<?= url('auditoria') ?>" class="sidebar-link <?= fpActive('auditoria') ?>">
            <i class="bi bi-shield-check"></i>Auditoría
        </a>
        <?php endif; ?>

        <a href="<?= url('ajustes') ?>" class="sidebar-link <?= fpActive('ajustes') ?>">
            <i class="bi bi-gear"></i>Configuración
        </a>

    </div>

    <!-- notifications + user -->
    <div class="sidebar-footer">

        <!-- notifications bell -->
        <div style="position:relative;margin-bottom:.3rem;" id="notifWrapper">
            <button onclick="toggleNotif()" class="sidebar-link sidebar-notif-btn" style="width:100%;background:none;border:none;text-align:left;cursor:pointer;">
                <i class="bi bi-bell"></i>Notificaciones
                <span id="notifBadge" style="display:none;margin-left:auto;background:#EF4444;color:#fff;border-radius:99px;font-size:.64rem;font-weight:700;padding:.12em .5em;min-width:1.3em;text-align:center;">0</span>
            </button>
            <div id="notifDropdown" style="display:none;position:absolute;bottom:calc(100% + 4px);left:0;right:0;background:#fff;border-radius:11px;box-shadow:0 12px 32px rgba(15,23,42,.22);overflow:hidden;z-index:200;border:1px solid var(--border);">
                <div style="padding:.6rem 1rem;border-bottom:1px solid var(--border);font-weight:700;font-size:.78rem;color:var(--text);">Notificaciones</div>
                <div id="notifList" style="max-height:260px;overflow-y:auto;"></div>
            </div>
        </div>

        <!-- user (clic → modal perfil) -->
        <div class="sidebar-user" onclick="abrirPerfil()" title="Ver mi perfil"
             style="display:flex;align-items:center;gap:.55rem;padding:.45rem .5rem;border-radius:9px;background:rgba(255,255,255,.08);margin-bottom:.25rem;">
            <?= avatarHtml(avatarUrl(), $_SESSION['emp_nombre'] ?? 'U', 1.95, 0.72) ?>
            <div style="overflow:hidden;flex:1;">
                <div style="color:#fff;font-size:.78rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($_SESSION['emp_nombre'] ?? 'Usuario') ?></div>
                <div style="color:rgba(255,255,255,.5);font-size:.66rem;text-transform:capitalize;"><?= e($_SESSION['emp_rol'] ?? '') ?></div>
            </div>
            <i class="bi bi-chevron-expand"></i>
        </div>
        <a href="<?= url('login/logout') ?>" class="sidebar-link sidebar-logout" style="color:#FCA5A5;font-size:.8rem;">
            <i class="bi bi-box-arrow-left"></i>Cerrar sesión
        </a>
    </div>
</nav>

<!-- ====================================================
     MOBILE TOPBAR + OFFCANVAS
     ==================================================== -->
<nav class="mobile-topbar d-flex d-lg-none align-items-center justify-content-between">
    <button data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" style="background:rgba(255,255,255,.14);border:none;color:#fff;width:2.3rem;height:2.3rem;border-radius:8px;font-size:1.25rem;display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-list"></i>
    </button>
    <span style="font-weight:800;color:#fff;letter-spacing:-0.02em;"><i class="bi bi-stopwatch-fill me-1"></i> <?= APP_NAME ?></span>
    <div style="width:2.3rem;"></div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" style="background:linear-gradient(195deg,#1A649C 0%,#1E3A8A 100%);width:264px;">
    <div class="offcanvas-header" style="border-bottom:1px solid rgba(255,255,255,.1);">
        <div class="sidebar-brand" style="border:none;padding:0;">
            <div class="sidebar-logo"><i class="bi bi-stopwatch-fill"></i></div>
            <div>
                <div class="sidebar-name">Ficha<span>Pro</span></div>
                <div class="sidebar-sub">CONTROL HORARIO</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" style="filter:invert(1) brightness(2);opacity:.7;"></button>
    </div>
    <div class="offcanvas-body sidebar-nav p-0" style="overflow-y:auto;">

        <a href="<?= url('home') ?>" class="sidebar-link <?= fpActive('home') ?>"><i class="bi bi-grid-1x2"></i>Inicio</a>
        <a href="<?= url('fichar') ?>" class="sidebar-link <?= fpActive('fichar') ?>"><i class="bi bi-clock-history"></i>Fichar</a>

        <?php if (isAdmin()): ?>
        <a href="<?= url('fichajes') ?>" class="sidebar-link <?= fpActive('fichajes') ?>"><i class="bi bi-list-check"></i>Detalle fichajes</a>
        <a href="<?= url('empleados') ?>" class="sidebar-link <?= fpActive('empleados') ?>"><i class="bi bi-people"></i>Empleados</a>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
        <!-- Jornadas (solo admin) -->
        <a href="javascript:void(0)" class="sidebar-link sidebar-collapse-toggle <?= $jornadasActivo ? 'parent-active' : '' ?>"
           data-bs-toggle="collapse" data-bs-target="#mobileMenuJornadas" aria-expanded="<?= $jornadasActivo ? 'true' : 'false' ?>">
            <i class="bi bi-kanban"></i>Jornadas<i class="bi bi-chevron-down ms-auto chevron"></i>
        </a>
        <div class="collapse <?= $jornadasActivo ? 'show' : '' ?>" id="mobileMenuJornadas">
            <a href="<?= url('jornadas') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'jornadas' ? 'active' : '' ?>">Gestión de jornadas</a>
        </div>
        <?php endif; ?>

        <a href="<?= url('vacaciones') ?>" class="sidebar-link <?= fpActive('vacaciones') ?>"><i class="bi bi-airplane"></i>Vacaciones</a>

        <!-- Ausencias -->
        <a href="javascript:void(0)" class="sidebar-link sidebar-collapse-toggle <?= $ausenciasActivo ? 'parent-active' : '' ?>"
           data-bs-toggle="collapse" data-bs-target="#mobileMenuAusencias" aria-expanded="<?= $ausenciasActivo ? 'true' : 'false' ?>">
            <i class="bi bi-person-dash"></i>Ausencias<i class="bi bi-chevron-down ms-auto chevron"></i>
        </a>
        <div class="collapse <?= $ausenciasActivo ? 'show' : '' ?>" id="mobileMenuAusencias">
            <a href="<?= url('ausencias') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'ausencias' ? 'active' : '' ?>">Ausencias</a>
            <?php if (isAdmin()): ?>
            <a href="<?= url('ausencias/tipos') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'ausencias/tipos' ? 'active' : '' ?>">Tipos / Gestión</a>
            <?php endif; ?>
        </div>

        <a href="<?= url('tareas') ?>" class="sidebar-link <?= fpActive('tareas') ?>"><i class="bi bi-check2-square"></i>Tareas</a>
        <a href="<?= url('incidencias') ?>" class="sidebar-link <?= fpActive('incidencias') ?>"><i class="bi bi-exclamation-triangle"></i>Incidencias</a>

        <?php if (isAdmin()): ?>
        <a href="<?= url('vehiculos') ?>" class="sidebar-link <?= fpActive('vehiculos') ?>"><i class="bi bi-car-front"></i>Vehículos</a>

        <!-- Estadísticas -->
        <a href="javascript:void(0)" class="sidebar-link sidebar-collapse-toggle <?= $estadActivo ? 'parent-active' : '' ?>"
           data-bs-toggle="collapse" data-bs-target="#mobileMenuEstadisticas" aria-expanded="<?= $estadActivo ? 'true' : 'false' ?>">
            <i class="bi bi-bar-chart"></i>Estadísticas<i class="bi bi-chevron-down ms-auto chevron"></i>
        </a>
        <div class="collapse <?= $estadActivo ? 'show' : '' ?>" id="mobileMenuEstadisticas">
            <a href="<?= url('estadisticas/resumen') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/resumen' ? 'active' : '' ?>">Resumen</a>
            <a href="<?= url('estadisticas/fichajes') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/fichajes' ? 'active' : '' ?>">Fichajes</a>
            <a href="<?= url('estadisticas/horas') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/horas' ? 'active' : '' ?>">Horas</a>
            <a href="<?= url('estadisticas/retrasos') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/retrasos' ? 'active' : '' ?>">Retrasos</a>
            <a href="<?= url('estadisticas/actividad') ?>" class="sidebar-link sidebar-sub-link <?= $urlActual === 'estadisticas/actividad' ? 'active' : '' ?>">Actividad</a>
        </div>

        <a href="<?= url('informes') ?>" class="sidebar-link <?= fpActive('informes') ?>"><i class="bi bi-file-earmark-bar-graph"></i>Informes</a>
        <a href="<?= url('auditoria') ?>" class="sidebar-link <?= fpActive('auditoria') ?>"><i class="bi bi-shield-check"></i>Auditoría</a>
        <?php endif; ?>

        <a href="<?= url('ajustes') ?>" class="sidebar-link <?= fpActive('ajustes') ?>"><i class="bi bi-gear"></i>Configuración</a>

        <hr style="border-color:rgba(255,255,255,.1);margin:.5rem .7rem;">
        <a href="javascript:void(0)" onclick="abrirPerfil()" class="sidebar-link"><i class="bi bi-person-circle"></i>Mi perfil</a>
        <a href="<?= url('login/logout') ?>" class="sidebar-link sidebar-logout" style="color:#FCA5A5;"><i class="bi bi-box-arrow-left"></i>Cerrar sesión</a>
    </div>
</div>

<!-- ====================================================
     MODAL — Mi perfil
     ==================================================== -->
<div class="modal fade" id="perfilModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Mi perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" style="padding:1.3rem;">
                <div id="perfilMsg" class="cfg-alert" style="display:none;"></div>
                <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:1.2rem;">
                    <div style="position:relative;flex-shrink:0;">
                        <div id="perfilAvatar" style="width:3.4rem;height:3.4rem;border-radius:50%;background:#1A649C;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;overflow:hidden;background-size:cover;background-position:center;">U</div>
                        <button type="button" onclick="document.getElementById('perfilFoto').click()" title="Cambiar foto"
                            style="position:absolute;right:-3px;bottom:-3px;width:1.4rem;height:1.4rem;border-radius:50%;background:#1A649C;color:#fff;border:2px solid #fff;display:flex;align-items:center;justify-content:center;font-size:.62rem;cursor:pointer;padding:0;line-height:1;">
                            <i class="bi bi-camera-fill"></i>
                        </button>
                        <input type="file" id="perfilFoto" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none;">
                    </div>
                    <div style="min-width:0;">
                        <div id="perfilNombreBig" style="font-weight:800;color:var(--text);font-size:1rem;line-height:1.2;">—</div>
                        <div id="perfilRolTxt" class="muted" style="font-size:.8rem;">—</div>
                        <a href="javascript:void(0)" id="perfilQuitarFoto" onclick="quitarFotoPerfil()" style="display:none;font-size:.72rem;color:#dc2626;font-weight:600;">Quitar foto</a>
                    </div>
                </div>
                <form id="perfilForm">
                    <div class="row g-2">
                        <div class="col-6"><label class="lbl">Nombre</label><input name="nombre" class="inp" required></div>
                        <div class="col-6"><label class="lbl">Apellidos</label><input name="apellidos" class="inp"></div>
                        <div class="col-12"><label class="lbl">Email</label><input name="email" type="email" class="inp" required></div>
                        <div class="col-6"><label class="lbl">Teléfono</label><input name="telefono" class="inp"></div>
                        <div class="col-6"><label class="lbl">Nacimiento</label><input name="fecha_nacimiento" type="date" class="inp"></div>
                        <div class="col-6"><label class="lbl">Usuario</label><input id="perfilUsuario" class="inp" disabled style="background:#f1f5f9;cursor:not-allowed;"></div>
                        <div class="col-6"><label class="lbl">DNI</label><input id="perfilDni" class="inp" disabled style="background:#f1f5f9;cursor:not-allowed;"></div>
                    </div>
                    <div style="display:flex;gap:.5rem;margin-top:1.2rem;">
                        <button type="submit" class="btn btn-teal" style="flex:1;justify-content:center;"><i class="bi bi-save"></i> Guardar cambios</button>
                        <a href="<?= url('ajustes?tab=cuenta') ?>" class="btn btn-ghost" title="Configuración completa (contraseña…)"><i class="bi bi-gear"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const NOTIF_COLORS={danger:'#FEF2F2',warning:'#FFF7ED',success:'#F0FDF4',primary:'#EFF6FF'};
    const NOTIF_TEXT  ={danger:'#B91C1C',warning:'#C2410C',success:'#15803D',primary:'#1A649C'};

    function loadNotificaciones(){
        if(typeof BASE==='undefined') return;
        fetch(BASE+'/notificaciones/getNotificaciones')
        .then(r=>r.json()).then(data=>{
            const badge=document.getElementById('notifBadge');
            const list =document.getElementById('notifList');
            if(!badge||!list) return;
            if(!data.success||data.total===0){
                badge.style.display='none';
                list.innerHTML='<div style="padding:.9rem;text-align:center;color:var(--muted);font-size:.82rem;">Sin notificaciones</div>';
                return;
            }
            badge.style.display='inline-block'; badge.textContent=data.total;
            list.innerHTML=data.items.map(n=>`
                <a href="${BASE}${n.url}" style="display:flex;align-items:center;gap:.7rem;padding:.6rem 1rem;border-bottom:1px solid var(--border);text-decoration:none;background:${NOTIF_COLORS[n.tipo]||'#F8FAFC'};">
                    <i class="bi ${n.icono}" style="color:${NOTIF_TEXT[n.tipo]||'#475569'};font-size:1rem;"></i>
                    <span style="font-size:.8rem;font-weight:600;color:${NOTIF_TEXT[n.tipo]||'#1E293B'};">${n.mensaje}</span>
                    <span style="margin-left:auto;background:#fff;color:${NOTIF_TEXT[n.tipo]||'#475569'};border:1px solid var(--border);border-radius:99px;font-size:.68rem;font-weight:700;padding:.1em .5em;">${n.count}</span>
                </a>
            `).join('');
        }).catch(()=>{});
    }

    window.toggleNotif=function(){
        const dd=document.getElementById('notifDropdown');
        if(!dd) return;
        if(dd.style.display==='none'){dd.style.display='block';loadNotificaciones();}
        else dd.style.display='none';
    };
    document.addEventListener('click',function(e){
        const w=document.getElementById('notifWrapper');
        if(w&&!w.contains(e.target)){const dd=document.getElementById('notifDropdown');if(dd)dd.style.display='none';}
    });
    document.addEventListener('DOMContentLoaded',loadNotificaciones);

    // ── Perfil (modal) ──────────────────────────────────────
    window.abrirPerfil=function(){
        if(typeof BASE==='undefined'||typeof bootstrap==='undefined') return;
        fetch(BASE+'/ajustes/perfil').then(r=>r.json()).then(d=>{
            if(!d.success) return;
            const u=d.data, f=document.getElementById('perfilForm');
            f.nombre.value=u.nombre||''; f.apellidos.value=u.apellidos||'';
            f.email.value=u.email||''; f.telefono.value=u.telefono||'';
            f.fecha_nacimiento.value=u.fecha_nacimiento||'';
            document.getElementById('perfilUsuario').value=u.usuario||'';
            document.getElementById('perfilDni').value=u.dni||'';
            document.getElementById('perfilNombreBig').textContent=((u.nombre||'')+' '+(u.apellidos||'')).trim();
            document.getElementById('perfilRolTxt').textContent=u.rol==='admin'?'Administrador':'Trabajador';
            setPerfilAvatar(u.foto_url, u.nombre);
            const msg=document.getElementById('perfilMsg'); if(msg) msg.style.display='none';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('perfilModal')).show();
        }).catch(()=>{});
    };

    // Pinta el avatar del modal con foto o inicial, y muestra/oculta "Quitar foto".
    function setPerfilAvatar(fotoUrl, nombre){
        const av=document.getElementById('perfilAvatar');
        const quitar=document.getElementById('perfilQuitarFoto');
        if(!av) return;
        if(fotoUrl){
            av.style.backgroundImage='url("'+fotoUrl+'")';
            av.textContent='';
            if(quitar) quitar.style.display='inline';
        }else{
            av.style.backgroundImage='none';
            av.textContent=(nombre||'U').charAt(0).toUpperCase();
            if(quitar) quitar.style.display='none';
        }
    }

    function perfilFotoMsg(html, ok){
        const msg=document.getElementById('perfilMsg');
        if(!msg) return;
        msg.style.display='';
        msg.className='cfg-alert '+(ok===null?'':(ok?'cfg-alert-ok':'cfg-alert-err'));
        msg.innerHTML=html;
    }

    // Subir foto al elegir archivo
    document.addEventListener('DOMContentLoaded',function(){
        const inp=document.getElementById('perfilFoto');
        if(!inp) return;
        inp.addEventListener('change',async function(){
            if(!this.files||!this.files[0]) return;
            perfilFotoMsg('<i class="bi bi-hourglass-split"></i> Subiendo foto…',null);
            const fd=new FormData(); fd.append('foto',this.files[0]);
            try{
                const res=await fetch(BASE+'/ajustes/subirFoto',{method:'POST',body:fd});
                const d=await res.json();
                perfilFotoMsg('<i class="bi bi-'+(d.success?'check-circle-fill':'exclamation-triangle-fill')+'"></i> '+(d.message||(d.success?'Foto actualizada':'Error')), d.success);
                if(d.success){ setPerfilAvatar(d.foto, ''); setTimeout(()=>location.reload(),800); }
            }catch{ perfilFotoMsg('<i class="bi bi-exclamation-triangle-fill"></i> Error de conexión.',false); }
            finally{ this.value=''; }
        });
    });

    // Quitar foto
    window.quitarFotoPerfil=async function(){
        if(!confirm('¿Quitar tu foto de perfil?')) return;
        try{
            const res=await fetch(BASE+'/ajustes/eliminarFoto',{method:'POST'});
            const d=await res.json();
            perfilFotoMsg('<i class="bi bi-'+(d.success?'check-circle-fill':'exclamation-triangle-fill')+'"></i> '+(d.message||''), d.success);
            if(d.success) setTimeout(()=>location.reload(),700);
        }catch{ perfilFotoMsg('<i class="bi bi-exclamation-triangle-fill"></i> Error de conexión.',false); }
    };
    document.addEventListener('DOMContentLoaded',function(){
        const f=document.getElementById('perfilForm');
        if(!f) return;
        f.addEventListener('submit',async function(e){
            e.preventDefault();
            const msg=document.getElementById('perfilMsg');
            const btn=f.querySelector('[type="submit"]'), orig=btn.innerHTML;
            btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Guardando…';
            try{
                const res=await fetch(BASE+'/ajustes/guardarPerfil',{method:'POST',body:new FormData(f)});
                const d=await res.json();
                msg.style.display=''; msg.className='cfg-alert '+(d.success?'cfg-alert-ok':'cfg-alert-err');
                msg.innerHTML='<i class="bi bi-'+(d.success?'check-circle-fill':'exclamation-triangle-fill')+'"></i> '+d.message;
                if(d.success) setTimeout(()=>location.reload(),900);
            }catch{ msg.style.display=''; msg.className='cfg-alert cfg-alert-err'; msg.textContent='Error de conexión.'; }
            finally{ btn.disabled=false; btn.innerHTML=orig; }
        });
    });
})();
</script>
