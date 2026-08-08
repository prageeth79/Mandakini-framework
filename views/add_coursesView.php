<h1>Add Landing Page Courses</h1>
<?php $form = \app\core\form\Form::begin('', 'post') ?>
  <?php echo $form->field($model, 'course_id') ?>
  <div class="row">
    <div class="col-md-6">
      <?php echo $form->field($model, 'course_name') ?>
    </div>
    <div class="col-md-4">
      <?php echo $form->field($model, 'course_image_land')->setReadOnly(true) ?>
    </div>
    <div class="col-md-2">
      <?php echo $form->field($model, 'course_image_file_land')->fileField(['label' => '']) ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <?php echo $form->field($model, 'course_image_detail')->setReadOnly(true) ?>
    </div>    
    <div class="col-md-2">
      <?php echo $form->field($model, 'course_image_file_detail')->fileField(['label' => '']) ?>
    </div>    
    <div class="col-md-6">
      <?php echo $form->field($model, 'course_instructor') ?>
    </div>
   </div>
  <div class="row">
    <div class="col-md-6">
      <?php echo $form->field($model, 'course_duration') ?>
    </div>
    <div class="col-md-6">
      <?php echo $form->field($model, 'course_fee')->numberField() ?>
    </div>
   </div>
   <div class="row">
      <div class="col-md-10">
        <?php echo $form->field($model, 'course_category')->selectField($categoryOptions) ?>
      </div>
      <div class="col-md-2">
        <div class="form-group" style="padding-top:20px" >
          <a href="<?php echo $this->asset('courses/category/add'); ?>" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Add Category</a>
        </div>
      </div>
    </div>
   <?php echo $form->field($model, 'course_contents')->textAreaField() ?>
   <?php echo $form->field($model, 'course_description')->textAreaField() ?>
   <?php echo $form->field($model, 'extra')->textAreaField() ?>
   <?php echo $form->field($model, 'is_active')->checkboxField() ?>
   <br>
  <a class="btn btn-primary" href="/public/courses/add" role="button">New</a> &nbsp;&nbsp;  <button type="submit" class="btn btn-primary">Save</button>
<?php \app\core\form\Form::end() ?>
<?php
// $dbtable may be an object with renderHtml() or a string
if (is_object($dataTable) && method_exists($dataTable, 'renderHtml')) {
    echo $dataTable->renderHtml();
} else {
    echo (string)$dataTable;
}

?>
