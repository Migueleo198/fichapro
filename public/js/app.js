/* FichaPro — shared client helpers (page-specific logic lives in each view) */

function confirmAction(msg) {
    return window.confirm(msg);
}

// close any open modal on Escape / backdrop click
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-bg.open').forEach(m => m.classList.remove('open'));
    }
});
document.addEventListener('click', (e) => {
    if (e.target.classList && e.target.classList.contains('modal-bg')) {
        e.target.classList.remove('open');
    }
});

/* ============================================================
   FILTROS DE TABLA (cliente) — genérico por data-attributes
   Panel: <div class="fp-filtros" data-fp-target="#tbodyId"> con
          controles [data-fp-key="x"] (+ opcional data-fp-mode="eq").
   Filas: <tr data-row data-x="valor en minúsculas" ...>
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.fp-filtros[data-fp-target]').forEach(panel => {
        const tbody = document.querySelector(panel.dataset.fpTarget);
        if (!tbody) return;
        const controls = panel.querySelectorAll('[data-fp-key]');
        const dot = panel.closest('.dropdown')?.querySelector('[data-fp-dot]');

        function apply() {
            const active = [...controls]
                .map(c => ({ key: c.dataset.fpKey, mode: c.dataset.fpMode || 'includes', val: (c.value || '').toString().toLowerCase().trim() }))
                .filter(f => f.val !== '');
            let visible = 0;
            tbody.querySelectorAll('tr[data-row]').forEach(tr => {
                const ok = active.every(f => {
                    const v = (tr.dataset[f.key] || '').toLowerCase();
                    return f.mode === 'eq' ? v === f.val : v.includes(f.val);
                });
                tr.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            let nr = tbody.querySelector('.fp-noresult');
            if (visible === 0) {
                if (!nr) {
                    nr = document.createElement('tr');
                    nr.className = 'fp-noresult';
                    nr.innerHTML = '<td colspan="100" class="empty">Sin resultados con esos filtros</td>';
                    tbody.appendChild(nr);
                }
                nr.style.display = '';
            } else if (nr) {
                nr.style.display = 'none';
            }
            if (dot) dot.style.display = active.length ? '' : 'none';
        }

        controls.forEach(c => { c.addEventListener('input', apply); c.addEventListener('change', apply); });
        const clear = panel.querySelector('[data-fp-clear]');
        if (clear) clear.addEventListener('click', () => { controls.forEach(c => c.value = ''); apply(); });
    });
});
