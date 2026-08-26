<?php

namespace app\core;
use app\core\Application;
use app\core\Request;
use app\core\Response;

class Router {
    protected $routes = [];
    public Request $request;
    public Response $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback) {
        $this->routes['get'][$path] = $callback;        
    }

    public function post($path, $callback) {
        $this->routes['post'][$path] = $callback;
    }

    protected function matchRoute($method, $path) {
        if (isset($this->routes[$method][$path])) {
            return [$this->routes[$method][$path], []];
        }

        if (!isset($this->routes[$method])) {
            return [false, []];
        }

        foreach ($this->routes[$method] as $route => $callback) {
            if (strpos($route, '{') === false || strpos($route, '}') === false) {
                continue;
            }

            $parameterNames = [];
            $routePattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use (&$parameterNames) {
                $parameterNames[] = $matches[1];
                return '___PARAM___';
            }, $route);

            $pattern = preg_quote($routePattern, '#');
            $pattern = str_replace('___PARAM___', '([^/]+)', $pattern);

            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                $params = [];
                foreach (array_slice($matches, 1) as $index => $value) {
                    $key = $parameterNames[$index] ?? 'param' . ($index + 1);
                    $params[$key] = $value;
                    $_GET[$key] = $value;
                    $_REQUEST[$key] = $value;
                }

                return [$callback, $params];
            }
        }

        return [false, []];
    }

    public function resolve() {
        $method = $this->request->method();
        $path = $this->request->getPath(); 
        [$callback, $routeParams] = $this->matchRoute($method, $path);

        if ($callback === false) {
            throw new exceptions\NotFoundException();
        }

        if (is_string($callback)) {
            return $this->renderView($callback, $routeParams);
        }

        if (is_array($callback) && 
            is_subclass_of($callback[0], 'app\core\Controller') && 
            method_exists($callback[0], $callback[1] . 'Action') &&
            str_ends_with($callback[0], 'Controller')) {
            Application::$app->setController(new $callback[0]());
            $callback[0] = Application::$app->getController();
            Application::$app->getController()->action = $callback[1];
            $callback[1] = $callback[1] . 'Action';

            foreach (Application::$app->getController()->getMiddlewares() as $middleware) {
                $middleware->execute();
            }
        }

        return call_user_func($callback, $this->request);
    }

    public function renderView($view, $params = []) {
        return Application::$app->view->renderView($view, $params);
    }
    public function renderContent($viewContent) {
        return Application::$app->view->renderContent($viewContent);
    }

    protected function layoutContent() {
       return Application::$app->view->layoutContent();
    }

    protected function renderOnlyView($view, $params = []) {
        return Application::$app->view->renderOnlyView($view, $params);
    }
}