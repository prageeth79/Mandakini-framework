<?php
namespace app\models;
use app\core\Model;

class ContactForm extends Model {
    public string $email = '';
    public string $subject = '';
    public string $body = '';

    public function rules(): array{
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'subject' => [self::RULE_REQUIRED],
            'body' => [self::RULE_REQUIRED]
        ];
    }

    public function labels(): array {
        return [
            'email' => 'Email',
            'subject' => 'Subject',
            'body' => 'Message'
        ];
    }

    public function send() {
        // Implement email sending logic here
        return true;
    }
}