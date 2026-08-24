<?php

namespace app\core\a0_2\form;

use app\core\a0_2\Model;

abstract class BaseField{
    abstract public function renderInput(): string;
}
     