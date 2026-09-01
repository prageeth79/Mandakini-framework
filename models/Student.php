<?php

namespace app\models;

use app\core\db\mysql\DBModel;

class Student extends DBModel
{
    public static function tableName(): string
    {
        return 'students';
    }

    public function calculate(): bool
    {
        // Implement any calculations or logic specific to the Student model here
        return true;
    }

    public function rules(): array
    {
        return [
            
        ];
    }
}