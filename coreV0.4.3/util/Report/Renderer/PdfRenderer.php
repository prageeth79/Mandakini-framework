<?php
namespace app\core\util\Report\Renderer;
use app\core\util\Report;
use RuntimeException;
class PdfRenderer {
    public function render(Report $report): string {
        if (!class_exists('Dompdf\\Dompdf')) throw new RuntimeException('Dompdf is not installed. Run: composer require dompdf/dompdf');
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($report->html(), 'UTF-8');
        $dompdf->setPaper($report->getPageSize(), $report->getPageOrientation());
        $dompdf->render();
        if ($report->getShowPageNumbers()) {
            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
            $w = $canvas->get_width();
            $h = $canvas->get_height();
            $canvas->page_text($w - 110, $h - 22, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.35,0.35,0.35]);
        }
        return $dompdf->output();
    }
}
