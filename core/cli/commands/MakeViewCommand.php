<?php
namespace app\core\cli\commands;

use app\core\Application;
use app\core\cli\CommandInterface;

class MakeViewCommand implements CommandInterface{

    public function getDescription(): string
    {
        return 'Generate view using an exsisting model (Usage: make:view <model_name|--all>)';
    }

    public function execute(array $args): int {

        $modelName = $args[0] ?? null;
        $viewName = $args[1] ?? null;

        if(!$modelName || !$viewName) {
            echo "\033[33mUsage:\033[0m php mm make:view <model_name> <view_name|--all>\n";
            return 1;
        }

        $modelClass = 'app\\models\\' . ucfirst($modelName);

        if (!class_exists($modelClass)) {
            echo "\033[31m[ERROR]\033[0m Model {$modelClass} does not exist. Please create the model first using 'make:model' command.\n";
            return 1;
        }

        $model = new $modelClass();
        $columns = $model->attributes();
        $primaryKey = $modelClass::primaryKey();
        $fields = [];
        $fields[] = "<?php echo \$form->field(\$model, '{$primaryKey}') ?>";
        foreach($columns as $column) {
            $fields[] = "<?php echo \$form->field(\$model, '{$column}') ?>";
        }

        $fieldsVar = implode("\n\t\t\t\t", $fields);

        $code = <<<PHP
<div class="container">
    <div class="row">
        <div class="col-12 p-4";>
      
            <h1>Create/edit {$modelName}</h1>
            <?php \$form = \app\core\\form\Form::begin('', 'post') ?>
                {$fieldsVar}
                
                <div class="form-group mt-3">
                    <div class="row">
                        <div class="col-md-4">
                            <button type="submit" name="btnNew" class="btn btn-primary">New</button>
                            <button type="submit" name="btnSave" class="btn btn-primary">Save</button>
                            <button type="submit" name="btnUpdate" class="btn btn-primary">Update</button>
                            <button type="submit" name="btnDelete" class="btn btn-primary">Delete</button>
                            <?php echo \app\core\util\Csrf::field(); ?>
                        </div>
                    </div>
                </div>
            <?php \app\core\\form\Form::end() ?>
        </div>
    </div>
    <?php echo \$dataTable ?>
</div>

PHP;

        $filePath = dirname(__DIR__, 3) . "/views/{$viewName}View.php";
        if (file_exists($filePath)) {
            echo "\033[31m[ERROR]\033[0m View {$viewName} already exists at {$filePath}\n";
            return 1;
        }

        file_put_contents($filePath, $code);
        echo "\033[32m[SUCCESS]\033[0m View 'app\\views\\{$viewName}' created at views/{$viewName}.php" . PHP_EOL;
        return 0;       
    }
}