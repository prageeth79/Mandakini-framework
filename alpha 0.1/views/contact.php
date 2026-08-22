<h1>Contact Us</h1>

<?php
/** @var string $message */ 

if (!empty($message)) {
    echo '<div class="alert alert-success" role="alert">' . $message . '</div>';
}

$form = new \app\core\form\Form();
$form->begin('', 'post');
echo $form->field($model, 'email')->emailField();
echo $form->field($model, 'subject')->textField();  
echo $form->field($model, 'body')->textareaField();
echo '<button type="submit" class="btn btn-primary">Send Message</button>';
$form->end();
?>
