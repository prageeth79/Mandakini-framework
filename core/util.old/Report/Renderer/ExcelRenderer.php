<?php
namespace app\core\util\Report\Renderer;
use app\core\util\Report;
use RuntimeException;
class ExcelRenderer {
    public function render(Report $report): string {
        if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) throw new RuntimeException('PhpSpreadsheet is not installed. Run: composer require phpoffice/phpspreadsheet');
        $spreadsheet=new \PhpOffice\PhpSpreadsheet\Spreadsheet(); $sheet=$spreadsheet->getActiveSheet(); $sheet->setTitle(substr(preg_replace('/[^A-Za-z0-9 ]/','',$report->getTitle())?:'Report',0,31));
        $columns=$report->getColumns(); $col=1;
        foreach($columns as $c){$cell=$sheet->getCellByColumnAndRow($col,1);$cell->setValue($c['label']);$cell->getStyle()->getFont()->setBold(true);if($c['width']!==null)$sheet->getColumnDimensionByColumn($col)->setWidth(is_numeric($c['width'])?(float)$c['width']:15);$col++;}
        $rowNum=2; foreach($report->getProcessedRows() as $row){$col=1;foreach($columns as $c){$sheet->setCellValueByColumnAndRow($col,$rowNum,$report->getRowValue($row,$c['key']));$col++;}$rowNum++;}
        $sheet->freezePane('A2'); $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $writer=new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet); ob_start();$writer->save('php://output');$data=ob_get_clean();$spreadsheet->disconnectWorksheets();return $data;
    }
}
