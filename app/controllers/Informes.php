<?php

class Informes extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        [$desde, $hasta] = $this->rango();
        $m = $this->model('InformeModel');
        $this->view('inc/header', ['title' => 'Informes']);
        $this->view('pages/informes', [
            'desde'   => $desde, 'hasta' => $hasta,
            'totales' => $m->totales($desde, $hasta),
            'tabla'   => $m->porEmpleado($desde, $hasta),
            'porDia'  => $m->porDia($desde, $hasta),
        ]);
        $this->view('inc/footer');
    }

    public function pdf() {
        [$desde, $hasta] = $this->rango();
        $m = $this->model('InformeModel');
        $totales = $m->totales($desde, $hasta);
        $tabla   = $m->porEmpleado($desde, $hasta);
        $empresa = ajuste('nombre_empresa', 'Mi Empresa');

        $autoload = RUTA_APP . '/../vendor/autoload.php';
        if (!file_exists($autoload)) {
            die('Falta la librería de PDF. Ejecuta "composer install" en la carpeta del proyecto.');
        }
        require_once $autoload;

        ob_start();
        require RUTA_APP . '/views/pdf/informe_pdf.php';
        $html = ob_get_clean();

        $opt = new \Dompdf\Options();
        $opt->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("informe_{$desde}_{$hasta}.pdf", ['Attachment' => true]);
    }

    private function rango(): array {
        return [$_GET['desde'] ?? date('Y-m-01'), $_GET['hasta'] ?? date('Y-m-t')];
    }
}
