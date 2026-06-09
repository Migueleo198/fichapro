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
