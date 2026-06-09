<?php

class Empleados extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        $this->view('inc/header', ['title' => 'Empleados']);
        $this->view('pages/empleados', [
            'empleados' => $this->model('EmpleadoModel')->getAll(),
        ]);
        $this->view('inc/footer');
    }

    public function crear() {
        $d = $this->input();
        if (empty($d['nombre']) || empty($d['usuario']) || empty($d['email']) || empty($d['dni']) || empty($d['password'])) {
            $this->json(['success' => false, 'message' => 'Nombre, usuario, email, DNI y contraseña son obligatorios']);
        }
        try {
            $id = $this->model('EmpleadoModel')->crear($d);
            $this->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'El usuario, email o DNI ya existe' : 'Error al crear';
            $this->json(['success' => false, 'message' => $msg]);
        }
    }

    public function actualizar($id) {
        $d = $this->input();
        if ((int)$id === currentEmpId() && ($d['rol'] ?? '') !== 'admin') {
            $this->json(['success' => false, 'message' => 'No puedes quitarte a ti mismo el rol de administrador']);
        }
        try {
            $this->model('EmpleadoModel')->actualizar((int)$id, $d);
            if (!empty($d['password'])) {
                $this->model('EmpleadoModel')->cambiarPassword((int)$id, $d['password']);
            }
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate') ? 'El usuario, email o DNI ya existe' : 'Error al guardar';
            $this->json(['success' => false, 'message' => $msg]);
        }
    }

    public function estado($id) {
        if ((int)$id === currentEmpId()) {
            $this->json(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta']);
        }
        $d = $this->input();
        $this->model('EmpleadoModel')->setActivo((int)$id, (int)($d['activo'] ?? 0));
        $this->json(['success' => true]);
    }
}
