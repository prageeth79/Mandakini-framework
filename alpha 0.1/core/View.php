<?php

namespace app\core;

class View {
    public string $title = '';

    public function renderView($view, $params = []) {
        $viewContent = $this->renderOnlyView($view, $params);
        $layoutContent = $this->layoutContent();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent() {
        ob_start();
        include_once Application::$ROOT_DIR . "/views/layout/" . Application::$app->layout . ".php";
        return ob_get_clean();
    }

    public function renderOnlyView($view, $params) {
        foreach ($params as $key => $value) {
            $$key = $value;
           
        }        
        ob_start();
        include_once Application::$ROOT_DIR . "/views/" . $view . ".php";
        $view = ob_get_clean();
        return $view;
    }

    /**
     * Return a web-accessible asset path (relative to the public document root).
     *
     * @param string $path
     * @return string
     */
    public function asset(string $path): string {
        // Compute a base path so the app can be served from a subdirectory
        // (for example: http://example.com/mandakini3/public/).
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        // dirname('/mandakini3/public/index.php') => '/mandakini3/public'
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
        // If dirname returned '.' or empty, fall back to root
        if ($base === '.' || $base === '\\' || $base === '') {
            $base = '';
        }
        return $base . '/' . ltrim($path, '/');
    }
}