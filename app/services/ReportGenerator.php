<?php

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Genera el PDF del informe individual de un empleado (para adjuntar al correo).
 * Requiere que el autoload de Composer ya esté cargado.
 */
class ReportGenerator {

    /**
     * @param array $datos  ['empresa','empleado','desde','hasta','resumen','dias','compensaciones']
     * @return string       Contenido binario del PDF.
     */
    public static function pdfEmpleado(array $datos): string {
        extract($datos);

        ob_start();
        require RUTA_APP . '/views/pdf/informe_empleado_pdf.php';
        $html = ob_get_clean();

        $opt = new Options();
        $opt->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
