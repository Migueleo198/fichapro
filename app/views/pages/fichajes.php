<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title">Fichajes</h1>
        <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Registro de entradas y salidas</p>
    </div>
    <a href="<?= url('informes?desde='.$f['desde'].'&hasta='.$f['hasta']) ?>" class="btn btn-ghost"><i class="bi bi-bar-chart-line"></i> Ver informes</a>
</div>

<form method="GET" action="<?= url('fichajes') ?>" class="card" style="padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
    <div style="flex:1;min-width:150px;"><label class="lbl">Empleado</label>
        <select name="empleado" class="inp"><option value="">Todos</option>
        <?php foreach ($empleados as $e): ?><option value="<?= $e['id'] ?>" <?= $f['empleado']==$e['id']?'selected':'' ?>><?= e($e['nombre'].' '.$e['apellidos']) ?></option><?php endforeach; ?>
        </select></div>
    <div><label class="lbl">Desde</label><input type="date" name="desde" value="<?= e($f['desde']) ?>" class="inp"></div>
    <div><label class="lbl">Hasta</label><input type="date" name="hasta" value="<?= e($f['hasta']) ?>" class="inp"></div>
    <div><label class="lbl">Estado</label><select name="estado" class="inp"><option value="">Todos</option>
        <?php foreach (['abierto','cerrado','incidencia','validado'] as $es): ?><option <?= $f['estado']==$es?'selected':'' ?>><?= $es ?></option><?php endforeach; ?>
        </select></div>
    <button class="btn btn-teal"><i class="bi bi-search"></i> Filtrar</button>
</form>

<div class="card">
    <div class="card-head" style="justify-content:space-between;"><span><i class="bi bi-calendar2-check teal"></i> Registros</span><span class="muted" style="font-size:.75rem;font-weight:700;"><?= count($lista) ?></span></div>
    <div style="overflow-x:auto;">
    <table class="tbl">
        <thead><tr><th>Fecha</th><th>Empleado</th><th>Entrada</th><th>Salida</th><th>Total</th><th>Extra</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($lista as $r): ?>
        <tr id="fi-<?= $r['id'] ?>">
            <td style="font-weight:800;color:#0A2E4E;white-space:nowrap;"><?= fechaLarga($r['fecha']) ?></td>
            <td><?= e($r['nombre'].' '.$r['apellidos']) ?></td>
            <td><?= hhmm($r['hora_entrada']) ?></td>
            <td><?= hhmm($r['hora_salida']) ?></td>
            <td style="font-weight:700;"><?= horasLegibles($r['total_horas']) ?></td>
            <td><?= $r['horas_extra']>0 ? '<span style="color:#c2410c;font-weight:700;">'.horasLegibles($r['horas_extra']).'</span>' : '—' ?></td>
            <td><?= estadoFichajeBadge($r['estado']) ?></td>
            <td style="text-align:right;white-space:nowrap;">
                <?php if ($r['estado']!=='validado' && $r['estado']!=='abierto'): ?>
                <button class="btn btn-ghost btn-sm" onclick="validar(<?= $r['id'] ?>)" title="Validar"><i class="bi bi-check2-circle"></i></button>
                <?php endif; ?>
                <button class="btn btn-danger btn-sm" onclick="borrar(<?= $r['id'] ?>)" title="Eliminar"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lista)): ?><tr><td colspan="8" class="empty">Sin fichajes en este período.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
async function validar(id){ const r=await api('/fichajes/validar/'+id); if(r.success){toast('Fichaje validado ✓');setTimeout(()=>location.reload(),500);} }
async function borrar(id){ if(!confirm('¿Eliminar este fichaje?'))return; const r=await api('/fichajes/eliminar/'+id); if(r.success){document.getElementById('fi-'+id).remove();toast('Eliminado','info');} }
</script>
