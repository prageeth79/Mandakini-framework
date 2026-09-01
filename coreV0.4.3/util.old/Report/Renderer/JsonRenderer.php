<?php
namespace app\core\util\Report\Renderer;
use app\core\util\Report;
class JsonRenderer { public function render(Report $report): string { return json_encode($report->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR); } }
