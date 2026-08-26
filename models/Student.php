<?php

namespace app\models;

use app\core\db\MySqlDBModel;

class Student extends MySqlDBModel
{
    public function tableName(): string
    {
        return 'students';
    }

    public function calculate(): bool
    {
        // Implement any calculations or logic specific to the Student model here
        return true;
    }
}