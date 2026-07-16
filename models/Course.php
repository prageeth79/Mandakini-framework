<?php

namespace app\models;

use app\core\db\DBModel;
use app\core\db\MySqlDBColumnsModel;

class Course extends MysqlDBColumnsModel {

    public CourseCategory $category;

    public function __construct(){
        $category = new CourseCategory();
    }

    
    public static function tableName(): string {
        return 'courses';
    } 

    public  function rules(): array {
        $list = $category::findAll();
        return [
            'course_name' => [self::RULE_REQUIRED],
            'course_year' => [self::RULE_REQUIRED, self::RULE_NUMARIC],
            'course_duration' => [self::RULE_REQUIRED],
            'course_conducted_by' => [self::RULE_REQUIRED],
            'course_begin_date' => [self::RULE_REQUIRED, self::RULE_DATE],
            'course_end_date' => [self::RULE_REQUIRED, self::RULE_DATE],
            'course_category' => [self::RULE_REQUIRED, [self::RULE_INLIST, 'list' => array_map(fn($item) => $item->course_id, $list)]],
        ];
    }

    public function labels(): array {
        return [
            'course_name' => 'Course Name',
            'course_year' => 'Year',
            'course_duration' => 'Duration',
            'course_conducted_by' => 'conducted by',
            'course_begin_date' => 'Start Date',
            'course_end_date' => 'End Date',
            'course_category' => 'Category',
        ];
    }

}