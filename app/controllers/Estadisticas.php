<?php

class Estadisticas extends Controller {

    public function __construct() { requireAdmin(); }

    /** /estadisticas sin método → vista por defecto. */
    public function index() { redirect('estadisticas/resumen'); }

    private function filtroFechas(): array {
        $desde = $_GET['desde'] ?? date('Y-m-01');
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        return [$desde, $hasta];
    }

    public function resumen() {
        $m = $this->model('EstadisticasModel');
        $this->view('inc/header', ['title' => 'Estadísticas – Resumen']);
        $this->view('pages/estadisticasResumen', [
            'resumen'       => $m->getResumenGeneral(),
            'fichajesMeses' => $m->getFichajesUltimosMeses(6),
            'diaSemana'     => $m->getFichajesPorDiaSemana(),
            'topEmpleados'  => $m->getTopEmpleadosHoras(5),
        ]);
        $this->view('inc/footer');
    }

    public function fichajes() {
        [$desde, $hasta] = $this->filtroFechas();
        $m = $this->model('EstadisticasModel');
        $this->view('inc/header', ['title' => 'Estadísticas – Fichajes']);
        $this->view('pages/estadisticasFichajes', [
            'desde'           => $desde,
            'hasta'           => $hasta,
            'resumenFichajes' => $m->getResumenFichajes($desde, $hasta),
            'porEstado'       => $m->getFichajesPorEstado($desde, $hasta),
            'diarios'         => $m->getFichajesDiarios($desde, $hasta),
            'porEmpleado'     => $m->getFichajesPorEmpleado($desde, $hasta),
        ]);
        $this->view('inc/footer');
    }

    public function horas() {
        [$desde, $hasta] = $this->filtroFechas();
        $m = $this->model('EstadisticasModel');
        $this->view('inc/header', ['title' => 'Estadísticas – Horas']);
        $this->view('pages/estadisticasHoras', [
            'desde'         => $desde,
            'hasta'         => $hasta,
            'resumenHoras'  => $m->getResumenHoras($desde, $hasta),
            'horasDiarias'  => $m->getHorasPorDia($desde, $hasta),
            'horasEmpleado' => $m->getHorasPorEmpleado($desde, $hasta),
        ]);
        $this->view('inc/footer');
    }

    public function retrasos() {
        [$desde, $hasta] = $this->filtroFechas();
        $m = $this->model('EstadisticasModel');
        $this->view('inc/header', ['title' => 'Estadísticas – Retrasos']);
        $this->view('pages/estadisticasRetrasos', [
            'desde'            => $desde,
            'hasta'            => $hasta,
            'resumenRetrasos'  => $m->getResumenRetrasos($desde, $hasta),
            'retrasosDiarios'  => $m->getRetrasosPorDia($desde, $hasta),
            'retrasosEmpleado' => $m->getRetrasosPorEmpleado($desde, $hasta),
            'horaEntrada'      => $m->getHoraEntradaMedia($desde, $hasta),
        ]);
        $this->view('inc/footer');
    }

    public function actividad() {
        [$desde, $hasta] = $this->filtroFechas();
        $m = $this->model('EstadisticasModel');
        $this->view('inc/header', ['title' => 'Estadísticas – Actividad']);
        $this->view('pages/estadisticasActividad', [
            'desde'            => $desde,
            'hasta'            => $hasta,
            'resumenActividad' => $m->getResumenActividad($desde, $hasta),
            'actividadDiaria'  => $m->getActividadDiaria($desde, $hasta),
            'porHora'          => $m->getActividadPorHora($desde, $hasta),
            'ausenciasVacas'   => $m->getAusenciasYVacaciones($desde, $hasta),
            'presencia'        => $m->getPresenciaPorEmpleado($desde, $hasta),
        ]);
        $this->view('inc/footer');
    }
}
