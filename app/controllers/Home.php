<?php

class Home extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        $fichaje  = $this->model('FichajeModel');
        $empleado = $this->model('EmpleadoModel');
        $db       = (new Database())->conectar();

        $pend = $db->query("SELECT
            (SELECT COUNT(*) FROM vacaciones  WHERE estado='pendiente') AS vacaciones,
            (SELECT COUNT(*) FROM ausencias   WHERE estado='pendiente') AS ausencias,
            (SELECT COUNT(*) FROM incidencias WHERE estado='pendiente') AS incidencias
        ")->fetch();

        $this->view('inc/header', ['title' => 'Dashboard']);
        $this->view('pages/home', [
            'emp'          => $empleado->contar(),
            'hoy'          => $fichaje->statsHoy(),
            'trabajando'   => $fichaje->trabajandoAhora(),
            'pendientes'   => $pend,
        ]);
        $this->view('inc/footer');
    }
}
