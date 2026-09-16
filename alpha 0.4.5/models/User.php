<?php
namespace app\models;
use app\core\UserModel;

class User extends UserModel {

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
/*
    public function attributes(): array {
        return ['loging_id', 'firstName', 'lastName', 'email', 'password','category'];
    }

    public static function primaryKey(): string {
        return 'loging_id';
    }
*/
    public function save(String $passwordField = "password"):bool {       
        return parent::save($passwordField);
    }

    public function delete(array $where = [], string $statusField = "status"):int{
        return parent::delete($where, $statusField);
    }

    public function labels(): array {

        $lables = parent::labels();
        $lables['confirmPassword'] = 'Confirm Password';
        return $lables;
        /*
        return [
            'loging_id' => 'Login ID',
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'email' => 'Email',
            'password' => 'Password',
            'confirmPassword' => 'Confirm Password',
            'category' => 'Category',
        ];
        */
    }

    public function getDisplayName(): string {
        $first = $this->firstName ?: ($this->firstname ?? '');
        $last = $this->lastName ?: ($this->lastname ?? '');
        $full = trim($first . ' ' . $last);
        return $full !== '' ? $full : ($this->email ?? '');
    }

    public function calculate(): bool{
        // Implement any calculations or logic specific to the User model here
        return true;
    }

    public function getUserByLoginId(string $loginId): ?self {
        $user = self::findOne(['loging_id' => $loginId]);
        return $user ?: null;
    }

    public function getUserByEmail(string $email): ?self {
        $user = self::findOne(['email' => $email]);
        return $user ?: null;
    }

    public function getUserById(int $id): ?self {
        $user = self::findOne(['id' => $id]);
        return $user ?: null;
    }

    public function getUserByCategory(string $category): ?self {
        $user = self::findOne(['category' => $category]);
        return $user ?: null;
    }

    public function getUserByStatus(int $status): ?self {
        $user = self::findOne(['status' => $status]);
        return $user ?: null;
    }

    public function getUserByLoginIdAndPassword(string $loginId, string $password): ?self {
        $user = self::findOne(['loging_id' => $loginId]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByEmailAndPassword(string $email, string $password): ?self {
        $user = self::findOne(['email' => $email]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByLoginIdAndStatus(string $loginId, int $status): ?self {
        $user = self::findOne(['loging_id' => $loginId, 'status' => $status]);
        return $user ?: null;
    }

    public function getUserByEmailAndStatus(string $email, int $status): ?self {
        $user = self::findOne(['email' => $email, 'status' => $status]);
        return $user ?: null;
    }

    public function getUserByLoginIdAndCategory(string $loginId, string $category): ?self {
        $user = self::findOne(['loging_id' => $loginId, 'category' => $category]);
        return $user ?: null;
    }

    public function getUserByEmailAndCategory(string $email, string $category): ?self {
        $user = self::findOne(['email' => $email, 'category' => $category]);
        return $user ?: null;
    }
    public function getUserByLoginIdAndPasswordAndStatus(string $loginId, string $password, int $status): ?self {
        $user = self::findOne(['loging_id' => $loginId, 'status' => $status]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByEmailAndPasswordAndStatus(string $email, string $password, int $status): ?self {
        $user = self::findOne(['email' => $email, 'status' => $status]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByLoginIdAndPasswordAndCategory(string $loginId, string $password, string $category): ?self {
        $user = self::findOne(['loging_id' => $loginId, 'category' => $category]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByEmailAndPasswordAndCategory(string $email, string $password, string $category): ?self {
        $user = self::findOne(['email' => $email, 'category' => $category]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByLoginIdAndPasswordAndStatusAndCategory(string $loginId, string $password, int $status, string $category): ?self {
        $user = self::findOne(['loging_id' => $loginId, 'status' => $status, 'category' => $category]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByEmailAndPasswordAndStatusAndCategory(string $email, string $password, int $status, string $category): ?self {
        $user = self::findOne(['email' => $email, 'status' => $status, 'category' => $category]);
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        return null;
    }

    public function getUserByLoginIdAndStatusAndCategory(string $loginId, int $status, string $category): ?self {
        $user = self::findOne(['loging_id' => $loginId, 'status' => $status, 'category' => $category]);
        return $user ?: null;
    }

    public function getUserByEmailAndStatusAndCategory(string $email, int $status, string $category): ?self {
        $user = self::findOne(['email' => $email, 'status' => $status, 'category' => $category]);
        return $user ?: null;
    }

    public function isActive(): bool {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function isDeleted(): bool {
        return $this->status === self::STATUS_DELETED;
    }

    public function activateUser(): void {
        $this->status = self::STATUS_ACTIVE;
    }

    public function deactivateUser(): void {
        $this->status = self::STATUS_INACTIVE;
    }

    public function deleteUser(): void {
        $this->status = self::STATUS_DELETED;
    }

    public function restoreUser(): void {
        $this->status = self::STATUS_ACTIVE;
    }

    public function isAdmin(): bool {
        return $this->category === 'admin';
    }

    public function isInstructor(): bool {
        return $this->category === 'instructor';
    }

    public function isStudent(): bool {
        return $this->category === 'student';
    }

    public function isGuest(): bool {
        return $this->category === 'guest';
    }


}