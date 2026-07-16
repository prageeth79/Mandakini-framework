
<div class="container">
  <div class="row">
    <div class="col-12 p-4">
      
<h1>Add Course Category</h1>
<?php $form = \app\core\form\Form::begin('', 'post') ?>
  
  <?php echo $form->field($model, 'category_id') ?>
  <?php echo $form->field($model, 'category_name') ?>
  
  <br>
  <button type="submit" class="btn btn-primary">Submit</button>
  <br>
<?php \app\core\form\Form::end() ?>
</div>
</div>
<?php
// $dbtable may be an object with renderHtml() or a string
if (is_object($dataTable) && method_exists($dataTable, 'renderHtml')) {
    echo $dataTable->renderHtml();
} else {
    echo (string)$dataTable;
}

?>
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
