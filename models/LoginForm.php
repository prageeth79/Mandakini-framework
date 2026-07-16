<?php

namespace app\models;
use app\core\Model;
use app\core\Application;

class LoginForm extends Model {
    public string $loging_id = '';
    public string $password = '';

    public function rules(): array {
        return [
            'loging_id' => [self::RULE_REQUIRED, self::RULE_ALPHANUMARIC],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 8]],
        ];
    }

    public function login() {
        $user = User::findOne(['loging_id' => $this->loging_id]);
        if (!$user) {
            $this->addError('loging_id', 'User does not exist with this login ID');
            return false;
        }
        if (!password_verify($this->password, $user->password)) {
            $this->addError('password', 'Password is incorrect');
            return false;
        }
        return Application::$app->login($user);
    }

    public function labels(): array {
        return [
            'loging_id' => 'Login ID',
            'password' => 'Password',
        ];
    }
}