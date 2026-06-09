<?php

class Tareas extends Controller {

    public function __construct() { requireAuth(); }

    public function index() {
        $m = $this->model('TareaModel');
        $this->view('inc/header', ['title' => 'Tareas']);
        $this->view('pages/tareas', [
            'lista'   => isAdmin() ? $m->getAll() : $m->getByEmpleado(currentEmpId()),
            'tipos'   => $m->tipos(),
            'esAdmin' => isAdmin(),
        ]);
        $this->view('inc/footer');
    }

    public function crear() {
        $d = $this->input();
        if (empty($d['titulo'])) $this->json(['success' => false, 'message' => 'El título es obligatorio']);
        try {
            $this->model('TareaModel')->crear(currentEmpId(), $d);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function estado($id, $estado) {
        $this->model('TareaModel')->setEstado((int)$id, $estado);
        $this->json(['success' => true]);
    }

    public function eliminar($id) {
        $this->model('TareaModel')->eliminar((int)$id);
        $this->json(['success' => true]);
    }
}
