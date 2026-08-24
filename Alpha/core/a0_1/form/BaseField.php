<?php

namespace app\core\a0_1\form;

use app\core\Model;

abstract class BaseField{
    abstract public function renderInput(): string;
}
     