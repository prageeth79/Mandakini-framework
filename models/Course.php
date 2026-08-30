<?php

namespace app\models;

use app\core\db\DBModel;
use app\core\db\mysql\MySqlDBModel;

class Course extends MySqlDBModel {

    public CourseCategory $category;
    public string $course_name = '';
    public string $course_year = '';
    public string $course_duration = '';
    public string $course_conducted_by = '';
    public string $course_begin_date = '';
    public string $course_end_date = '';
    public string $course_category = '';
    public string $course_id = '';
    

    public function __construct(){
        $this->category = new CourseCategory();
        parent::__construct();   
    }


    public function loadData(array $data): void {
        parent::loadData($data);
        if($this->course_category != ''){
            $this->category = $this->category::findOne(['category_id' => $this->course_category]);
        }
    }

    
    public static function tableName(): string {
        return 'courses';
    } 

    public  function rules(): array {
        $list = $this->category::findAll();
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

    public function calculate():bool{
        return true;
    }

}