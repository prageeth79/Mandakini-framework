<?php
/* @var $model \app\models\LoginForm */
?>

<div class="position-relative" style="height: 100vh;">
<div class="position-absolute top-50 start-50 translate-middle col-md-6 col-sm-12 col-xs-12 col-12" style="border: 1px solid #ccc; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);" >
        <h1>Login</h1>
        <?php $form = \app\core\form\Form::begin('', 'post') ?>
          
          <?php echo $form->field($model, 'loging_id') ?>
          <?php echo $form->field($model, 'password')->passwordField() ?>
        
          <button type="submit" class="btn btn-primary">Submit</button>
        <?php \app\core\form\Form::end() ?>

  </div>
</div>
  