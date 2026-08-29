<?php

namespace app\core\util\Report\Renderer;

use app\core\util\Report;

class HtmlRenderer
{
    public function render(Report $report): string
    {
        $columns = array_values(array_filter($report->getColumns(), fn($c) => ($c['visible'] ?? true) !== false));
        $styles = $report->getStyles();
        $groups = $report->getGroups();
        $html = '<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        $html .= '<title>'.$this->e($report->getTitle()).'</title><style>'.$this->css($report).'</style></head><body>';
        $html .= '<div class="report-header">';
        if (($logo = $this->logo($report)) !== '') $html .= $logo;
        if ($report->getHeader() !== '') $html .= '<div class="header-text">'.nl2br($this->e($report->getHeader())).'</div>';
        $html .= '<h1>'.$this->e($report->getTitle()).'</h1>';
        if ($report->getShowGeneratedMeta()) $html .= '<div class="meta">Generated: '.$this->e($report->getDateGenerated()).' &nbsp; | &nbsp; By: '.$this->e($report->getGeneratedBy()).'</div>';
        $html .= '</div>';
        if ($report->getSummary()) $html .= $this->summary($report->getSummary(), 'Summary');
        $html .= '<table><thead><tr>';
        foreach ($columns as $c) $html .= '<th style="'.$this->cellStyle($c).'">'.$this->e($c['label']).'</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($groups as $group) {
            if ($report->getGroupBy() !== null) $html .= '<tr class="group-row"><td colspan="'.count($columns).'">'.$this->e((string)$group['key']).'</td></tr>';
            foreach ($group['rows'] as $row) {
                $html .= '<tr>';
                foreach ($columns as $c) {
                    $value = $this->formatValue($report->getRowValue($row, $c['key']), $c);
                    $extra = $this->conditionalStyle($report, $c['key'], $report->getRowValue($row, $c['key']));
                    $html .= '<td style="'.$this->cellStyle($c).$extra.'">'.$this->e($value).'</td>';
                }
                $html .= '</tr>';
            }
            if ($report->getGroupBy() !== null && $group['calculations']) $html .= $this->calculationRow($columns, $group['calculations'], 'Group Total');
        }
        $html .= '</tbody></table>';
        if ($report->calculateAggregates()) $html .= $this->summary($report->calculateAggregates(), 'Grand Totals');
        if ($report->getFooter() !== '') $html .= '<div class="footer">'.nl2br($this->e($report->getFooter())).'</div>';
        return $html.'</body></html>';
    }

    private function calculationRow(array $columns, array $calculations, string $label): string
    {
        $text = $label;
        foreach ($calculations as $name => $value) $text .= ' | '.$name.': '.$this->stringify($value);
        return '<tr class="calculation-row"><td colspan="'.count($columns).'">'.$this->e($text).'</td></tr>';
    }

    private function summary(array $items, string $title): string
    {
        $html = '<div class="summary"><strong>'.$this->e($title).'</strong>';
        foreach ($items as $key => $value) $html .= '<div><strong>'.$this->e((string)$key).':</strong> '.$this->e($this->stringify($value)).'</div>';
        return $html.'</div>';
    }

    private function logo(Report $report): string
    {
        $path = $report->getLogoPath();
        $url = $report->getLogoUrl();
        if ($path && is_file($path)) {
            $mime = mime_content_type($path) ?: 'image/png';
            $data = base64_encode((string)file_get_contents($path));
            return '<div class="logo"><img src="data:'.$this->e($mime).';base64,'.$data.'"></div>';
        }
        if ($url) return '<div class="logo"><img src="'.$this->e($url).'" alt="Logo"></div>';
        return '';
    }

    private function formatValue(mixed $value, array $column): string
    {
        if (($column['formatter'] ?? null) instanceof \Closure) return (string)($column['formatter'])($value);
        return match ($column['format'] ?? null) {
            'number' => is_numeric($value) ? number_format((float)$value, 2) : $this->stringify($value),
            'integer' => is_numeric($value) ? number_format((float)$value, 0) : $this->stringify($value),
            'currency' => is_numeric($value) ? number_format((float)$value, 2) : $this->stringify($value),
            'percent' => is_numeric($value) ? number_format((float)$value, 2). '%' : $this->stringify($value),
            'date' => $value ? date('Y-m-d', strtotime((string)$value)) : '',
            'datetime' => $value ? date('Y-m-d H:i:s', strtotime((string)$value)) : '',
            default => $this->stringify($value),
        };
    }

    private function conditionalStyle(Report $report, string $column, mixed $value): string
    {
        foreach ($report->getConditionalFormats() as $rule) {
            if ($rule['column'] !== $column) continue;
            $match = match ($rule['operator']) {
                '=' => $value == $rule['value'], '!=' , '<>' => $value != $rule['value'],
                '>' => $value > $rule['value'], '<' => $value < $rule['value'],
                '>=' => $value >= $rule['value'], '<=' => $value <= $rule['value'],
                'LIKE' => stripos((string)$value, (string)$rule['value']) !== false,
                'IN' => in_array($value, (array)$rule['value'], true), default => false,
            };
            if ($match) {
                $css = '';
                foreach ($rule['styles'] as $key => $val) $css .= $key.':'.$val.';';
                return $css;
            }
        }
        return '';
    }

    private function cellStyle(array $column): string
    {
        $s = 'text-align:'.($column['align'] ?? 'left').';';
        if (!empty($column['width'])) $s .= 'width:'.htmlspecialchars((string)$column['width'], ENT_QUOTES, 'UTF-8').';';
        return $s;
    }
    private function stringify(mixed $v): string { if ($v === null) return ''; if (is_bool($v)) return $v ? 'Yes' : 'No'; return is_scalar($v) ? (string)$v : (json_encode($v, JSON_UNESCAPED_UNICODE) ?: ''); }
    private function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

    private function css(Report $report): string
    {
        $s = $report->getStyles();
        $font = $s['font_family'] ?? 'Arial, sans-serif'; $size = $s['font_size'] ?? '12px'; $accent = $s['accent'] ?? '#333';
        $headerBg = $s['header_background'] ?? '#eeeeee';
        $striped = $report->getStripedRows() ? 'tbody tr:nth-child(even){background:#fafafa}' : '';
        return 'body{font-family:'.$font.';font-size:'.$size.';margin:20px;color:#222}.report-header{margin-bottom:18px;border-bottom:2px solid '.$accent.';padding-bottom:10px}.logo img{max-height:70px;max-width:240px}h1{font-size:24px;margin:5px 0}.header-text{font-weight:bold;margin-bottom:8px}.meta{font-size:11px;color:#666}table{width:100%;border-collapse:collapse;margin-top:18px}thead{display:table-header-group}th,td{border:1px solid #ccc;padding:7px;vertical-align:top}th{background:'.$headerBg.';font-weight:bold}.group-row td{background:#e8e8e8;font-weight:bold}.calculation-row td{background:#f4f4f4;font-weight:bold}.summary{margin-top:18px;padding:10px;border:1px solid #ddd;background:#f7f7f7}.summary div{margin:3px 0}.footer{margin-top:20px;padding-top:8px;border-top:1px solid #ddd;font-size:10px;color:#666}'.$striped;
    }
}
