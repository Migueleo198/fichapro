<?php

class Informes extends Controller {

    public function __construct() { requireAdmin(); }

    public function index() {
        [$desde, $hasta] = $this->rango();
        $m    = $this->model('InformeModel');
        $comp = $this->model('CompensacionModel');

        $this->view('inc/header', ['title' => 'Informes']);
        $this->view('pages/informes', [
            'desde'        => $desde, 'hasta' => $hasta,
            'qs'           => $this->qs($desde, $hasta),
            'totales'      => $m->totales($desde, $hasta),
            'tabla'        => $m->porEmpleado($desde, $hasta),
            'porDia'       => $m->porDia($desde, $hasta),
            'mapaComp'     => $comp->mapaPorEmpleado($desde, $hasta),
            'compTotales'  => $comp->totales($desde, $hasta),
            'compensaciones' => $comp->getAll($desde, $hasta),
            'empleados'    => $this->model('EmpleadoModel')->getAll(true),
            'smtpOk'       => $this->smtpConfigurado(),
            'ok'           => $_GET['ok']  ?? null,
            'err'          => $_GET['err'] ?? null,
            'n'            => isset($_GET['n']) ? (int)$_GET['n'] : null,
        ]);
        $this->view('inc/footer');
    }

    public function pdf() {
        [$desde, $hasta] = $this->rango();
        $m       = $this->model('InformeModel');
        $comp    = $this->model('CompensacionModel');
        $totales = $m->totales($desde, $hasta);
        $tabla   = $m->porEmpleado($desde, $hasta);
        $mapaComp    = $comp->mapaPorEmpleado($desde, $hasta);
        $compTotales = $comp->totales($desde, $hasta);
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

    // ── Compensación de horas (alta/baja manual) ────────────────────────
    public function crearCompensacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('informes');
        [$desde, $hasta] = $this->rango();

        $empId = (int)   ($_POST['id_empleado'] ?? 0);
        $fecha = trim($_POST['fecha'] ?? '');
        $horas = (float) ($_POST['horas'] ?? 0);
        $tipo  = $_POST['tipo'] ?? 'pagada';

        if (!$empId || $fecha === '' || $horas <= 0) {
            redirect('informes?' . $this->qs($desde, $hasta) . '&err=comp_datos');
        }

        $this->model('CompensacionModel')->crear([
            'id_empleado' => $empId,
            'fecha'       => $fecha,
            'horas'       => $horas,
            'tipo'        => $tipo,
            'concepto'    => $_POST['concepto'] ?? '',
            'id_creador'  => currentEmpId(),
        ]);
        redirect('informes?' . $this->qs($desde, $hasta) . '&ok=comp_add');
    }

    public function eliminarCompensacion($id) {
        [$desde, $hasta] = $this->rango();
        $this->model('CompensacionModel')->eliminar((int)$id);
        redirect('informes?' . $this->qs($desde, $hasta) . '&ok=comp_del');
    }

    // ── Envío manual del informe del periodo por correo ─────────────────
    public function enviar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('informes');
        [$desde, $hasta] = $this->rango();

        $autoload = RUTA_APP . '/../vendor/autoload.php';
        if (!file_exists($autoload)) {
            redirect('informes?' . $this->qs($desde, $hasta) . '&err=vendor');
        }
        require_once $autoload;
        require_once RUTA_APP . '/services/enviar_informes.php';

        if (!Mailer::configurado()) {
            redirect('informes?' . $this->qs($desde, $hasta) . '&err=smtp');
        }

        $db = (new Database())->conectar();
        $r  = fp_enviar_informes($db, $desde, $hasta, 'Informe de horas');

        $estado = empty($r['errores']) ? 'ok=enviados' : 'err=envio_parcial';
        redirect('informes?' . $this->qs($desde, $hasta) . "&{$estado}&n=" . $r['enviados']);
    }

    private function smtpConfigurado(): bool {
        $autoload = RUTA_APP . '/../vendor/autoload.php';
        if (!file_exists($autoload)) return false;
        require_once $autoload;
        require_once RUTA_APP . '/services/Mailer.php';
        return Mailer::configurado();
    }

    private function rango(): array {
        return [$_GET['desde'] ?? date('Y-m-01'), $_GET['hasta'] ?? date('Y-m-t')];
    }

    private function qs(string $desde, string $hasta): string {
        return 'desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta);
    }
}
