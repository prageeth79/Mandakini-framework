<?php

namespace app\core;

use app\core\Application;
use app\core\db\DbModel;   


abstract class UserModel extends DbModel {
    abstract public function getDisplayName(): string;

    public function save() {
        //$this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return parent::save();
    }
}

?>