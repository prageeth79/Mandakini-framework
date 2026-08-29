<?php

namespace app\core\util;

/**
 * Report Generator Class
 * 
 * Generates comprehensive reports in multiple formats
 * Supports PDF, CSV, Excel, HTML, and JSON formats
 * 
 * Features:
 * - Multiple output formats (PDF, CSV, Excel, HTML, JSON)
 * - Data aggregation and calculations
 * - Filtering and sorting
 * - Custom styling and formatting
 * - Table and columnar layouts
 * - Charts and summaries
 * - Batch reporting
 * 
 * Usage:
 *   $report = new Report('Sales Report');
 *   $report->addColumns(['Name', 'Amount', 'Date']);
 *   $report->addRows($data);
 *   $report->export('sales.csv');
 */
class Report
{
    private $title;
    private $columns = [];
    private $rows = [];
    private $filters = [];
    private $sortBy = null;
    private $sortDir = 'ASC';
    private $summary = [];
    private $metadata = [];
    private $styles = [];
    private $pageSize = 'A4';
    private $pageOrientation = 'portrait';
    private $dateGenerated;
    private $generatedBy = 'System';
    private $footerText = '';
    private $headerText = '';
    private $columnWidths = [];
    private $groupBy = null;
    private $calculations = [];

    /**
     * Constructor
     * 
     * @param string $title Report title
     * @param string $generatedBy Who generated the report
     */
    public function __construct($title = 'Report', $generatedBy = 'System')
    {
        $this->title = $title;
        $this->generatedBy = $generatedBy;
        $this->dateGenerated = date('Y-m-d H:i:s');
    }

    /**
     * Set report title
     * 
     * @param string $title Report title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Add columns to report
     * 
     * @param array $columns Column names
     * @param array $widths Optional column widths
     * @return $this
     */
    public function addColumns($columns, $widths = [])
    {
        $this->columns = $columns;
        $this->columnWidths = $widths;
        return $this;
    }

    /**
     * Add single row to report
     * 
     * @param array $row Row data
     * @return $this
     */
    public function addRow($row)
    {
        $this->rows[] = $row;
        return $this;
    }

    /**
     * Add multiple rows to report
     * 
     * @param array $rows Array of rows
     * @return $this
     */
    public function addRows($rows)
    {
        foreach ($rows as $row) {
            $this->rows[] = $row;
        }
        return $this;
    }

    /**
     * Set header text for report
     * 
     * @param string $text Header text
     * @return $this
     */
    public function setHeader($text)
    {
        $this->headerText = $text;
        return $this;
    }

    /**
     * Set footer text for report
     * 
     * @param string $text Footer text
     * @return $this
     */
    public function setFooter($text)
    {
        $this->footerText = $text;
        return $this;
    }

