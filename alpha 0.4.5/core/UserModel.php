<?php

namespace app\core;

use app\core\Application;
use app\core\db\DbModel;   


abstract class UserModel extends DbModel {
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    abstract public function getDisplayName(): string;
    abstract public function calculate():bool;

    public function save(string $passwordField = ""):bool {
        if($passwordField == "") return false;
        $this->SavePassword($passwordField);
        return parent::save();
    }

    public function delete(array $where = [], string $statusField = ""): int {
        if($statusField == "") return -1;
        $this->$statusField = self::STATUS_DELETED;
        return parent::update();
    }

    public function SavePassword(string $passwordField): void{
        $this->$passwordField = password_hash($this->$passwordField, PASSWORD_DEFAULT);
    }

    public function deleteRecord(array $where = []): int {
        return parent::delete($where);
    }

}

?>