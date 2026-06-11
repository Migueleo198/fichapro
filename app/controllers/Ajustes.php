<?php

class Ajustes extends Controller {



    /** Todos los usuarios acceden a "Mi cuenta"; las pestañas de admin se protegen aparte. */
    public function __construct() { requireAuth(); }

    // ============================================================
    // VISTA PRINCIPAL (pestañas)
    // ============================================================
    public function index() {
        $esAdmin = isAdmin();

        $config = [];
        foreach ($this->model('AjusteModel')->getAll() as $a) {
            $config[$a['clave']] = $a['valor'];
        }

        $this->view('inc/header', ['title' => 'Configuración']);
        $this->view('pages/ajustes', [
            'perfil'  => $this->model('EmpleadoModel')->getById(currentEmpId()),
            'config'  => $config,
            'tipos'   => $esAdmin ? $this->model('TareaModel')->tipos() : [],
            'esAdmin' => $esAdmin,
            'tab'     => $_GET['tab'] ?? 'cuenta',
            'ok'      => $_GET['ok']  ?? null,
            'error'   => $_GET['err'] ?? null,
        ]);
        $this->view('inc/footer');
    }

    // ============================================================
    // MI CUENTA — actualizar perfil
    // ============================================================
    public function actualizarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('ajustes?tab=cuenta');

        $ok = $this->model('EmpleadoModel')->actualizarPerfil(currentEmpId(), [
            'nombre'           => trim($_POST['nombre']           ?? ''),
            'apellidos'        => trim($_POST['apellidos']        ?? ''),
            'email'            => trim($_POST['email']            ?? ''),
            'telefono'         => trim($_POST['telefono']         ?? ''),
            'fecha_nacimiento' => $_POST['fecha_nacimiento']      ?? '',
        ]);

