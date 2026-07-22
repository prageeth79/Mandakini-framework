
<div class="container">
  <div class="row">
    <div class="col-12 p-4";">
      
<h1>Create an Account</h1>
<?php $form = \app\core\form\Form::begin('', 'post') ?>
  <?php echo $form->field($model, 'loging_id') ?>
  <div class="row">
    <div class="col-md-6">
      <?php echo $form->field($model, 'firstName') ?>
    </div>
    <div class="col-md-6">
      <?php echo $form->field($model, 'lastName') ?>
    </div>
  </div>
  <?php echo $form->field($model, 'email') ?>
  <?php echo $form->field($model, 'password')->passwordField() ?>
  <?php echo $form->field($model, 'confirmPassword')->passwordField() ?>
  <?php echo $form->field($model, 'category')->selectField(['student' => 'Student', 'instructor' => 'Instructor ','admin' => 'Admin']) ?>
  <br>
  <button type="submit" class="btn btn-primary">Submit</button>
  <br>
<?php \app\core\form\Form::end() ?>
</div>
</div>
</div>

