<?php

class Jornadas extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        $this->view('inc/header', ['title' => 'Jornadas']);
        $this->view('pages/jornadas', [
            'jornadas'  => $this->model('JornadaModel')->getAll(),
            'empleados' => $this->model('EmpleadoModel')->getAll(true),
        ]);
        $this->view('inc/footer');
    }

    public function crear() {
        $d = $this->input();
        if (empty($d['id_empleado']) || empty($d['horas_dia']) || empty($d['horas_semana'])) {
            $this->json(['success' => false, 'message' => 'Empleado y horas son obligatorios']);
        }
        $this->model('JornadaModel')->crear($d);
        $this->json(['success' => true]);
    }

    public function eliminar($id) {
        $this->model('JornadaModel')->eliminar((int)$id);
        $this->json(['success' => true]);
    }
}
