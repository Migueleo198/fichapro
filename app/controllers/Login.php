<?php

class Login extends Controller {

    public function index() {
        if (isLoggedIn()) redirect('home');
        $this->view('pages/login', ['title' => 'Acceso']);
    }

    public function login() {
        $d = $this->input();
        $usuario  = trim($d['usuario'] ?? '');
        $password = $d['password'] ?? '';

        if (!$usuario || !$password) {
            $this->json(['success' => false, 'message' => 'Introduce usuario y contraseña']);
        }

        $emp = $this->model('EmpleadoModel')->login($usuario, $password);
        if (!$emp) {
            $this->json(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
        }

        session_regenerate_id(true);
        $_SESSION['emp_id']     = $emp['id'];
        $_SESSION['emp_nombre'] = $emp['nombre'];
        $_SESSION['emp_rol']    = $emp['rol'];
        $_SESSION['emp_foto']   = $emp['foto'] ?? '';

        $this->json(['success' => true, 'redirect' => url($emp['rol'] === 'admin' ? 'home' : 'fichar')]);
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        redirect('login');
    }

    // ============================================================
    // RECUPERACIÓN DE CONTRASEÑA
    // ============================================================

    /** Formulario: pedir el correo. */
    public function recuperar() {
        if (isLoggedIn()) redirect('home');
        $this->view('pages/recuperar', [
            'ok'  => isset($_GET['ok']),
            'err' => $_GET['err'] ?? null,
        ]);
    }

    /** Procesa el correo y envía el enlace (respuesta siempre genérica). */
    public function enviarRecuperacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('login/recuperar');

        $email = trim($_POST['email'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirect('login/recuperar?err=email');
        }

        $emp = $this->model('EmpleadoModel')->getByEmail($email);
        if ($emp) {
            $token = $this->model('PasswordResetModel')->crear((int)$emp['id'], 60);
            $link  = rtrim(RUTA_URL, '/') . '/login/restablecer/' . $token;
            $this->enviarEnlace($emp, $link);
        }
        // No revelamos si el correo existe o no.
        redirect('login/recuperar?ok=1');
    }

    /** Formulario: nueva contraseña (validando el token). */
    public function restablecer($token = '') {
        if (isLoggedIn()) redirect('home');
        $empId = $this->model('PasswordResetModel')->empleadoPorToken($token);
        $this->view('pages/restablecer', [
            'token'  => $token,
            'valido' => $empId !== null,
            'err'    => $_GET['err'] ?? null,
        ]);
    }

    /** Guarda la nueva contraseña. */
    public function guardarPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('login');

        $token = $_POST['token'] ?? '';
        $p1    = $_POST['password']  ?? '';
        $p2    = $_POST['password2'] ?? '';

        $resets = $this->model('PasswordResetModel');
        $empId  = $resets->empleadoPorToken($token);
        if ($empId === null) {
            $this->view('pages/restablecer', ['token' => $token, 'valido' => false, 'err' => null]);
            return;
        }
        if (strlen($p1) < 6)  redirect('login/restablecer/' . $token . '?err=corta');
        if ($p1 !== $p2)      redirect('login/restablecer/' . $token . '?err=coinciden');

        $this->model('EmpleadoModel')->cambiarPassword($empId, $p1);
        $resets->marcarUsado($token);
        redirect('login?reset=1');
    }

    /** Envía el enlace por SMTP; si no hay correo configurado, lo deja en storage (modo dev). */
    private function enviarEnlace(array $emp, string $link): void {
        $empresa  = ajuste('nombre_empresa', APP_NAME);
        $nombre   = trim(($emp['nombre'] ?? '') . ' ' . ($emp['apellidos'] ?? ''));
        $autoload = RUTA_APP . '/../vendor/autoload.php';

        if (file_exists($autoload)) {
            require_once $autoload;
            require_once RUTA_APP . '/services/Mailer.php';
            if (Mailer::configurado()) {
                try {
                    $mail = Mailer::crear();
                    $mail->addAddress($emp['email'], $nombre);
                    $mail->isHTML(true);
                    $mail->Subject = 'Recuperación de contraseña · ' . $empresa;
                    $mail->Body =
                        '<div style="font-family:Arial,sans-serif;color:#1E293B;">'
                        . '<p>Hola <b>' . e($emp['nombre'] ?? '') . '</b>,</p>'
                        . '<p>Has solicitado restablecer tu contraseña en <b>' . e($empresa) . '</b>. '
                        . 'Pulsa el botón (válido durante 1 hora):</p>'
                        . '<p><a href="' . e($link) . '" style="display:inline-block;background:#1A649C;color:#fff;'
                        . 'text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:bold;">Restablecer contraseña</a></p>'
                        . '<p style="font-size:13px;color:#64748B;">Si no has sido tú, ignora este correo.</p>'
                        . '<p style="font-size:12px;color:#94A3B8;word-break:break-all;">' . e($link) . '</p></div>';
                    $mail->AltBody = "Restablece tu contraseña (válido 1 hora): $link";
                    $mail->send();
                    return;
                } catch (\Throwable $e) { /* cae al modo dev */ }
            }
        }

        // Modo dev (sin SMTP): deja el enlace en storage/ (bloqueado por web) y en el log.
        $dir = RUTA_APP . '/../storage';
        if (is_dir($dir) || @mkdir($dir, 0775, true)) {
            @file_put_contents(
                $dir . '/recuperacion_DEV.txt',
                date('Y-m-d H:i:s') . '  ' . $emp['email'] . "\n" . $link . "\n\n",
                FILE_APPEND
            );
        }
        error_log("[FichaPro] Enlace de recuperación para {$emp['email']}: {$link}");
    }
}
