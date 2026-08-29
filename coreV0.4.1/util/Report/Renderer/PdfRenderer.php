<?php

namespace app\core\util\Report\Renderer;

use app\core\util\Report;
use RuntimeException;

class PdfRenderer
{
    public function render(Report $report): string
    {
        if (!class_exists('Dompdf\\Dompdf')) throw new RuntimeException('Dompdf is not installed. Run: composer require dompdf/dompdf');
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isPhpEnabled', false);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml((new HtmlRenderer())->render($report), 'UTF-8');
        $dompdf->setPaper($report->getPageSize(), $report->getPageOrientation());
        $dompdf->render();
        if ($report->getShowPageNumbers()) {
            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
            $x = max(20, $canvas->get_width() - 90);
            $y = max(20, $canvas->get_height() - 25);
            $canvas->page_text($x, $y, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0,0,0]);
        }
        return $dompdf->output();
    }
}
