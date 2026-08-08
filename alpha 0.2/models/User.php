<?php
namespace app\models;
use app\core\Model;
use app\core\DbModel;
use app\core\UserModel;
use app\core\Application;

class User extends UserModel {

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    public string $loging_id = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $password = '';
    public string $confirmPassword = '';
    public string $category = '';
    public int $status = self::STATUS_INACTIVE;

    public static function tableName(): string {
        return 'users';
    }

    public function rules(): array {
        return [
            'loging_id' => [self::RULE_REQUIRED],
            'firstName' => [self::RULE_REQUIRED, self::RULE_ALPHA],
            'lastName' => [self::RULE_REQUIRED, self::RULE_ALPHA],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, [self::RULE_UNIQUE, 'class' => self::class]],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8]],
            'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
            'category' => [self::RULE_REQUIRED],
        ];
    }

    public function attributes(): array {
        return ['loging_id', 'firstName', 'lastName', 'email', 'password','category'];
    }

    public static function primaryKey(): string {
        return 'loging_id';
    }

    public function save() {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return parent::save();
    }

    public function labels(): array {
        return [
            'loging_id' => 'Login ID',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'email' => 'Email',
            'password' => 'Password',
            'confirmPassword' => 'Confirm Password',
            'category' => 'Category',
        ];
    }

    public function getDisplayName(): string {
        $first = $this->firstName ?: ($this->firstname ?? '');
        $last = $this->lastName ?: ($this->lastname ?? '');
        $full = trim($first . ' ' . $last);
        return $full !== '' ? $full : ($this->email ?? '');
    }
}