<?php

namespace app\models;

use app\core\db\MySqlDBModel;

class Certificate extends MySqlDBModel{

    public function tableName(): string{
        return "certificate";
    }

    public function calculate():bool{
        return true;
    }
}