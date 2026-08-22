<?php
namespace app\core;

abstract class Model {
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';
    public const RULE_UNIQUE = 'unique';
    public const RULE_NUMARIC = 'numaric';
    public const RULE_INT = 'integer';
    public const RULE_FLOAT = 'float';
    public const RULE_DATE = 'date';
    public const RULE_INLIST = 'in list';
    public const RULE_REGEX = 'regex';
    public const RULE_ALPHA = 'alpha';
    public const RULE_ALPHA_PLUS_SPACE = 'alpha + space';
    public const RULE_ALPHA_PLUS_SPACE_PLUS_DOT = 'alpha + space + dot';
    public const RULE_ALPHANUMARIC = 'alphanumaric';
    public const RULE_ALPHANUMARIC_PLUS_SPACE = 'alphanumaric + space';
    public array $errors = [];



    public function loadData($data) {
        
        foreach ($data as $key => $value) {            
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    abstract public function rules(): array;

    public function labels(): array {
        return [];
    }

    public function getLabel($attribute) {
        return $this->labels()[$attribute] ?? $attribute;
    }
    
    function isValidDate($dateString, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $dateString);
        // The object must be valid, and its formatted value must match the input string
        return $d && $d->format($format) === $dateString;
    }

    public function validate() {
        foreach ($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute};
            foreach ($rules as $rule) {
                $ruleName = $rule;
                if (is_array($rule)) {
                    $ruleName = $rule[0];
                }

                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addErrorForRule($attribute, [self::RULE_REQUIRED]);
                }

                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorForRule($attribute, [self::RULE_EMAIL]);
                }

                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorForRule($attribute, [self::RULE_MIN, 'min' => $rule['min']], $rule);
                }
                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorForRule($attribute, [self::RULE_MAX, 'max' => $rule['max']], $rule);
                }

                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $rule['match'] = $this->getLabel($rule['match']);
                    $this->addErrorForRule($attribute, [self::RULE_MATCH, 'match' => $rule['match']], $rule);
                }
                if ($ruleName === self::RULE_UNIQUE) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->pdo->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    if ($record) {
                        $this->addErrorForRule($attribute, [self::RULE_UNIQUE, 'field' => $this->getLabel($attribute)], $rule);
                    }
                }
                if ($ruleName === self::RULE_NUMARIC && !is_numeric($value)) {
                    $this->addErrorForRule($attribute, [self::RULE_NUMARIC, 'field' => $this->getLabel($attribute), $rule]);
                }
                if ($ruleName === self::RULE_INT && !is_int($value)) {
                    $this->addErrorForRule($attribute, [self::RULE_INT, 'field' => $this->getLabel($attribute), $rule]);
                }
                if ($ruleName === self::RULE_FLOAT && !is_float($value)) {
                    $this->addErrorForRule($attribute, [self::RULE_FLOAT, 'field' => $this->getLabel($attribute), $rule]);
                }
                if ($ruleName === self::RULE_DATE && !is_ValidDate($value)) {
                    $this->addErrorForRule($attribute, [self::RULE_DATE, 'field' => $this->getLabel($attribute), $rule]);
                }
                if ($ruleName === self::RULE_INLIST && !in_array($value, $rule['list'])) {
                    $this->addErrorForRule($attribute, [self::RULE_INLIST], $this->getLabel($attribute),  $rule);
                }
                if($ruleName === self::RULE_REGEX && !preg_match($rule['pattern'], $value) ){
                        $this->addErrorForRule($attribute,[self::RULE_REGEX, 'field' => $this->getLabel($attribute)], $rule);
                }
                if($ruleName === self::RULE_ALPHA && !preg_match('/^[A-Za-z]+$/', $value)){
                    $this->addErrorForRule($attribute, [self::RULE_ALPHA, 'field' => $this->getLabel($attribute), $rule]);
                }
                if($ruleName === self::RULE_ALPHA_PLUS_SPACE && !preg_match('/^[A-Za-z ]+$/', $value)){
                    $this->addErrorForRule($attribute, [self::RULE_ALPHA_PLUS_SPACE, 'field' => $this->getLabel($attribute), $rule]);
                }
                if($ruleName === self::RULE_ALPHA_PLUS_SPACE_PLUS_DOT && !preg_match('/^[A-Za-z .]+$/', $value)){
                    $this->addErrorForRule($attribute, [self::RULE_ALPHA_PLUS_SPACE_PLUS_DOT, 'field' => $this->getLabel($attribute), $rule]);
                }
                if($ruleName === self::RULE_ALPHANUMARIC && !preg_match('/^[A-Za-z0-9]+$/', $value)){
                    $this->addErrorForRule($attribute, [self::RULE_ALPHANUMARIC, 'field' => $this->getLabel($attribute), $rule]);
                }
                if($ruleName === self::RULE_ALPHANUMARIC_PLUS_SPACE && !preg_match('/^[A-Za-z0-9 ]+$/', $value)){
                    $this->addErrorForRule($attribute, [self::RULE_ALPHANUMARIC_PLUS_SPACE, 'field' => $this->getLabel($attribute), $rule]);
                }
            }
        }

        return empty($this->errors);
    }

    protected function addErrorForRule($attribute, $rule, $params = []) {
        $messages = $this->errorMessages()[$rule[0]] ?? '';
        foreach ($params as $key => $value) {
            if(is_array($value)){
                $messages = str_replace('{' . $key . '}', implode('; ',$value), $messages);
            }else{
                $messages = str_replace('{' . $key . '}', $value, $messages);
            }
        }
        $this->errors[$attribute][] = $messages;
    }
    public function addError($attribute, $message) {
         $this->errors[$attribute][] = $message;
      }

    public function errorMessages() {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be a valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MATCH => 'This field must match {match}',
            self::RULE_UNIQUE => 'Record with this {field} already exists',
            self::RULE_NUMARIC => 'The {field} must be a valid number',
            self::RULE_INT => 'The {field] must be a whole number',
            self::RULE_FLOAT => 'The {field] must be a number with decimal points',
            self::RULE_DATE => 'The {field} must be a valid date',
            self::RULE_INLIST => 'The {field} must contain one of {list} value', 
            self::RULE_REGEX => 'THe {field} must match the pattern {pattern}',
            self::RULE_ALPHA => 'THe {field} must contain only \'A\' to \'Z\' and \'a\' to \'z\' charactors',
            self::RULE_ALPHA_PLUS_SPACE => 'THe {field} must contain only \'A\' to \'Z\' , \'a\' to \'z\' and \'space\'  charactors',
            self::RULE_ALPHA_PLUS_SPACE_PLUS_DOT => 'THe {field} must contain only \'A\' to \'Z\' , \'a\' to \'z\' , \'space\' and \'.\'  charactors',
            self::RULE_ALPHANUMARIC => 'THe {field} must contain only \'A\' to \'Z\' , \'a\' to \'z\' and \'0\' to \'9\'  charactors',
            self::RULE_ALPHANUMARIC_PLUS_SPACE => 'THe {field} must contain only \'A\' to \'Z\' , \'a\' to \'z\' , \'0\' to \'9\' and \'space\'  charactors',
        ];
    }

    public function hasError($attribute) {
        return $this->errors[$attribute] ?? false;
    }

    public function getFirstError($attribute) {
        return $this->errors[$attribute][0] ?? '';
    }
}