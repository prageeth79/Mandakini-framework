<?php
namespace app\core\util\Report;
use app\core\util\Report;
class ReportFactory {
    public static function fromArray(array $definition): Report {
        $report=Report::make($definition['title']??'Report',$definition['generated_by']??'System');
        foreach(($definition['columns']??[]) as $key=>$column){if(is_array($column))$report->addColumn($key,$column['label']??$key,$column);else $report->addColumn((string)$key,(string)$column);}
        foreach(($definition['summary']??[]) as $k=>$v)$report->addSummary($k,$v);
        foreach(($definition['metadata']??[]) as $k=>$v)$report->addMetadata($k,$v);
        if(isset($definition['page_size']))$report->setPageSize($definition['page_size']);
        if(isset($definition['orientation']))$report->setPageOrientation($definition['orientation']);
        if(isset($definition['header']))$report->setHeader($definition['header']);
        if(isset($definition['footer']))$report->setFooter($definition['footer']);
        if(isset($definition['styles']))$report->setStyles($definition['styles']);
        if(isset($definition['rows']))$report->addRows($definition['rows']);
        return $report;
    }
}
