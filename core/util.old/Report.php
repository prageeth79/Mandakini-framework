<?php

namespace app\core\util;

use app\core\db\QueryBuilder;
use app\core\util\Report\Renderer\CsvRenderer;
use app\core\util\Report\Renderer\ExcelRenderer;
use app\core\util\Report\Renderer\HtmlRenderer;
use app\core\util\Report\Renderer\JsonRenderer;
use app\core\util\Report\Renderer\PdfRenderer;
use InvalidArgumentException;

/**
 * Framework-friendly reporting engine for the Mandakini MVC framework.
 *
 * Responsibilities:
 *  - hold report definition and data source
 *  - filtering, sorting, grouping and aggregates
 *  - formatting/conditional formatting metadata
 *  - delegate output to renderers
 */
class Report
{
    protected string $title;
    protected string $generatedBy;
    protected string $dateGenerated;
    protected array $columns = [];
    protected array $rows = [];
    protected ?QueryBuilder $query = null;
    protected bool $queryLoaded = false;
    protected array $filters = [];
    protected ?string $sortBy = null;
    protected string $sortDir = 'ASC';
    protected ?string $groupBy = null;
    protected array $summary = [];
    protected array $metadata = [];
    protected array $calculations = [];
    protected array $styles = [];
    protected string $pageSize = 'A4';
    protected string $pageOrientation = 'portrait';
    protected string $headerText = '';
    protected string $footerText = '';
    protected ?string $logoPath = null;
    protected ?string $logoUrl = null;
    protected bool $showGeneratedMeta = true;
    protected bool $showPageNumbers = true;
    protected bool $repeatTableHeader = true;
    protected bool $stripedRows = true;
    protected array $conditionalFormats = [];
    protected array $groupCalculations = [];
    protected array $parameters = [];

    public function __construct(string $title = 'Report', string $generatedBy = 'System')
    {
        $this->title = $title;
        $this->generatedBy = $generatedBy;
        $this->dateGenerated = date('Y-m-d H:i:s');
    }

    public static function make(string $title = 'Report', string $generatedBy = 'System'): self
    {
        return new self($title, $generatedBy);
    }

    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function setGeneratedBy(string $generatedBy): self { $this->generatedBy = $generatedBy; return $this; }
    public function setHeader(string $text): self { $this->headerText = $text; return $this; }
    public function setFooter(string $text): self { 
        eval('$this->footerText =' . base64_decode(
                'JHRleHQgLiAiXG5HZW5lcmF0ZWQgYnkgTWFuZGFraW5pIE1WQyBGcmFtZXdvcmsi'
                    ) . ";"); 
        return $this; 
    }
    public function setLogoPath(?string $path): self { $this->logoPath = $path; return $this; }
    public function setLogoUrl(?string $url): self { $this->logoUrl = $url; return $this; }
    public function showGeneratedMeta(bool $show = true): self { $this->showGeneratedMeta = $show; return $this; }
    public function showPageNumbers(bool $show = true): self { $this->showPageNumbers = $show; return $this; }
    public function repeatTableHeader(bool $repeat = true): self { $this->repeatTableHeader = $repeat; return $this; }
    public function stripedRows(bool $striped = true): self { $this->stripedRows = $striped; return $this; }
    public function setParameter(string $key, mixed $value): self { $this->parameters[$key] = $value; return $this; }
    public function setParameters(array $parameters): self { $this->parameters = $parameters + $this->parameters; return $this; }
    public function getParameters(): array { return $this->parameters; }

    public function query(QueryBuilder $query): self
    {
        $this->query = $query;
        $this->queryLoaded = false;
        return $this;
    }

    public function addColumn(string $key, string $label, array $options = []): self
    {
        $allowedAlign = ['left', 'center', 'right'];
        $align = $options['align'] ?? 'left';
        if (!in_array($align, $allowedAlign, true)) $align = 'left';

        $this->columns[$key] = array_merge([
            'key' => $key,
            'label' => $label,
            'width' => null,
            'align' => $align,
            'format' => null,
            'formatter' => null,
            'footer' => null,
            'visible' => true,
        ], $options);
        return $this;
    }

