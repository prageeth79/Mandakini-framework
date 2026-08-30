<?php
namespace app\core\util\Report\Renderer;
use app\core\util\Report;
class CsvRenderer {
    public function render(Report $report): string {
        $h = fopen('php://temp', 'r+');
        $columns = array_values(array_filter($report->getColumns(), fn($c) => ($c['visible'] ?? true) !== false));
        fputcsv($h, array_map(fn($c) => $c['label'], $columns));
        foreach ($report->getProcessedRows() as $row) fputcsv($h, array_map(fn($c) => $report->formatValue($report->getRowValue($row, $c['key']), $c), $columns));
        if ($report->getSummary()) { fputcsv($h, []); foreach ($report->getSummary() as $k=>$v) fputcsv($h, [$k,$v]); }
        if ($report->calculateAggregates()) { fputcsv($h, []); foreach ($report->calculateAggregates() as $k=>$v) fputcsv($h, [$k,$v]); }
        rewind($h); $out=stream_get_contents($h); fclose($h); return $out;
    }
}
