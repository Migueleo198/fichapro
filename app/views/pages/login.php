<?php require_once RUTA_APP . '/helpers/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso · <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{--blue:#1A649C;--navy:#1E3A8A;--green:#16A34A;--green-d:#15803D;--ink:#1E293B;--mid:#475569;--muted:#64748B;--border:#E2E8F0;--bg:#F8FAFC;}
        *,*::before,*::after{box-sizing:border-box;}
        body{font-family:'Inter',system-ui,sans-serif;margin:0;background:var(--bg);min-height:100vh;
            display:flex;align-items:center;justify-content:center;padding:1rem;color:var(--ink);
            letter-spacing:-0.011em;-webkit-font-smoothing:antialiased;}
        .lg-input:focus{outline:none;border-color:var(--blue) !important;box-shadow:0 0 0 3px rgba(26,100,156,.16) !important;}
        .iw:focus-within{border-color:var(--blue) !important;box-shadow:0 0 0 3px rgba(26,100,156,.16);}
        .btn-login{transition:all .16s;}
        .btn-login:hover{background:var(--green-d) !important;box-shadow:0 6px 18px rgba(34,197,94,.35) !important;}
        @keyframes spin{to{transform:rotate(360deg);}}
    </style>
</head>
<body>
<div style="width:100%;max-width:400px;">
    <!-- brand -->
    <div style="text-align:center;margin-bottom:1.6rem;">
        <div style="width:3.4rem;height:3.4rem;border-radius:15px;background:linear-gradient(135deg,#1A649C,#1E3A8A);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.55rem;box-shadow:0 10px 24px rgba(26,100,156,.35);"><i class="bi bi-stopwatch-fill"></i></div>
        <h1 style="font-size:1.4rem;font-weight:800;color:var(--ink);margin:.7rem 0 .15rem;letter-spacing:-0.03em;">Ficha<span style="color:var(--blue);">Pro</span></h1>
        <p style="font-size:.82rem;font-weight:500;color:var(--muted);margin:0;">Control horario y gestión de personal</p>
    </div>

    <!-- card -->
    <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(24,24,27,.08);border:1px solid var(--border);">
        <div style="padding:1.8rem 1.9rem 1.9rem;">
            <h2 style="text-align:center;font-size:1.02rem;font-weight:700;color:var(--ink);margin:0 0 .25rem;">Inicia sesión</h2>
            <p style="text-align:center;font-size:.82rem;font-weight:500;color:var(--muted);margin:0 0 1.5rem;">Accede para fichar y gestionar tu jornada</p>
            <?php if (isset($_GET['reset'])): ?>
            <div style="margin-bottom:1.1rem;padding:.65rem .9rem;border-radius:9px;background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;font-size:.82rem;font-weight:550;text-align:center;">
                <i class="bi bi-check-circle-fill"></i> Contraseña actualizada. Ya puedes iniciar sesión.
            </div>
            <?php endif; ?>
            <form id="loginForm">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:var(--mid);margin-bottom:.4rem;">Usuario</label>
                    <div class="iw" style="display:flex;align-items:center;gap:.65rem;border:1px solid var(--border);border-radius:10px;padding:.65rem .9rem;background:#fff;transition:all .16s;">
                        <i class="bi bi-person" style="color:var(--muted);font-size:1.05rem;"></i>
                        <input type="text" name="usuario" placeholder="admin" autofocus required class="lg-input"
                               style="flex:1;background:transparent;font-family:inherit;font-size:.9rem;font-weight:500;color:var(--ink);border:none;outline:none;">
                    </div>
                </div>
                <div style="margin-bottom:1.3rem;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:var(--mid);margin-bottom:.4rem;">Contraseña</label>
                    <div class="iw" style="display:flex;align-items:center;gap:.65rem;border:1px solid var(--border);border-radius:10px;padding:.65rem .9rem;background:#fff;transition:all .16s;">
                        <i class="bi bi-lock" style="color:var(--muted);font-size:1.05rem;"></i>
                        <input type="password" name="password" placeholder="••••••••" required class="lg-input"
                               style="flex:1;background:transparent;font-family:inherit;font-size:.9rem;font-weight:500;color:var(--ink);border:none;outline:none;">
                    </div>
                </div>
                <div id="err" style="display:none;margin-bottom:1rem;padding:.65rem .9rem;border-radius:9px;background:#FEF2F2;border:1px solid #FECACA;color:#B91C1C;font-size:.82rem;font-weight:550;text-align:center;"></div>
                <button type="submit" id="btn" class="btn-login" style="width:100%;padding:.85rem;border-radius:10px;border:none;background:var(--green);color:#fff;font-family:inherit;font-size:.92rem;font-weight:700;cursor:pointer;letter-spacing:-0.01em;box-shadow:0 4px 14px rgba(34,197,94,.3);">
                    <i class="bi bi-box-arrow-in-right" style="margin-right:.4rem;"></i>Entrar
                </button>
            </form>
            <p style="text-align:center;margin:1.1rem 0 0;font-size:.82rem;">
                <a href="<?= url('login/recuperar') ?>" style="color:var(--blue);font-weight:600;text-decoration:none;">¿Olvidaste tu contraseña?</a>
            </p>
        </div>
    </div>
    <p style="text-align:center;font-size:.74rem;color:var(--muted);margin:1.3rem 0 0;font-weight:500;"><?= APP_NAME ?> · Control horario</p>
</div>
<script>
const BASE = "<?= URL_BASE ?>";
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const f = e.target, btn = document.getElementById('btn'), err = document.getElementById('err');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:1rem;height:1rem;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;"></span>';
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