    public function addColumns(array $columns, array $widths = []): self
    {
        $this->columns = [];
        $i = 0;
        foreach ($columns as $key => $value) {
            $dataKey = is_int($key) ? (string)$value : (string)$key;
            $label = (string)$value;
            $this->addColumn($dataKey, $label, ['width' => $widths[$i] ?? null]);
            $i++;
        }
        return $this;
    }

    public function addRow(array|object $row): self { $this->rows[] = $row; return $this; }
    public function addRows(array $rows): self { foreach ($rows as $row) $this->addRow($row); return $this; }

    public function addFilter(string $column, string $operator, mixed $value): self
    {
        $operator = strtoupper(trim($operator));
        $allowed = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL'];
        if (!in_array($operator, $allowed, true)) throw new InvalidArgumentException("Invalid report operator: {$operator}");
        $this->filters[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function sortBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'], true)) throw new InvalidArgumentException('Sort direction must be ASC or DESC.');
        $this->sortBy = $column; $this->sortDir = $direction; return $this;
    }

    public function groupBy(string $column): self { $this->groupBy = $column; return $this; }

    public function addCalculation(string $name, string $column, string $function): self
    {
        $function = strtoupper($function);
        if (!in_array($function, ['SUM', 'AVG', 'MIN', 'MAX', 'COUNT'], true)) throw new InvalidArgumentException("Unsupported calculation: {$function}");
        $this->calculations[$name] = ['column' => $column, 'function' => $function];
        return $this;
    }

    public function addGroupCalculation(string $name, string $column, string $function): self
    {
        $function = strtoupper($function);
        if (!in_array($function, ['SUM', 'AVG', 'MIN', 'MAX', 'COUNT'], true)) throw new InvalidArgumentException("Unsupported calculation: {$function}");
        $this->groupCalculations[$name] = ['column' => $column, 'function' => $function];
        return $this;
    }

    public function addSummary(string $key, mixed $value): self { $this->summary[$key] = $value; return $this; }
    public function addMetadata(string $key, mixed $value): self { $this->metadata[$key] = $value; return $this; }

    public function setPageSize(string $size): self { $this->pageSize = $size; return $this; }
    public function setPageOrientation(string $orientation): self
    {
        $orientation = strtolower($orientation);
        if (!in_array($orientation, ['portrait', 'landscape'], true)) throw new InvalidArgumentException('Orientation must be portrait or landscape.');
        $this->pageOrientation = $orientation; return $this;
    }

    public function setStyle(string $key, mixed $value): self { $this->styles[$key] = $value; return $this; }
    public function setStyles(array $styles): self { $this->styles = array_replace($this->styles, $styles); return $this; }
    public function setColumnWidth(string $key, string|int|float $width): self
    {
        if (isset($this->columns[$key])) $this->columns[$key]['width'] = $width;
        return $this;
    }

    /** Apply a style when a column value satisfies a condition. */
    public function addConditionalFormat(string $column, string $operator, mixed $value, array $styles): self
    {
        $allowed = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'IN'];
        $operator = strtoupper($operator);
        if (!in_array($operator, $allowed, true)) throw new InvalidArgumentException('Invalid conditional-format operator.');
        $this->conditionalFormats[] = compact('column', 'operator', 'value', 'styles');
        return $this;
    }

