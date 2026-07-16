<?php
namespace app\core\middlewares;
use app\core\Controller;

abstract class BaseMiddleware {
    protected Controller $controller;

    public function setController(Controller $controller) {
        $this->controller = $controller;
    }

    abstract public function execute();
}