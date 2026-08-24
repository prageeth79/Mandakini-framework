<?php

namespace app\core\a0_1;

use app\core\a0_1\Application;
use app\core\a0_1\db\DbModel;   


abstract class UserModel extends DbModel {
    abstract public function getDisplayName(): string;

    public function save() {
        //$this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return parent::save();
    }
}

?>