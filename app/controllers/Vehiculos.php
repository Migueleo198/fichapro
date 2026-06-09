<?php

class Vehiculos extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        $this->view('inc/header', ['title' => 'Vehículos']);
        $this->view('pages/vehiculos', [
            'lista'     => $this->model('VehiculoModel')->getAll(),
            'empleados' => $this->model('EmpleadoModel')->getAll(true),
        ]);
        $this->view('inc/footer');
    }

    public function crear() {
        $d = $this->input();
        if (empty($d['id_empleado']) || empty($d['matricula'])) {
            $this->json(['success' => false, 'message' => 'Empleado y matrícula son obligatorios']);
        }
        $this->model('VehiculoModel')->crear($d);
        $this->json(['success' => true]);
    }

    public function eliminar($id) {
        $this->model('VehiculoModel')->eliminar((int)$id);
        $this->json(['success' => true]);
    }
}
