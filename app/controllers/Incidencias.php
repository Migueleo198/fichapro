<?php

class Incidencias extends Controller {

    public function __construct() { requireAuth(); }

    public function index() {
        $m = $this->model('IncidenciaModel');
        $this->view('inc/header', ['title' => 'Incidencias']);
        $this->view('pages/incidencias', [
            'lista'      => isAdmin() ? $m->getAll() : $m->getByEmpleado(currentEmpId()),
            'fichajes'   => isAdmin() ? [] : $m->fichajesRecientes(currentEmpId()),
            'esAdmin'    => isAdmin(),
        ]);
        $this->view('inc/footer');
    }

    public function crear() {
        $d = $this->input();
        if (empty($d['id_fichaje']) || empty($d['mensaje'])) {
            $this->json(['success' => false, 'message' => 'Selecciona el fichaje y describe la incidencia']);
        }
        $this->model('IncidenciaModel')->crear((int)$d['id_fichaje'], $d['mensaje']);
        $this->json(['success' => true]);
    }

    public function responder($id) {
        requireAdmin();
        $d = $this->input();
        $this->model('IncidenciaModel')->responder((int)$id, $d['respuesta'] ?? '');
        $this->json(['success' => true]);
    }

    public function eliminar($id) {
        requireAdmin();
        $this->model('IncidenciaModel')->eliminar((int)$id);
        $this->json(['success' => true]);
    }
}
