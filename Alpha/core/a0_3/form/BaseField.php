<?php

namespace app\core\a0_3\form;

use app\core\a_03\Model;

abstract class BaseField{
    abstract public function renderInput(): string;
}
     