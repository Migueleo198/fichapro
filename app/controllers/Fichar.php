<?php

class Fichar extends Controller {

    public function __construct() { requireAuth(); }

    public function index() {
        $m   = $this->model('FichajeModel');
        $emp = currentEmpId();
        $abierto   = $m->getAbierto($emp);
        $descanso  = $abierto ? $m->getDescansoAbierto((int)$abierto['id']) : false;
        $descansos = $abierto ? $m->getDescansos((int)$abierto['id']) : [];
        $hoy       = $m->getByEmpleado($emp, date('Y-m-d'), date('Y-m-d'));

        $this->view('inc/header', ['title' => 'Fichar']);
        $this->view('pages/fichar', [
            'abierto'   => $abierto,
            'descanso'  => $descanso,
            'descansos' => $descansos,
            'hoy'       => $hoy,
            'horasMes'  => $m->horasMes($emp, date('Y-m')),
        ]);
        $this->view('inc/footer');
    }

    public function entrada() {
        try {
            $id = $this->model('FichajeModel')->abrir(currentEmpId());
            $this->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function salida() {
        $m = $this->model('FichajeModel');
        $f = $m->getAbierto(currentEmpId());
        if (!$f) $this->json(['success' => false, 'message' => 'No tienes ningún fichaje abierto']);
        $m->cerrar((int)$f['id']);
        $this->json(['success' => true]);
    }

    public function descanso() {
        $m = $this->model('FichajeModel');
        $f = $m->getAbierto(currentEmpId());
        if (!$f) $this->json(['success' => false, 'message' => 'Debes fichar la entrada primero']);
        $d = $this->input();
        try {
            if ($m->getDescansoAbierto((int)$f['id'])) {
                $m->finalizarDescanso((int)$f['id']);
                $this->json(['success' => true, 'estado' => 'fin']);
            } else {
                $m->iniciarDescanso((int)$f['id'], $d['motivo'] ?? 'Descanso');
                $this->json(['success' => true, 'estado' => 'inicio']);
            }
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
