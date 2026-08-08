<?php

namespace app\models;

use app\core\db\MySqlDBModel;

class Certificate extends MySqlDBModel{

    public function tableName(): string{
        return "certificate";
    }
}