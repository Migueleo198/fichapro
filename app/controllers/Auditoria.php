<?php

class Auditoria extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        $this->view('inc/header', ['title' => 'Auditoría']);
        $this->view('pages/auditoria', ['lista' => $this->model('AuditoriaModel')->getAll()]);
        $this->view('inc/footer');
    }
}
