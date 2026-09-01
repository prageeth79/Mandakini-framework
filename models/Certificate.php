<?php

namespace app\models;

use app\core\db\DBModel;

class Certificate extends DBModel{

    public function tableName(): string{
        return "certificate";
    }

    public function calculate():bool{
        return true;
    }
}