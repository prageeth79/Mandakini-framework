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
<!---
<form class="row g-3" action="" method="post" >
  <div class="col-md-6">
    <label for="inputEmail4" class="form-label">Email</label>
    <input type="email" name="email" class="form-control" id="inputEmail4">
  </div>
  <div class="col-md-6">
    <label for="inputSubject4" class="form-label">Subject</label>
    <input name="subject" type="text" class="form-control" id="inputSubject4">
  </div>
  <div class="col-12">
    <label for="inputBody" class="form-label">Message</label>
    <textarea name="body" class="form-control" id="inputBody" placeholder="Your message here..."></textarea>
  </div>
  
  <div class="col-12">
    <button type="submit" class="btn btn-primary">Send Message</button>
  </div>
</form>
--->