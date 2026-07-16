<?php
namespace app\models;
use app\core\db\MySqlDBModel;

class CourseCategory extends MySqlDBModel{
    public string $category_id = '';
    public string $category_name = '';

    public static function tableName(): string{
        return 'course_category';
    }

    public function rules():array{
        return [
            'category_id' => [self::RULE_REQUIRED],
            'category_name' => [self::RULE_REQUIRED],
        ];
    }

    public function labels(): array{
        return [
            'category_id' => 'Category ID',
            'category_name' => 'Category Name',
        ];
    }
}