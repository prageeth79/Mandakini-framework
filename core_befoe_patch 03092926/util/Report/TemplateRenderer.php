<?php
namespace app\core\util\Report;

use app\core\util\Report;
use RuntimeException;

class TemplateRenderer
{
    public function render(Report $report): string
    {
        $template = $report->getTemplate();
        $path = $report->resolveTemplatePath();
        if (!is_file($path)) {
            throw new RuntimeException("Report template not found: {$template}");
        }

        $columns = array_values(array_filter($report->getColumns(), fn(array $c) => ($c['visible'] ?? true) !== false));
        $rows = $report->getProcessedRows();
        $groups = $report->getGroups();
        $calculations = $report->calculateAggregates();
        $summary = $report->getSummary();
        $metadata = $report->getMetadata();
        $styles = $report->getStyles();
        $parameters = $report->getParameters();
        $title = $report->getTitle();
        $generatedBy = $report->getGeneratedBy();
        $generated = $report->getDateGenerated();
        $header = $report->getHeader();
        $footer = $report->getFooter();
        $logo = $report->getLogoPath() ?: $report->getLogoUrl();
        $pageSize = $report->getPageSize();
        $orientation = $report->getPageOrientation();
        $showGeneratedMeta = $report->getShowGeneratedMeta();
        $showPageNumbers = $report->getShowPageNumbers();
        $repeatTableHeader = $report->getRepeatTableHeader();
        $stripedRows = $report->getStripedRows();
        $conditionalFormats = $report->getConditionalFormats();
        $groupBy = $report->getGroupBy();

        $value = fn($row, string $key) => $report->getRowValue($row, $key);
        $format = fn($value, array $column) => $report->formatValue($value, $column);
        $cellStyle = fn($row, array $column) => $report->getCellStyles($row, $column);
        $escape = fn($value) => htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $asset = fn(?string $path) => $report->assetToDataUri($path);
        $hasGrouping = $groupBy !== null;

        ob_start();
        include $path;
        return (string)ob_get_clean();
    }
}
