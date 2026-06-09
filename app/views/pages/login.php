<?php require_once RUTA_APP . '/helpers/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;}
        body{font-family:'Nunito',sans-serif;margin:0;background:#F4F6F8;min-height:100vh;
            display:flex;align-items:center;justify-content:center;padding:1rem;}
        input:focus{outline:none;border-color:#3EC6C1 !important;box-shadow:0 0 0 3px rgba(62,198,193,.18) !important;}
        .iw:focus-within{border-color:#3EC6C1 !important;background:#fff !important;}
        .btn-login{transition:all .2s;}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(62,198,193,.45) !important;}
        @keyframes spin{to{transform:rotate(360deg);}}
    </style>
</head>
<body>
<div style="width:100%;max-width:410px;">
    <div style="background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 8px 40px rgba(10,46,78,.15);border:1px solid #dce6ee;">
        <div style="background:#0A2E4E;padding:2.4rem 2rem 1.8rem;text-align:center;">
            <div style="width:3.8rem;height:3.8rem;border-radius:14px;background:#3EC6C1;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.7rem;box-shadow:0 4px 16px rgba(62,198,193,.45);">⏱</div>
            <h1 style="font-size:1.5rem;font-weight:900;color:#fff;margin:0;">Ficha<span style="color:#3EC6C1;">Pro</span></h1>
            <p style="font-size:.78rem;font-weight:600;color:rgba(255,255,255,.5);margin:.35rem 0 0;">Control horario y gestión de personal</p>
            <svg style="display:block;margin-top:1.4rem;margin-bottom:-1px;" viewBox="0 0 400 24"><path fill="#fff" d="M0,16 C80,28 160,4 240,16 C320,28 360,8 400,16 L400,24 L0,24 Z"/></svg>
        </div>
        <div style="padding:1.75rem 2rem 2rem;">
            <p style="text-align:center;font-size:.875rem;font-weight:600;color:#7a9cb0;margin:0 0 1.5rem;">Inicia sesión para fichar 👋</p>
            <form id="loginForm">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#7a9cb0;margin-bottom:.5rem;">Usuario</label>
                    <div class="iw" style="display:flex;align-items:center;gap:.7rem;border:1.5px solid #dce6ee;border-radius:11px;padding:.7rem 1rem;background:#F4F6F8;transition:all .18s;">
                        <i class="bi bi-person-fill" style="color:#3EC6C1;font-size:1.1rem;"></i>
                        <input type="text" name="usuario" placeholder="admin" autofocus required
                               style="flex:1;background:transparent;font-family:'Nunito',sans-serif;font-size:.875rem;font-weight:700;color:#0A2E4E;border:none;outline:none;">
                    </div>
                </div>
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#7a9cb0;margin-bottom:.5rem;">Contraseña</label>
                    <div class="iw" style="display:flex;align-items:center;gap:.7rem;border:1.5px solid #dce6ee;border-radius:11px;padding:.7rem 1rem;background:#F4F6F8;transition:all .18s;">
                        <i class="bi bi-lock-fill" style="color:#3EC6C1;font-size:1.1rem;"></i>
                        <input type="password" name="password" placeholder="••••••••" required
                               style="flex:1;background:transparent;font-family:'Nunito',sans-serif;font-size:.875rem;font-weight:700;color:#0A2E4E;border:none;outline:none;">
                    </div>
                </div>
                <div id="err" style="display:none;margin-bottom:1rem;padding:.7rem 1rem;border-radius:10px;background:#fff5f5;border:1px solid #fecaca;color:#dc2626;font-size:.82rem;font-weight:700;text-align:center;"></div>
                <button type="submit" id="btn" class="btn-login" style="width:100%;padding:1rem;border-radius:11px;border:none;background:#3EC6C1;color:#fff;font-family:'Nunito',sans-serif;font-size:.95rem;font-weight:900;cursor:pointer;box-shadow:0 4px 16px rgba(62,198,193,.35);">
                    <i class="bi bi-box-arrow-in-right" style="margin-right:.4rem;"></i>Entrar
                </button>
            </form>
            <p style="text-align:center;font-size:.72rem;color:#b0c8d8;margin:1.2rem 0 0;font-weight:600;"><?= APP_NAME ?> · Control horario</p>
        </div>
    </div>
</div>
<script>
const BASE = "<?= URL_BASE ?>";
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const f = e.target, btn = document.getElementById('btn'), err = document.getElementById('err');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:1rem;height:1rem;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:.5rem;"></span>Entrando...';
    err.style.display = 'none';
    try {
        const res = await fetch(BASE + '/login/login', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ usuario: f.usuario.value, password: f.password.value })
        });
        const data = await res.json();
        if (data.success) { window.location.href = data.redirect; }
        else { err.textContent = data.message; err.style.display='block'; reset(); }
    } catch { err.textContent='Error de conexión'; err.style.display='block'; reset(); }
    function reset(){ btn.disabled=false; btn.innerHTML='<i class="bi bi-box-arrow-in-right" style="margin-right:.4rem;"></i>Entrar'; }
});
</script>
</body>
</html>
