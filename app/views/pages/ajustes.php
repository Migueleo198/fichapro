<div style="margin-bottom:1.5rem;">
    <h1 class="page-title">Ajustes</h1>
    <p class="muted" style="font-weight:600;margin:.3rem 0 0;">Configuración general de la aplicación</p>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-head"><i class="bi bi-gear teal"></i> Parámetros</div>
    <form id="ajForm" style="padding:1.4rem;display:flex;flex-direction:column;gap:1rem;" onsubmit="saveAj(event)">
        <?php foreach ($ajustes as $a): ?>
        <div>
            <label class="lbl"><?= e($a['descripcion'] ?: $a['clave']) ?></label>
            <input class="inp" data-clave="<?= e($a['clave']) ?>" value="<?= e($a['valor']) ?>">
        </div>
        <?php endforeach; ?>
        <div><button class="btn btn-teal" style="justify-content:center;"><i class="bi bi-floppy"></i> Guardar cambios</button></div>
    </form>
</div>

<script>
async function saveAj(ev){ ev.preventDefault();
    const valores={};
    document.querySelectorAll('#ajForm [data-clave]').forEach(i=>valores[i.dataset.clave]=i.value);
    const r=await api('/ajustes/guardar',{valores});
    if(r.success) toast('Ajustes guardados ✓'); else toast('Error','err');
}
</script>
