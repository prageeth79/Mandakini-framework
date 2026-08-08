
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

<!--
<form class="row g-3" action="" method="post" >
  <div class="col-md-12">
    <label for="inputFirstName4" class="form-label">First Name</label>
    <input type="text" name="firstName" class="form-control" id="inputFirstName4">
  </div>
  <div class="col-md-12">
    <label for="inputLastName4" class="form-label">Last Name</label>
    <input name="lastName" type="text" class="form-control" id="inputLastName4">
  </div>
  <div class="col-md-12">
    <label for="inputEmail4" class="form-label">Email</label>
    <input type="email" name="email" class="form-control" id="inputEmail4">
  
  <div class="col-md-12">
    <label for="inputPassword4" class="form-label">Password</label>
    <input type="password" name="password" class="form-control" id="inputPassword4">
  </div>
  <div class="col-md-12">
    <label for="inputPasswordRepeat4" class="form-label">Confirm Password</label>
    <input type="password" name="confirmPassword" class="form-control" id="inputPasswordRepeat4">
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-primary">Send Message</button>
  </div>
</form>
-->
