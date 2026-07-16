<?php

namespace app\core\form;

use app\core\Model;

abstract class BaseField{
    abstract public function renderInput(): string;
}
     