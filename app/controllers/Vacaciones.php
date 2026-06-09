<?php

class Vacaciones extends Controller {

    public function __construct() { requireAuth(); }

    public function index() {
        $m = $this->model('VacacionModel');
        $this->view('inc/header', ['title' => 'Vacaciones']);
        $this->view('pages/vacaciones', [
            'lista'  => isAdmin() ? $m->getAll() : $m->getByEmpleado(currentEmpId()),
            'esAdmin'=> isAdmin(),
        ]);
        $this->view('inc/footer');
    }

    public function crear() {
        $d = $this->input();
        if (empty($d['fecha_inicio']) || empty($d['fecha_fin'])) {
            $this->json(['success' => false, 'message' => 'Indica las fechas de inicio y fin']);
        }
        if ($d['fecha_fin'] < $d['fecha_inicio']) {
            $this->json(['success' => false, 'message' => 'La fecha fin no puede ser anterior a la de inicio']);
        }
        $this->model('VacacionModel')->crear(currentEmpId(), $d);
        $this->json(['success' => true]);
    }

    public function estado($id, $estado) {
        requireAdmin();
        $this->model('VacacionModel')->setEstado((int)$id, $estado);
        $this->json(['success' => true]);
    }

    public function eliminar($id) {
        $this->model('VacacionModel')->eliminar((int)$id);
        $this->json(['success' => true]);
    }
}
