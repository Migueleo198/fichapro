<?php require_once RUTA_APP . '/helpers/functions.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña · <?= APP_NAME ?></title>
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
        .iw:focus-within{border-color:var(--blue) !important;box-shadow:0 0 0 3px rgba(26,100,156,.16);}
        .lg-input:focus{outline:none;}
        .btn-main{transition:all .16s;}
        .btn-main:hover{background:var(--green-d) !important;box-shadow:0 6px 18px rgba(34,197,94,.32) !important;}
        a.link{color:var(--blue);font-weight:600;text-decoration:none;}
        a.link:hover{text-decoration:underline;}
        .eye{background:none;border:none;color:var(--muted);cursor:pointer;padding:0 .1rem;}
    </style>
</head>
<body>
<div style="width:100%;max-width:400px;">
    <div style="text-align:center;margin-bottom:1.6rem;">
        <div style="width:3.4rem;height:3.4rem;border-radius:15px;background:linear-gradient(135deg,#1A649C,#1E3A8A);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.55rem;box-shadow:0 10px 24px rgba(26,100,156,.35);"><i class="bi bi-shield-lock-fill"></i></div>
        <h1 style="font-size:1.4rem;font-weight:800;color:var(--ink);margin:.7rem 0 .15rem;letter-spacing:-0.03em;">Ficha<span style="color:var(--blue);">Pro</span></h1>
        <p style="font-size:.82rem;font-weight:500;color:var(--muted);margin:0;">Nueva contraseña</p>
    </div>

    <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(24,24,27,.08);border:1px solid var(--border);">
        <div style="padding:1.8rem 1.9rem 1.9rem;">

            <?php if (empty($valido)): ?>
                <div style="text-align:center;">
                    <i class="bi bi-x-octagon" style="font-size:2.2rem;color:#DC2626;"></i>
                    <h2 style="font-size:1.02rem;font-weight:700;margin:.6rem 0 .3rem;">Enlace no válido</h2>
                    <p style="font-size:.85rem;color:var(--mid);margin:0 0 1.3rem;line-height:1.5;">
                        El enlace no es válido, ya se ha usado o ha caducado. Solicita uno nuevo.
                    </p>
                    <a href="<?= url('login/recuperar') ?>" class="link"><i class="bi bi-arrow-repeat"></i> Solicitar nuevo enlace</a>
                </div>
            <?php else: ?>
                <h2 style="text-align:center;font-size:1.02rem;font-weight:700;color:var(--ink);margin:0 0 .25rem;">Crea una nueva contraseña</h2>
                <p style="text-align:center;font-size:.82rem;font-weight:500;color:var(--muted);margin:0 0 1.5rem;">Debe tener al menos 6 caracteres</p>

                <?php
                $msgErr = ['corta' => 'La contraseña debe tener al menos 6 caracteres.', 'coinciden' => 'Las contraseñas no coinciden.'];
                if (($err ?? null) && isset($msgErr[$err])): ?>
                <div style="margin-bottom:1rem;padding:.65rem .9rem;border-radius:9px;background:#FEF2F2;border:1px solid #FECACA;color:#B91C1C;font-size:.82rem;font-weight:550;text-align:center;">
                    <?= $msgErr[$err] ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('login/guardarPassword') ?>">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div style="margin-bottom:1rem;">
                        <label style="display:block;font-size:.78rem;font-weight:600;color:var(--mid);margin-bottom:.4rem;">Nueva contraseña</label>
                        <div class="iw" style="display:flex;align-items:center;gap:.65rem;border:1px solid var(--border);border-radius:10px;padding:.65rem .9rem;background:#fff;transition:all .16s;">
                            <i class="bi bi-lock" style="color:var(--muted);font-size:1.05rem;"></i>
                            <input type="password" name="password" id="p1" placeholder="••••••••" required minlength="6" class="lg-input"
                                   style="flex:1;background:transparent;font-family:inherit;font-size:.9rem;font-weight:500;color:var(--ink);border:none;outline:none;">
                            <button type="button" class="eye" onclick="tog('p1',this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div style="margin-bottom:1.3rem;">
                        <label style="display:block;font-size:.78rem;font-weight:600;color:var(--mid);margin-bottom:.4rem;">Repite la contraseña</label>
                        <div class="iw" style="display:flex;align-items:center;gap:.65rem;border:1px solid var(--border);border-radius:10px;padding:.65rem .9rem;background:#fff;transition:all .16s;">
                            <i class="bi bi-lock-fill" style="color:var(--muted);font-size:1.05rem;"></i>
                            <input type="password" name="password2" id="p2" placeholder="••••••••" required minlength="6" class="lg-input"
                                   style="flex:1;background:transparent;font-family:inherit;font-size:.9rem;font-weight:500;color:var(--ink);border:none;outline:none;">
                            <button type="button" class="eye" onclick="tog('p2',this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="btn-main" style="width:100%;padding:.85rem;border-radius:10px;border:none;background:var(--green);color:#fff;font-family:inherit;font-size:.92rem;font-weight:700;cursor:pointer;letter-spacing:-0.01em;box-shadow:0 4px 14px rgba(34,197,94,.3);">
                        <i class="bi bi-check2-circle" style="margin-right:.4rem;"></i>Guardar contraseña
                    </button>
                </form>
                <p style="text-align:center;margin:1.3rem 0 0;font-size:.82rem;">
                    <a href="<?= url('login') ?>" class="link"><i class="bi bi-arrow-left"></i> Volver al inicio de sesión</a>
                </p>
            <?php endif; ?>

        </div>
    </div>
    <p style="text-align:center;font-size:.74rem;color:var(--muted);margin:1.3rem 0 0;font-weight:500;"><?= APP_NAME ?> · Control horario</p>
</div>
<script>
function tog(id,b){const i=document.getElementById(id),ic=b.querySelector('i');
    if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}else{i.type='password';ic.className='bi bi-eye';}}
</script>
</body>
</html>
