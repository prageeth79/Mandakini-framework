<?php
namespace app\core\util\Report;

use app\core\util\Report;

/**
 * Small HTTP helper. If your MVC framework has a Response class, you can
 * replace these methods with Application::$app->response calls.
 */
class ReportResponse
{
    public static function inlinePdf(Report $report, string $filename = 'report.pdf'): never
    {
        self::send($report->pdf(), 'application/pdf', $filename, 'inline');
    }

    public static function download(Report $report, string $format, ?string $filename = null): never
    {
        $format = strtolower($format);
        $defaults = ['pdf'=>'report.pdf','html'=>'report.html','csv'=>'report.csv','json'=>'report.json','excel'=>'report.xlsx','xlsx'=>'report.xlsx'];
        $types = ['pdf'=>'application/pdf','html'=>'text/html; charset=UTF-8','csv'=>'text/csv; charset=UTF-8','json'=>'application/json','excel'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $methods = ['pdf'=>'pdf','html'=>'html','csv'=>'csv','json'=>'json','excel'=>'excel','xlsx'=>'excel'];
        if (!isset($methods[$format])) throw new \InvalidArgumentException('Unsupported report format: '.$format);
        $filename ??= $defaults[$format];
        self::send($report->{$methods[$format]}(), $types[$format], $filename, 'attachment');
    }

    private static function send(string $content, string $type, string $filename, string $disposition): never
    {
        if (headers_sent()) throw new \RuntimeException('Cannot send report because headers were already sent.');
        header('Content-Type: '.$type);
        header('Content-Disposition: '.$disposition.'; filename="'.basename($filename).'"');
        header('Content-Length: '.strlen($content));
        echo $content;
        exit;
    }
}
