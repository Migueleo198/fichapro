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
}
