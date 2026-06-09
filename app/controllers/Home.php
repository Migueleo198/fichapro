<?php

class Home extends Controller {

    public function __construct() { requireAuth(); }

    public function index() {
        if (!isAdmin()) { $this->empleado(); return; }

        // ── Panel de administrador ──────────────────────────────
        $fichaje  = $this->model('FichajeModel');
        $empleado = $this->model('EmpleadoModel');
        $db       = (new Database())->conectar();

        $pend = $db->query("SELECT
            (SELECT COUNT(*) FROM vacaciones  WHERE estado='pendiente') AS vacaciones,
            (SELECT COUNT(*) FROM ausencias   WHERE estado='pendiente') AS ausencias,
            (SELECT COUNT(*) FROM incidencias WHERE estado='pendiente') AS incidencias
        ")->fetch();

        $this->view('inc/header', ['title' => 'Inicio']);
        $this->view('pages/home', [
            'emp'        => $empleado->contar(),
            'hoy'        => $fichaje->statsHoy(),
            'trabajando' => $fichaje->trabajandoAhora(),
            'pendientes' => $pend,
        ]);
        $this->view('inc/footer');
    }

    /** Inicio del empleado: sus fichajes + alta/consulta de incidencias. */
    private function empleado() {
        $emp = currentEmpId();
        $fi  = $this->model('FichajeModel');
        $inc = $this->model('IncidenciaModel');

        $hoy   = date('Y-m-d');
        $desde = date('Y-m-d', strtotime('-45 days'));
        $fichajes = $fi->getByEmpleado($emp, $desde, $hoy);

        $lunes = date('Y-m-d', strtotime('monday this week'));
        $horasSemana = 0.0;
        foreach ($fichajes as $f) {
            if ($f['fecha'] >= $lunes) $horasSemana += (float)$f['total_horas'];
        }

        $incidencias = $inc->getByEmpleado($emp);
        $incPend = 0;
        foreach ($incidencias as $i) if ($i['estado'] === 'pendiente') $incPend++;

        $this->view('inc/header', ['title' => 'Inicio']);
        $this->view('pages/home_empleado', [
            'fichajes'     => $fichajes,
            'abierto'      => $fi->getAbierto($emp),
            'horasSemana'  => round($horasSemana, 1),
            'horasMes'     => $fi->horasMes($emp, date('Y-m')),
            'totalFichajes'=> count($fichajes),
            'incPend'      => $incPend,
        ]);
        $this->view('inc/footer');
    }
}
