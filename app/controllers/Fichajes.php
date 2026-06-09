<?php

class Fichajes extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        $f = [
            'empleado' => $_GET['empleado'] ?? '',
            'desde'    => $_GET['desde'] ?? date('Y-m-01'),
            'hasta'    => $_GET['hasta'] ?? date('Y-m-t'),
            'estado'   => $_GET['estado'] ?? '',
        ];
        $this->view('inc/header', ['title' => 'Fichajes']);
        $this->view('pages/fichajes', [
            'lista'     => $this->model('FichajeModel')->getAll($f),
            'empleados' => $this->model('EmpleadoModel')->getAll(),
            'f'         => $f,
        ]);
        $this->view('inc/footer');
    }

    public function validar($id) {
        $this->model('FichajeModel')->validar((int)$id);
        $this->json(['success' => true]);
    }

    public function eliminar($id) {
        $this->model('FichajeModel')->eliminar((int)$id);
        $this->json(['success' => true]);
    }
}