        if ($ok && !empty($_POST['nombre'])) {
            $_SESSION['emp_nombre'] = trim($_POST['nombre']);
        }
        redirect('ajustes?tab=cuenta&' . ($ok ? 'ok=perfil' : 'err=perfil'));
    }

    // ============================================================
    // PERFIL (modal del sidebar) — leer / guardar vía AJAX
    // ============================================================
    public function perfil() {
        header('Content-Type: application/json; charset=utf-8');
        $u = $this->model('EmpleadoModel')->getById(currentEmpId());
        if (!$u) { echo json_encode(['success' => false]); exit; }
        unset($u['password']);
        $u['foto_url'] = !empty($u['foto']) ? url($u['foto']) : null;
        echo json_encode(['success' => true, 'data' => $u]);
        exit;
    }

    // ============================================================
    // FOTO DE PERFIL — subir / eliminar (AJAX)
    // ============================================================
    public function subirFoto() {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen.']); exit;
        }
        $f = $_FILES['foto'];
        if ($f['size'] > 3 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'La imagen supera el máximo de 3 MB.']); exit;
        }
        $info   = @getimagesize($f['tmp_name']);
        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!$info || !isset($extMap[$info['mime']])) {
            echo json_encode(['success' => false, 'message' => 'Formato no válido. Usa JPG, PNG, WEBP o GIF.']); exit;
        }

        $dir = RUTA_APP . '/../public/uploads/avatars';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear la carpeta de subidas.']); exit;
        }

        $nombre = 'emp_' . currentEmpId() . '_' . bin2hex(random_bytes(6)) . '.' . $extMap[$info['mime']];
        $rel    = 'uploads/avatars/' . $nombre;
        if (!@move_uploaded_file($f['tmp_name'], $dir . '/' . $nombre)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar la imagen.']); exit;
        }

        $emp = $this->model('EmpleadoModel');
        $old = $emp->getById(currentEmpId())['foto'] ?? null;
        $emp->actualizarFoto(currentEmpId(), $rel);
        if ($old && str_starts_with($old, 'uploads/avatars/')) {
            @unlink(RUTA_APP . '/../public/' . $old);
        }
        $_SESSION['emp_foto'] = $rel;

        echo json_encode(['success' => true, 'message' => 'Foto de perfil actualizada.', 'foto' => url($rel)]);
        exit;
    }

    public function eliminarFoto() {
        header('Content-Type: application/json; charset=utf-8');
        $emp = $this->model('EmpleadoModel');
        $old = $emp->getById(currentEmpId())['foto'] ?? null;
        $emp->actualizarFoto(currentEmpId(), null);
        if ($old && str_starts_with($old, 'uploads/avatars/')) {
            @unlink(RUTA_APP . '/../public/' . $old);
        }
        $_SESSION['emp_foto'] = '';
        echo json_encode(['success' => true, 'message' => 'Foto eliminada.']);
        exit;
    }

    public function guardarPerfil() {
        header('Content-Type: application/json; charset=utf-8');
        $ok = $this->model('EmpleadoModel')->actualizarPerfil(currentEmpId(), [
            'nombre'           => trim($_POST['nombre']           ?? ''),
            'apellidos'        => trim($_POST['apellidos']        ?? ''),
            'email'            => trim($_POST['email']            ?? ''),
            'telefono'         => trim($_POST['telefono']         ?? ''),
            'fecha_nacimiento' => $_POST['fecha_nacimiento']      ?? '',
        ]);
        if ($ok && !empty($_POST['nombre'])) $_SESSION['emp_nombre'] = trim($_POST['nombre']);
        echo json_encode([
            'success' => (bool) $ok,
            'message' => $ok ? 'Perfil actualizado correctamente.' : 'No se pudo guardar el perfil.',
        ]);
        exit;
    }

    // ============================================================
    // MI CUENTA — cambiar contraseña (AJAX / JSON)
    // ============================================================
    public function cambiarPassword() {
        header('Content-Type: application/json; charset=utf-8');

        $emp      = $this->model('EmpleadoModel');
        $id       = currentEmpId();
        $actual   = $_POST['password_actual']   ?? '';
        $nueva    = $_POST['password_nueva']    ?? '';
        $confirma = $_POST['password_confirma'] ?? '';

        if (!$actual || !$nueva || !$confirma) {
            echo json_encode(['ok' => false, 'msg' => 'Rellena todos los campos.']); exit;
        }
        if ($nueva !== $confirma) {
            echo json_encode(['ok' => false, 'msg' => 'La nueva contraseña y la confirmación no coinciden.']); exit;
        }
        if (strlen($nueva) < 6) {
            echo json_encode(['ok' => false, 'msg' => 'La contraseña debe tener al menos 6 caracteres.']); exit;
        }
        if (!$emp->verificarPassword($id, $actual)) {
            echo json_encode(['ok' => false, 'msg' => 'La contraseña actual es incorrecta.']); exit;
        }

        $ok = $emp->cambiarPassword($id, $nueva);
        echo json_encode([
            'ok'  => (bool) $ok,
            'msg' => $ok ? 'Contraseña cambiada correctamente.' : 'Error al guardar. Inténtalo de nuevo.',
        ]);
        exit;
    }

    // ============================================================
    // SISTEMA (admin) — guardar parámetros globales
    // ============================================================
    public function guardarSistema() {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('ajustes?tab=sistema');

        $valores = [
            'nombre_empresa'         => trim($_POST['nombre_empresa'] ?? ''),
            'hora_inicio_jornada'    => trim($_POST['hora_inicio_jornada'] ?? '08:00'),
            'umbral_retraso_minutos' => (int)   ($_POST['umbral_retraso_minutos'] ?? 30),
            'horas_jornada_defecto'  => (float) ($_POST['horas_jornada_defecto']  ?? 7.5),
            'horas_semana_defecto'   => (float) ($_POST['horas_semana_defecto']   ?? 37.5),
            'smtp_host'              => trim($_POST['smtp_host'] ?? ''),
            'smtp_port'             => (int) ($_POST['smtp_port'] ?? 587),
            'smtp_secure'           => in_array($_POST['smtp_secure'] ?? 'tls', ['tls','ssl'], true) ? $_POST['smtp_secure'] : 'tls',
            'smtp_user'             => trim($_POST['smtp_user'] ?? ''),
            'smtp_from_name'        => trim($_POST['smtp_from_name'] ?? 'FichaPro'),
        ];
        // La contraseña SMTP sólo se actualiza si se escribe una nueva (no se vacía por error).
        if (($_POST['smtp_pass'] ?? '') !== '') {
            $valores['smtp_pass'] = $_POST['smtp_pass'];
        }

        $this->model('AjusteModel')->guardar($valores);
        redirect('ajustes?tab=sistema&ok=sistema');
    }

    // ============================================================
    // TIPOS DE TAREA (admin)
    // ============================================================
    public function addTipo() {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('ajustes?tab=tipos');
        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') redirect('ajustes?tab=tipos&err=nombre_vacio');
        $this->model('TareaModel')->addTipo($nombre);
        redirect('ajustes?tab=tipos&ok=tipo_add');
    }

    public function editTipo() {
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('ajustes?tab=tipos');
        $id     = (int)  ($_POST['id']     ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        if (!$id || $nombre === '') redirect('ajustes?tab=tipos&err=datos_invalidos');
        $this->model('TareaModel')->editTipo($id, $nombre);
        redirect('ajustes?tab=tipos&ok=tipo_edit');
    }

    public function deleteTipo($id) {
        requireAdmin();
        $this->model('TareaModel')->deleteTipo((int)$id);
        redirect('ajustes?tab=tipos&ok=tipo_del');
    }
}
