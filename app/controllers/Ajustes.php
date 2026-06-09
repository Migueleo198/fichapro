<?php

class Ajustes extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        $this->view('inc/header', ['title' => 'Ajustes']);
        $this->view('pages/ajustes', ['ajustes' => $this->model('AjusteModel')->getAll()]);
        $this->view('inc/footer');
    }

    public function guardar() {
        $d = $this->input();
        $this->model('AjusteModel')->guardar($d['valores'] ?? []);
        $this->json(['success' => true]);
    }
}