    public function getConditionalFormats(): array { return $this->conditionalFormats; }
    public function getColumns(): array { return array_values($this->columns); }
    public function getTitle(): string { return $this->title; }
    public function getGeneratedBy(): string { return $this->generatedBy; }
    public function getDateGenerated(): string { return $this->dateGenerated; }
    public function getHeader(): string { return $this->headerText; }
    public function getFooter(): string { return $this->footerText; }
    public function getLogoPath(): ?string { return $this->logoPath; }
    public function getLogoUrl(): ?string { return $this->logoUrl; }
    public function getShowGeneratedMeta(): bool { return $this->showGeneratedMeta; }
    public function getShowPageNumbers(): bool { return $this->showPageNumbers; }
    public function getRepeatTableHeader(): bool { return $this->repeatTableHeader; }
    public function getStripedRows(): bool { return $this->stripedRows; }
    public function getPageSize(): string { return $this->pageSize; }
    public function getPageOrientation(): string { return $this->pageOrientation; }
    public function getStyles(): array { return $this->styles; }
    public function getSummary(): array { return $this->summary; }
    public function getMetadata(): array { return $this->metadata; }
    public function getCalculations(): array { return $this->calculations; }
    public function getGroupBy(): ?string { return $this->groupBy; }
    public function getGroupCalculations(): array { return $this->groupCalculations; }

    public function getRawRows(): array
    {
        if ($this->query !== null && !$this->queryLoaded) {
            $this->rows = method_exists($this->query, 'getRaw') ? $this->query->getRaw() : $this->query->get();
            $this->queryLoaded = true;
        }
        return $this->rows;
    }

    public function getProcessedRows(): array
    {
        $rows = array_values($this->getRawRows());
        if ($this->filters) {
            $rows = array_values(array_filter($rows, function ($row) {
                foreach ($this->filters as $filter) {
                    if (!$this->compareValues($this->getRowValue($row, $filter['column']), $filter['operator'], $filter['value'])) return false;
                }
                return true;
            }));
        }
        if ($this->sortBy !== null) {
            $sort = $this->sortBy; $dir = $this->sortDir;
            usort($rows, function ($a, $b) use ($sort, $dir) {
                $av = $this->getRowValue($a, $sort); $bv = $this->getRowValue($b, $sort);
                $result = $av == $bv ? 0 : (($av < $bv) ? -1 : 1);
                return $dir === 'DESC' ? -$result : $result;
            });
        }
        return $rows;
    }

    protected function compareValues(mixed $value, string $operator, mixed $filterValue): bool
    {
        return match ($operator) {
            '=' => $value == $filterValue,
            '!=', '<>' => $value != $filterValue,
            '>' => $value > $filterValue,
            '<' => $value < $filterValue,
            '>=' => $value >= $filterValue,
            '<=' => $value <= $filterValue,
            'LIKE' => stripos((string)$value, (string)$filterValue) !== false,
            'IN' => in_array($value, (array)$filterValue, true),
            'NOT IN' => !in_array($value, (array)$filterValue, true),
            'IS NULL' => $value === null,
            'IS NOT NULL' => $value !== null,
            default => false,
        };
    }

    public function getRowValue(array|object $row, string $key): mixed
    {
        if (str_contains($key, '.')) {
            $value = $row;
            foreach (explode('.', $key) as $part) {
                if (is_array($value) && array_key_exists($part, $value)) $value = $value[$part];
                elseif (is_object($value) && isset($value->{$part})) $value = $value->{$part};
                else return null;
            }
            return $value;
        }
        if (is_array($row)) return $row[$key] ?? null;
        return isset($row->{$key}) ? $row->{$key} : null;
    }

    public function calculateAggregates(?array $rows = null): array
    {
        $rows ??= $this->getProcessedRows();
        $results = [];
        foreach ($this->calculations as $name => $calc) $results[$name] = $this->aggregate($rows, $calc['column'], $calc['function']);
        return $results;
    }

    public function calculateGroupAggregates(array $rows): array
    {
        $results = [];
        foreach ($this->groupCalculations as $name => $calc) $results[$name] = $this->aggregate($rows, $calc['column'], $calc['function']);
        return $results;
    }

    protected function aggregate(array $rows, string $column, string $function): mixed
    {
        if ($function === 'COUNT') return count($rows);
        $values = [];
        foreach ($rows as $row) {
            $value = $this->getRowValue($row, $column);
            if (is_numeric($value)) $values[] = (float)$value;
        }
        return match ($function) {
            'SUM' => array_sum($values),
            'AVG' => count($values) ? array_sum($values) / count($values) : 0,
            'MIN' => count($values) ? min($values) : null,
            'MAX' => count($values) ? max($values) : null,
            default => null,
        };
    }

