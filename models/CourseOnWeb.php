<?php 
namespace app\models;
use app\core\db\MySqlDBModel;
use app\core\db\DBModel;
use app\core\Util;

class CourseOnWeb extends MySqlDBModel{
    public string $course_id = '';
    public string $course_name = '';
    public string $course_image_land = '';
    public string $course_image_file_land = '';
    public string $course_image_detail = '';
    public string $course_image_file_detail = '';
    public string $course_instructor = '';
    public string $course_description = '';
    public string $course_duration = '';
    public float $course_fee = 0.0;
    public string $course_contents = '';
    public string $course_category = '';
    public string $extra = '';
    public bool $is_active = true;

    public const RULE_ALLOWED_FILETYPE = 'allow File Types';

    public static function tableName(): string{
        return "courses_on_web";
    }

    public function validate() {
        if(Util::uploadFiles()->hasFile('course_image_file_land')){
            $ext = strtolower(Util::uploadFiles()->getFileExt('course_image_file_land'));
            $extArray = ['jpg', 'png', 'jpeg', 'gif', 'webp' ];
            $inList = in_array($ext, $extArray );
            
            if (!$inList) {
                $this->addErrorForRule('course_image_land', [self::RULE_ALLOWED_FILETYPE]);
            }
        }
        if(Util::uploadFiles()->hasFile('course_image_file_detail')){
            $ext = strtolower(Util::uploadFiles()->getFileExt('course_image_file_detail'));
            $extArray = ['jpg', 'png', 'jpeg', 'gif', 'webp' ];
            $inList = in_array($ext, $extArray );
            
            if (!$inList) {
                $this->addErrorForRule('course_image_detail', [self::RULE_ALLOWED_FILETYPE]);
            }
        }
        return parent::validate();
    }

    public function errorMessages() {
        $messages = parent::errorMessages();
        $messages[self::RULE_ALLOWED_FILETYPE] = "Upload file should have only extentions ('jpg', 'jpeg', 'png', 'webp') ";
        return $messages;
    }

    public function rules():array{
        return [
            'course_id' => [self::RULE_REQUIRED],
            'course_name' => [self::RULE_REQUIRED],
            'course_image_land' => [self::RULE_REQUIRED, self::RULE_ALLOWED_FILETYPE],
            'course_image_detail' => [self::RULE_REQUIRED],
            'course_instructor' => [self::RULE_REQUIRED],
            'course_contents' => [self::RULE_REQUIRED],
            'course_description' => [self::RULE_REQUIRED],
            'course_duration' => [self::RULE_REQUIRED],
            'course_category' => [self::RULE_REQUIRED],
            'course_fee' => [self::RULE_REQUIRED, self::RULE_NUMARIC],
            'extra' => [self::RULE_REQUIRED],
            'is_active' => [self::RULE_REQUIRED],
        ];
    }

    public function labels():array{
        return [
            'course_id' => "Course ID",
            'course_name' => "Course Name",
            'course_image_land' => 'Image (Landing Page)',
            'course_image_detail' => "Image (Detail Page)",
            'course_instructor' => "Instructor",
            'course_contents' => 'Course Contents (Seperated by ";" )',
            'course_description' => "Course Description",
            'course_duration' => 'Course Duration',
            'course_catetory' => 'Course Category',
            'course_fee' => 'Course Fee',
            'extra' => 'Extra Information',
            'is_active' => 'is an active record',
        ];
    }

}