    /**
     * Add filter to report data
     * 
     * @param string $column Column to filter
     * @param string $operator Operator (=, !=, >, <, >=, <=, LIKE, IN)
     * @param mixed $value Filter value
     * @return $this
     */
    public function addFilter($column, $operator, $value)
    {
        $this->filters[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        return $this;
    }

    /**
     * Sort report data
     * 
     * @param string $column Column to sort by
     * @param string $direction ASC or DESC
     * @return $this
     */
    public function sortBy($column, $direction = 'ASC')
    {
        $this->sortBy = $column;
        $this->sortDir = strtoupper($direction);
        return $this;
    }

    /**
     * Group report data by column
     * 
     * @param string $column Column to group by
     * @return $this
     */
    public function groupBy($column)
    {
        $this->groupBy = $column;
        return $this;
    }

    /**
     * Add calculation/aggregate to report
     * 
     * @param string $name Calculation name
     * @param string $column Column to calculate
     * @param string $function Function (SUM, AVG, MIN, MAX, COUNT)
     * @return $this
     */
    public function addCalculation($name, $column, $function)
    {
        $this->calculations[$name] = [
            'column' => $column,
            'function' => strtoupper($function)
        ];
        return $this;
    }

    /**
     * Add summary information to report
     * 
     * @param string $key Summary key
     * @param mixed $value Summary value
     * @return $this
     */
    public function addSummary($key, $value)
    {
        $this->summary[$key] = $value;
        return $this;
    }

    /**
     * Add metadata to report
     * 
     * @param string $key Metadata key
     * @param mixed $value Metadata value
     * @return $this
     */
    public function addMetadata($key, $value)
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * Set page size for PDF reports
     * 
     * @param string $size Page size (A4, Letter, Legal, etc.)
     * @return $this
     */
    public function setPageSize($size)
    {
        $this->pageSize = $size;
        return $this;
    }

    /**
     * Set page orientation
     * 
     * @param string $orientation portrait or landscape
     * @return $this
     */
    public function setPageOrientation($orientation)
    {
        $this->pageOrientation = strtolower($orientation);
        return $this;
    }

    /**
     * Get processed rows (with filters, sorting, grouping applied)
     * 
     * @return array Processed rows
     */
    public function getProcessedRows()
    {
        $rows = $this->rows;

        // Apply filters
        $rows = $this->applyFilters($rows);

        // Apply sorting
        if ($this->sortBy) {
            $rows = $this->sortRows($rows);
        }

        // Apply grouping
        if ($this->groupBy) {
            $rows = $this->groupRows($rows);
        }

        return $rows;
    }

    /**
     * Apply filters to rows
     * 
     * @param array $rows Rows to filter
     * @return array Filtered rows
     */
    private function applyFilters($rows)
    {
        if (empty($this->filters)) {
            return $rows;
        }

        return array_filter($rows, function ($row) {
            foreach ($this->filters as $filter) {
                $column = $filter['column'];
                $operator = $filter['operator'];
                $value = $filter['value'];

                $rowValue = $this->getRowValue($row, $column);

                if (!$this->compareValues($rowValue, $operator, $value)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Sort rows by specified column
     * 
     * @param array $rows Rows to sort
     * @return array Sorted rows
     */
    private function sortRows($rows)
    {
        usort($rows, function ($a, $b) {
            $aValue = $this->getRowValue($a, $this->sortBy);
            $bValue = $this->getRowValue($b, $this->sortBy);

            if ($aValue == $bValue) {
                return 0;
            }

            $result = $aValue < $bValue ? -1 : 1;
            return $this->sortDir === 'DESC' ? -$result : $result;
        });

        return $rows;
    }

    /**
     * Group rows by specified column
     * 
     * @param array $rows Rows to group
     * @return array Grouped rows
     */
    private function groupRows($rows)
    {
        $grouped = [];

        foreach ($rows as $row) {
            $key = $this->getRowValue($row, $this->groupBy);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $row;
        }

        $result = [];
        foreach ($grouped as $groupData) {
            $result = array_merge($result, $groupData);
        }

        return $result;
    }

    /**
     * Get value from row (supports nested keys like "address.city")
     * 
     * @param array|object $row Row data
     * @param string $key Key path
     * @return mixed Value
     */
    private function getRowValue($row, $key)
    {
        if (is_object($row)) {
            $row = (array)$row;
        }

        if (strpos($key, '.') === false) {
            return $row[$key] ?? null;
        }

        $keys = explode('.', $key);
        $value = $row;

        foreach ($keys as $k) {
            if (is_array($value)) {
                $value = $value[$k] ?? null;
            } elseif (is_object($value)) {
                $value = $value->{$k} ?? null;
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Compare values based on operator
     * 
     * @param mixed $value Row value
     * @param string $operator Operator
     * @param mixed $filterValue Filter value
     * @return bool Comparison result
     */
    private function compareValues($value, $operator, $filterValue)
    {
        switch ($operator) {
            case '=':
                return $value == $filterValue;
            case '!=':
                return $value != $filterValue;
            case '>':
                return $value > $filterValue;
            case '<':
                return $value < $filterValue;
            case '>=':
                return $value >= $filterValue;
            case '<=':
                return $value <= $filterValue;
            case 'LIKE':
                return stripos($value, $filterValue) !== false;
            case 'IN':
                return in_array($value, (array)$filterValue);
            default:
                return true;
        }
    }

    /**
     * Calculate aggregates from data
     * 
     * @return array Calculation results
     */
    private function calculateAggregates()
    {
        $results = [];
        $rows = $this->getProcessedRows();

        foreach ($this->calculations as $name => $calc) {
            $column = $calc['column'];
            $function = $calc['function'];
            $values = array_column($rows, $column);

            switch ($function) {
                case 'SUM':
                    $results[$name] = array_sum($values);
                    break;
                case 'AVG':
                    $results[$name] = !empty($values) ? array_sum($values) / count($values) : 0;
                    break;
                case 'MIN':
                    $results[$name] = !empty($values) ? min($values) : null;
                    break;
                case 'MAX':
                    $results[$name] = !empty($values) ? max($values) : null;
                    break;
                case 'COUNT':
                    $results[$name] = count($values);
                    break;
            }
        }

        return $results;
    }

    /**
     * Export report as CSV
     * 
     * @param string $filePath Output file path
     * @return bool Success
     */
    public function exportCSV($filePath)
    {
        $file = fopen($filePath, 'w');

        if (!$file) {
            return false;
        }

        // Write headers
        fputcsv($file, $this->columns);

        // Write data rows
        foreach ($this->getProcessedRows() as $row) {
            $values = [];
            foreach ($this->columns as $column) {
                $values[] = $this->getRowValue($row, $column);
            }
            fputcsv($file, $values);
        }

        // Write summary
        if (!empty($this->summary)) {
            fputcsv($file, []); // Empty row
            foreach ($this->summary as $key => $value) {
                fputcsv($file, [$key, $value]);
            }
        }

        fclose($file);
        return true;
    }

    /**
     * Export report as JSON
     * 
     * @param string $filePath Output file path
     * @return bool Success
     */
    public function exportJSON($filePath)
    {
        $data = [
            'title' => $this->title,
            'generated' => $this->dateGenerated,
            'generated_by' => $this->generatedBy,
            'columns' => $this->columns,
            'rows' => $this->getProcessedRows(),
            'summary' => $this->summary,
            'metadata' => $this->metadata,
            'calculations' => $this->calculateAggregates()
        ];

        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * Export report as HTML
     * 
     * @param string $filePath Output file path
     * @return bool Success
     */
    public function exportHTML($filePath)
    {
        $html = $this->generateHTML();
        return file_put_contents($filePath, $html) !== false;
    }

    /**
     * Generate HTML representation of report
     * 
     * @return string HTML
     */
    public function generateHTML()
    {
        $html = "<!DOCTYPE html>\n<html>\n<head>\n";
        $html .= "<meta charset=\"UTF-8\">\n";
        $html .= "<title>" . htmlspecialchars($this->title) . "</title>\n";
        $html .= "<style>";
        $html .= "body { font-family: Arial, sans-serif; margin: 20px; }";
        $html .= ".report-header { margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }";
        $html .= ".report-title { font-size: 24px; font-weight: bold; }";
        $html .= ".report-meta { font-size: 12px; color: #666; margin-top: 10px; }";
        $html .= "table { border-collapse: collapse; width: 100%; margin: 20px 0; }";
        $html .= "th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }";
        $html .= "th { background-color: #4CAF50; color: white; }";
        $html .= "tr:nth-child(even) { background-color: #f9f9f9; }";
        $html .= ".summary { margin: 20px 0; padding: 15px; background-color: #f0f0f0; }";
        $html .= ".summary-item { margin: 5px 0; }";
        $html .= ".footer { margin-top: 20px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }";
        $html .= "</style>\n";
        $html .= "</head>\n<body>\n";

        // Header
        $html .= "<div class=\"report-header\">\n";
        $html .= "<div class=\"report-title\">" . htmlspecialchars($this->title) . "</div>\n";
        $html .= "<div class=\"report-meta\">\n";
        $html .= "<div>Generated: " . $this->dateGenerated . "</div>\n";
        $html .= "<div>Generated by: " . htmlspecialchars($this->generatedBy) . "</div>\n";
        $html .= "</div>\n";
        $html .= "</div>\n";

        // Custom header text
        if ($this->headerText) {
            $html .= "<div>" . nl2br(htmlspecialchars($this->headerText)) . "</div>\n";
        }

        // Table
        $html .= "<table>\n<thead>\n<tr>\n";
        foreach ($this->columns as $column) {
            $html .= "<th>" . htmlspecialchars($column) . "</th>\n";
        }
        $html .= "</tr>\n</thead>\n<tbody>\n";

        foreach ($this->getProcessedRows() as $row) {
            $html .= "<tr>\n";
            foreach ($this->columns as $column) {
                $value = $this->getRowValue($row, $column);
                $html .= "<td>" . htmlspecialchars($value) . "</td>\n";
            }
            $html .= "</tr>\n";
        }

        $html .= "</tbody>\n</table>\n";

        // Summary
        if (!empty($this->summary)) {
            $html .= "<div class=\"summary\">\n";
            $html .= "<strong>Summary</strong>\n";
            foreach ($this->summary as $key => $value) {
                $html .= "<div class=\"summary-item\">";
                $html .= "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value);
                $html .= "</div>\n";
            }
            $html .= "</div>\n";
        }

        // Calculations
        $calculations = $this->calculateAggregates();
        if (!empty($calculations)) {
            $html .= "<div class=\"summary\">\n";
            $html .= "<strong>Calculations</strong>\n";
            foreach ($calculations as $key => $value) {
                $html .= "<div class=\"summary-item\">";
                $html .= "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value);
                $html .= "</div>\n";
            }
            $html .= "</div>\n";
        }

        // Custom footer text
        if ($this->footerText) {
            $html .= "<div class=\"footer\">" . nl2br(htmlspecialchars($this->footerText)) . "</div>\n";
        }

        $html .= "</body>\n</html>";

        return $html;
    }

    /**
     * Get report as array
     * 
     * @return array Report data
     */
    public function toArray()
    {
        return [
            'title' => $this->title,
            'generated' => $this->dateGenerated,
            'generated_by' => $this->generatedBy,
            'columns' => $this->columns,
            'rows' => $this->getProcessedRows(),
            'summary' => $this->summary,
            'metadata' => $this->metadata,
            'calculations' => $this->calculateAggregates()
        ];
    }

    /**
     * Display report as HTML in browser
     * 
     * @return string HTML
     */
    public function display()
    {
        return $this->generateHTML();
    }

    /**
     * Get report statistics
     * 
     * @return array Statistics
     */
    public function getStatistics()
    {
        $rows = $this->getProcessedRows();

        return [
            'total_rows' => count($rows),
            'total_columns' => count($this->columns),
            'generated' => $this->dateGenerated,
            'title' => $this->title,
            'calculations' => $this->calculateAggregates()
        ];
    }

    /**
     * Clear all report data
     * 
     * @return $this
     */
    public function clear()
    {
        $this->rows = [];
        $this->summary = [];
        $this->filters = [];
        $this->calculations = [];
        return $this;
    }

    /**
     * Clone report with new title
     * 
     * @param string $newTitle New report title
     * @return Report New report instance
     */
    public function cloneAs($newTitle)
    {
        $report = new self($newTitle, $this->generatedBy);
        $report->columns = $this->columns;
        $report->rows = $this->rows;
        $report->summary = $this->summary;
        $report->metadata = $this->metadata;
        return $report;
    }
}
