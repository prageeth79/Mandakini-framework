<?php

namespace app\core;
use app\core\Application;


class Controller {
    public string $layout = 'main';
    public string $action = '';
    public string $id = 'Base';
    /*
        * @var app\core\middlewares\BaseMiddleware[]
    */
    protected array $middlewares = [];

    public function setLayout($layout) {
        Application::$app->layout = $layout;
        $this->layout = $layout;
    }


    public function render($view, $params = []) {
        Application::$app->view->title = $params['title'] ??  $_ENV['DEFAULT_APP_NAME'] ?? 'Mandakini';
        return Application::$app->view->renderView($view, $params);
    }

    public function renderViewOnly($view, $params = []) {
        Application::$app->view->title = $params['title'] ??  $_ENV['DEFAULT_APP_NAME'] ?? 'Mandakini';
        return Application::$app->view->renderOnlyView($view, $params);
    }

        public function setMiddleware($middleware) {
            $middleware->setController($this);
            $this->middlewares[] = $middleware;
        }

        public function getMiddlewares() {
            return $this->middlewares;
        }

        public function registerMiddleware($middleware) {
            $this->setMiddleware($middleware);
        }
}