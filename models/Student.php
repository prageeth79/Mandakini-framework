<?php

namespace app\models;

use app\core\db\MySqlDBModel;

class Student extends MySqlDBModel
{
    public function tableName(): string
    {
        return 'students';
    }
}