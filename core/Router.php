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

    public function resolve() {
        $method = $this->request->method();
        $path = $this->request->getPath(); 
        $callback = $this->routes[$method][$path] ?? false;
        // Check if the route exists $callback can be used instead of $this->routes[$method][$path]
        // if (!$callback === false) {
        if ($callback !== false) { 
            if(is_string($callback)) {
                return $this->renderView($callback);
            }else {
                 if(is_array($callback)) {
                    Application::$app->setController(new $callback[0]());
                    $callback[0] = Application::$app->getController();
                    Application::$app->getController()->action = $callback[1];

                    foreach (Application::$app->getController()->getMiddlewares() as $middleware) {
                        $middleware->execute();
                    }
                }
                return call_user_func($callback, $this->request);
            }       
        }else {
            //return $this->renderContent("<h1>Not Found</h1>");
            //return $this->renderView("_404");
            throw new exceptions\NotFoundException();
        }

       

        return call_user_func($callback);
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