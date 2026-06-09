</main>
</div>

<div id="toast" class="toast"></div>

<script>
function toast(msg, type='ok'){
    const t=document.getElementById('toast');
    t.className='toast '+type; t.textContent=msg; t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'), 3200);
}
async function api(path, body){
    const res = await fetch(BASE + path, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: body ? JSON.stringify(body) : null
    });
    return res.json();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('js/app.js') ?>"></script>
</body>
</html>