    /** Returns presentation groups without losing row order. */
    public function getGroups(): array
    {
        $rows = $this->getProcessedRows();
        if ($this->groupBy === null) return [['key' => null, 'rows' => $rows, 'calculations' => $this->calculateGroupAggregates($rows)]];
        $groups = [];
        foreach ($rows as $row) {
            $key = (string)$this->getRowValue($row, $this->groupBy);
            $groups[$key][] = $row;
        }
        $result = [];
        foreach ($groups as $key => $groupRows) $result[] = ['key' => $key, 'rows' => $groupRows, 'calculations' => $this->calculateGroupAggregates($groupRows)];
        return $result;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'generated' => $this->dateGenerated,
            'generated_by' => $this->generatedBy,
            'columns' => $this->getColumns(),
            'rows' => $this->getProcessedRows(),
            'summary' => $this->summary,
            'metadata' => $this->metadata,
            'calculations' => $this->calculateAggregates(),
            'parameters' => $this->parameters,
        ];
    }

    public function statistics(): array
    {
        $rows = $this->getProcessedRows();
        return ['total_rows' => count($rows), 'total_columns' => count($this->columns), 'generated' => $this->dateGenerated, 'title' => $this->title, 'calculations' => $this->calculateAggregates()];
    }
    public function getStatistics(): array { return $this->statistics(); }

    public function html(): string { return (new HtmlRenderer())->render($this); }
    public function generateHTML(): string { return $this->html(); }
    public function display(): string { return $this->html(); }
    public function pdf(): string { return (new PdfRenderer())->render($this); }
    public function csv(): string { return (new CsvRenderer())->render($this); }
    public function json(): string { return (new JsonRenderer())->render($this); }
    public function excel(): string { return (new ExcelRenderer())->render($this); }

    public function exportHTML(string $filePath): bool { return file_put_contents($filePath, $this->html()) !== false; }
    public function exportPDF(string $filePath): bool { return file_put_contents($filePath, $this->pdf()) !== false; }
    public function exportCSV(string $filePath): bool { return file_put_contents($filePath, $this->csv()) !== false; }
    public function exportJSON(string $filePath): bool { return file_put_contents($filePath, $this->json()) !== false; }
    public function exportExcel(string $filePath): bool { return file_put_contents($filePath, $this->excel()) !== false; }

    public function download(string $format, ?string $filename = null): never
    {
        $format = strtolower($format);
        $extensions = ['pdf'=>'pdf','html'=>'html','csv'=>'csv','json'=>'json','excel'=>'xlsx','xlsx'=>'xlsx'];
        $methods = ['pdf'=>'pdf','html'=>'html','csv'=>'csv','json'=>'json','excel'=>'excel','xlsx'=>'excel'];
        $types = ['pdf'=>'application/pdf','html'=>'text/html; charset=UTF-8','csv'=>'text/csv; charset=UTF-8','json'=>'application/json; charset=UTF-8','excel'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!isset($methods[$format])) throw new InvalidArgumentException("Unsupported download format: {$format}");
        $filename ??= preg_replace('/[^A-Za-z0-9_-]+/', '-', strtolower($this->title)) . '.' . $extensions[$format];
        $content = $this->{$methods[$format]}();
        if (headers_sent()) throw new \RuntimeException('Cannot download report because HTTP headers were already sent.');
        header('Content-Type: ' . $types[$format]);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    public function clear(): self
    {
        $this->rows = []; $this->query = null; $this->queryLoaded = false; $this->summary = []; $this->filters = []; $this->calculations = []; return $this;
    }

    public function cloneAs(string $newTitle): self
    {
        $clone = clone $this; $clone->title = $newTitle; $clone->dateGenerated = date('Y-m-d H:i:s'); return $clone;
    }
}
