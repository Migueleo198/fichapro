<?php

class Ausencias extends Controller {

    public function __construct() { requireAuth(); }

    public function index() {
        $m = $this->model('AusenciaModel');
        $this->view('inc/header', ['title' => 'Ausencias']);
        $this->view('pages/ausencias', [
            'lista'   => isAdmin() ? $m->getAll() : $m->getByEmpleado(currentEmpId()),
            'tipos'   => $m->getTipos(),
            'esAdmin' => isAdmin(),
        ]);
        $this->view('inc/footer');
    }

    public function crear() {
        $d = $this->input();
        if (empty($d['fecha_inicio'])) {
            $this->json(['success' => false, 'message' => 'Indica al menos la fecha de inicio']);
        }
        $r = $this->model('AusenciaModel')->crear(currentEmpId(), $d);
        $this->json([
            'success'           => true,
            'remunerada'        => $r['remunerada'],
            'limite_superado'   => $r['limite_superado'],
            'horas'             => $r['horas'],
            'horas_remuneradas' => $r['horas_remuneradas'],
        ]);
    }

    public function estado($id, $estado) {
        requireAdmin();
        $this->model('AusenciaModel')->setEstado((int)$id, $estado);
        $this->json(['success' => true]);
    }

    public function eliminar($id) {
        $this->model('AusenciaModel')->eliminar((int)$id);
        $this->json(['success' => true]);
    }

    // ── Catálogo de tipos (admin) ────────────────────────────────────
    public function tipos() {
        requireAdmin();
        $this->view('inc/header', ['title' => 'Tipos de ausencia']);
        $this->view('pages/tipos_ausencia', ['tipos' => $this->model('AusenciaModel')->getTipos(false)]);
        $this->view('inc/footer');
    }

    public function crearTipo() {
        requireAdmin();
        $d = $this->input();
        if (empty($d['nombre'])) $this->json(['success' => false, 'message' => 'El nombre es obligatorio']);
        $this->model('AusenciaModel')->crearTipo($d);
        $this->json(['success' => true]);
    }

    public function actualizarLimite($id) {
        requireAdmin();
        $d = $this->input();
        $ok = $this->model('AusenciaModel')->actualizarLimite((int)$id, (int)($d['horas'] ?? 0));
        $this->json(['success' => (bool)$ok]);
    }

    public function eliminarTipo($id) {
        requireAdmin();
        $this->model('AusenciaModel')->eliminarTipo((int)$id);
        $this->json(['success' => true]);
    }
}
