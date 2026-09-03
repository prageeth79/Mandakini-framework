<?php
namespace app\core\form;
use app\core\Model;

class Field extends BaseField {
    public  const  TYPE_TEXT = 'text';
    public  const  TYPE_PASSWORD = 'password';  
    public  const  TYPE_EMAIL = 'email';
    public  const  TYPE_NUMBER = 'number';
    public  const  TYPE_HIDDEN = 'hidden';
    public  const  TYPE_TEXTAREA = 'textarea';
    public  const  TYPE_SELECT = 'select';
    public  const  TYPE_CHECKBOX = 'checkbox';
    public  const  TYPE_RADIO = 'radio';
    public const  TYPE_FILE = 'file';
    public const  TYPE_SUBMIT = 'submit';
    public const  TYPE_RESET = 'reset';
    public const  TYPE_BUTTON = 'button';
    public const  TYPE_COLOR = 'color';
    public const  TYPE_DATE = 'date';
    public const  TYPE_DATETIME_LOCAL = 'datetime-local';
    public const  TYPE_MONTH = 'month';
    public const  TYPE_RANGE = 'range';
    public const  TYPE_TIME = 'time';
    public const  TYPE_WEEK = 'week';
    public const  TYPE_URL = 'url';
    public const  TYPE_TEL = 'tel';
    public const  TYPE_SEARCH = 'search';
    public Model $model;
    public string $attribute;
    public string $type;
    public array $options = [];
    public string $value = '';
    public string $label = '';
    public bool $readOnly = false;

    private function addReadOnly():string{
        return ($this->readOnly)?" readOnly ": "";
    }

    public function __construct(Model $model, string $attribute, string $value = '') {
        $this->model = $model;
        $this->attribute = $attribute;
        $this->type = self::TYPE_TEXT;
        $this->value = $value;
        $this->label = $this->model->getLabel($this->attribute);       
    }


    public function __toString() {
        if ($this->type === self::TYPE_TEXTAREA) {
            return sprintf('
            <div class="form-group">
                <label>%s</label>
                <textarea name="%s" class="form-control %s" %s >%s</textarea>
                <div class="invalid-feedback">%s</div>
            </div>',
            $this->label,
            $this->attribute,
            $this->model->hasError($this->attribute) ? 'is-invalid' : '',
            $this->addReadOnly(),
            $this->value ?: $this->model->{$this->attribute},
            $this->model->getFirstError($this->attribute)
        );
        }
        if($this->type === self::TYPE_SELECT) {
            $optionsHtml = '';
            if($this->value ==''){
                $this->value = $this->model->{$this->attribute};
            }
            
            foreach ($this->options as $value => $label) {
                $selected = $this->value === $value ? ' selected ' : '';
                
                $optionsHtml .= sprintf('<option value="%s" %s>%s</option>', $value, $selected, $label);
            }
         
            return sprintf('
            <div class="form-group">
                <label>%s</label>
                <select name="%s" class="form-control %s" %s >%s</select>
                <div class="invalid-feedback">%s</div>
            </div>',
            $this->label,
            $this->attribute,
            $this->model->hasError($this->attribute) ? 'is-invalid' : '',
            $this->addReadOnly(),
            $optionsHtml,
            $this->model->getFirstError($this->attribute)
        );
        
        }
    if($this->type === self::TYPE_CHECKBOX){
        $checked = (isset($this->model->{$this->attribute}) && $this->model->{$this->attribute}== 1)? "checked ": "";
        return sprintf('
            <div class="form-group">
                <label>%s</label>
                <input type="%s" name="%s" value="%s" class="form-check %s" %s %s >
                <div class="invalid-feedback">%s</div>
            </div>',
            $this->label,
            $this->type,
            $this->attribute,
            $this->value ?: $this->model->{$this->attribute},
            $this->model->hasError($this->attribute) ? 'is-invalid' : '',
            $checked,
            $this->addReadOnly(),
            $this->model->getFirstError($this->attribute)
        );
    }
    if($this->type === self::TYPE_FILE){
        return sprintf('
            <div class="form-group">
                <label>%s</label>
                <input type="%s" name="%s" value="%s" class="form-control custom-file-input %s"  %s >
                <div class="invalid-feedback">%s</div>
            </div>',
            $this->label,
            $this->type,
            $this->attribute,
            $this->value ?: $this->model->{$this->attribute},
            $this->model->hasError($this->attribute) ? 'is-invalid' : '',
            $this->addReadOnly(),
            $this->model->getFirstError($this->attribute)
        );
    }
        return sprintf('
            <div class="form-group">
                <label>%s</label>
                <input type="%s" name="%s" value="%s" class="form-control %s" %s >
                <div class="invalid-feedback">%s</div>
            </div>',
            $this->label,
            $this->type,
            $this->attribute,
            $this->value ?: $this->model->{$this->attribute},
            $this->model->hasError($this->attribute) ? 'is-invalid' : '',
            $this->addReadOnly(),
            $this->model->getFirstError($this->attribute)
        );
    }

    public function setLabel(string $label){
        $this->label = $label;
        return $this;
    }

    public function setReadOnly(bool $readOnly){
        $this->readOnly = $readOnly;
        return $this;
    }


    public function passwordField() {
        $this->type = self::TYPE_PASSWORD;
        return $this;
    }

    public function textField() {
        $this->type = self::TYPE_TEXT;
        return $this;
    }

    public function emailField() {
        $this->type = self::TYPE_EMAIL;
        return $this;
    }
    public function numberField() {
        $this->type = self::TYPE_NUMBER;
        return $this;
    }
    public function hiddenField() {
        $this->type = self::TYPE_HIDDEN;
        return $this;
    }
    public function textareaField() {
        $this->type = self::TYPE_TEXTAREA;
        return $this;
    }
    public function selectField(array $options) {
        $this->type = self::TYPE_SELECT;
        $this->options = $options;
        return $this;
    }
    public function checkboxField() {
        $this->type = self::TYPE_CHECKBOX;
        return $this;
    }
    public function radioField() {
        $this->type = self::TYPE_RADIO;
        return $this;
    }
    public function fileField($options =[]) {
        if(isset($options['label'])){
            $this->label = $options['label'];
        }
        $this->type = self::TYPE_FILE;
        return $this;
    }
    public function submitField() {
        $this->type = self::TYPE_SUBMIT;
        return $this;
    }
    public function resetField() {
        $this->type = self::TYPE_RESET;
        return $this;
    }
    public function buttonField() {
        $this->type = self::TYPE_BUTTON;
        return $this;
    }
    public function colorField() {
        $this->type = self::TYPE_COLOR;
        return $this;
    }
    public function dateField() {
        $this->type = self::TYPE_DATE;
        return $this;
    }
    public function datetimeLocalField() {
        $this->type = self::TYPE_DATETIME_LOCAL;
        return $this;
    }
    public function monthField() {
        $this->type = self::TYPE_MONTH;
        return $this;
    }
    public function rangeField() {
        $this->type = self::TYPE_RANGE;
        return $this;
    }
    public function timeField() {
        $this->type = self::TYPE_TIME;
        return $this;
    }
    public function weekField() {
        $this->type = self::TYPE_WEEK;
        return $this;
    }
    public function urlField() {
        $this->type = self::TYPE_URL;
        return $this;
    }
    public function telField() {
        $this->type = self::TYPE_TEL;
        return $this;
    }
    public function searchField() {
        $this->type = self::TYPE_SEARCH;
        return $this;
    }
    
    public function renderInput(): string {
        return $this->__toString();
    }

}