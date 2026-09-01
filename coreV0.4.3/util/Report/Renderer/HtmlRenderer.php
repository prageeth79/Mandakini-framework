<?php
namespace app\core\util\Report\Renderer;
use app\core\util\Report;
use app\core\util\Report\TemplateRenderer;
class HtmlRenderer {
    public function render(Report $report): string { return (new TemplateRenderer())->render($report); }
}
