<?php
namespace app\core\a0_2\form;
use app\core\a0_2\Model;

class Form  {
    public static function begin($action, $method) {
        echo sprintf("<form action=\"%s\" method=\"%s\"  enctype=\"multipart/form-data\">", $action, $method);
        return new Form();
    }
    public static function end() {
        echo '</form>';
    }

    public function field(Model $model, $attribute) {
        return new Field($model, $attribute);
    }
}