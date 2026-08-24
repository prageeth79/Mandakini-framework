<?php
namespace app\core\a0_3\middlewares;
use app\core\a0_3\Controller;

abstract class BaseMiddleware {
    protected Controller $controller;

    public function setController(Controller $controller) {
        $this->controller = $controller;
    }

    abstract public function execute();
}