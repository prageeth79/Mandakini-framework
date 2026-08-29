<?php
namespace app\core\util\Report\Renderer;
use app\core\util\Report;
class CsvRenderer {
    public function render(Report $report): string {
        $stream=fopen('php://temp','w+'); if($stream===false) throw new \RuntimeException('Unable to create CSV stream.');
        $columns=$report->getColumns(); fputcsv($stream,array_map(fn($c)=>$c['label'],$columns));
        foreach($report->getProcessedRows() as $row){$values=[];foreach($columns as $c)$values[]=$report->getRowValue($row,$c['key']);fputcsv($stream,$values);}
        if($report->calculateAggregates()){fputcsv($stream,[]);foreach($report->calculateAggregates() as $k=>$v)fputcsv($stream,[$k,$v]);}
        rewind($stream);$csv=stream_get_contents($stream);fclose($stream);return "\xEF\xBB\xBF".($csv?:'');
    }
}
