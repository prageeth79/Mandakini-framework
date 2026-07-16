<?php

namespace app\core;

class Request {
    public function getPath() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        // Remove base folder (for example /public) when the app is served
        // from a subfolder so registered routes can be written as 
        // '/register' but still work when accessed as '/public/register'.
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseFolder = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseFolder !== '' && $baseFolder !== '/' && strpos($path, $baseFolder) === 0) {
            $path = substr($path, strlen($baseFolder));
            if ($path === '') {
                $path = '/';
            }
        }

        // Fallback: if the app is served under a literal /public folder
        // but SCRIPT_NAME didn't reflect that, also strip the '/public' prefix.
        if (strpos($path, '/public') === 0) {
            $path = substr($path, strlen('/public')) ?: '/';
        }

        return $path;
    }

    public function method() {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet() {
        return $this->method() === 'get';
    }

    public function isPost() {
        return $this->method() === 'post';
    }

    public function getBody() {
        $body = [];
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }

    public function getValues(){
        $values = [];
         foreach ($_GET as $key => $value) {
            $values['get'][$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
         }
         foreach ($_POST as $key => $value) {
            $values['post'][$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $values;
    }
}