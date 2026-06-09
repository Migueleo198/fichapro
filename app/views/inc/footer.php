</main>
</div>

<div id="toast" class="toast"></div>

<script>
const BASE = "<?= URL_BASE ?>";
function toast(msg, type='ok'){
    const t=document.getElementById('toast');
    t.className='toast '+type; t.textContent=msg; t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'), 3200);
}
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('backdrop').classList.toggle('show');
}
function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('backdrop').classList.remove('show');
}
async function api(path, body){
    const res = await fetch(BASE + path, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: body ? JSON.stringify(body) : null
    });
    return res.json();
}
</script>
<script src="<?= url('js/app.js') ?>"></script>
</body>
</html>
