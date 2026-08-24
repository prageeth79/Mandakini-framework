<?php
namespace app\core\a0_3\form;
use app\core\a0_3\Model;

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