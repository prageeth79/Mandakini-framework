<?php
namespace app\core\cli\commands;
use app\core\cli\CommandInterface;
use app\core\cli\commands\MakeModelCommand;

class MakeControllerCommand implements CommandInterface {

    public function getDescription(): string {
        return "Generates a new Controller class in app/controllers. 
        \t\tUsage: make:controller <Name>
        \t\t     : make:controller <controller_name>/<model_name> <table_name>";
    }

    public function execute(array $args): int {
        $name = $args[0] ?? null;
        $tableName = $args[1] ?? null;

        if (!$name) {
            echo "\033[31m[ERROR]\033[0m Controller name is required. Usage: php mm make:controller <Name>\n";
            return 1;
        }elseif(strpos($name, '/')) {
            $this->generateControllerAndModel($name, $tableName);
            return 0;
        }else{

        $className = ucfirst($name) . 'Controller';
        $filePath = dirname(__DIR__, 3) . "/controllers/{$className}.php";
        if (file_exists($filePath)) {
            echo "\033[31m[ERROR]\033[0m Controller {$className} already exists at {$filePath}\n";
            return 1;
        }

        $template = <<<PHP
<?php
namespace app\\controllers;

use app\\core\\Controller;

class {$className} extends Controller {

    public function indexAction() {
        return \$this->render('home');
    }
}
PHP;

        file_put_contents($filePath, $template);
        echo "\033[32m[SUCCESS]\033[0m Created controller: app/controllers/{$className}.php\n";
        return 0;
        }
    }

    private function generateControllerAndModel(string $name, string $tableName): int {
        $nameParts = explode('/', $name);
        
        if(count($nameParts) !== 2) {
            echo "\033[31m[ERROR]\033[0m Invalid format. Usage: php mm make:controller <controller_name>/<model_name> <table_name>\n";
            return 1;
        }else{
            $controllerName = ucfirst($nameParts[0]) . 'Controller';
            $modelName = ucfirst($nameParts[1]);
            $viewName = lcfirst($modelName);
            //$tableName = $args[1] ?? null;

            if (!$tableName) {
                echo "\033[31m[ERROR]\033[0m Table name is required. Usage: php mm make:controller <controller_name>/<model_name> <table_name>\n";
                return 1;
            }

            $filePathController = dirname(__DIR__, 3) . "/controllers/{$controllerName}.php";
            $filePathModel = dirname(__DIR__, 3) . "/models/{$modelName}.php";
            if (file_exists($filePathController)) {
                echo "\033[31m[ERROR]\033[0m Controller {$controllerName} already exists at {$filePathController}\n";
                return 1;
            }
            if (file_exists($filePathModel)) {
                echo "\033[31m[ERROR]\033[0m Model {$modelName} already exists at {$filePathModel}\n";
                return 1;
            }
            if(file_exists(dirname(__DIR__, 3) . "/views/{$viewName}View.php")) {
                echo "\033[31m[ERROR]\033[0m View {$viewName} already exists at views/{$viewName}View.php\n";
                return 1;
            }

            $model = new MakeModelCommand();
            $model->execute([$modelName, $tableName]);
            sleep(3); // Optional: Sleep for a second to ensure the model is created before proceeding  
            $primaryKey = $model->primaryKey;
            $view = new MakeViewCommand();            
            $view->execute([$modelName, $viewName]);
            $actionName = lcfirst($modelName) . 'Action';

            $template = <<<PHP
<?php
namespace app\\controllers;

use app\\core\\Controller;
use app\\models\\{$modelName};
use app\\core\\Request;
use app\\core\\Application;

class {$controllerName} extends Controller {

    public function indexAction() {
        return \$this->render('home');
    }

    public function save(Request \$request): ?{$modelName} {
        \$model = new {$modelName}();
        // Handle form submission and save data to the database
        \$model->loadData(\$request->getBody());
        if(\$model->validate() && \$model->save()) {
            // Redirect or show success message
            Application::\$app->session->setFlash('success', '{$modelName} Added Successfully');
            //return \$model;
        }else {
            // Handle validation errors or other issues
            Application::\$app->session->setFlash('error', 'Failed to add {$modelName}. Please check the input.');
        }

        return \$model;
    }

    public function update(Request \$request): ?{$modelName} {
        \$model = new {$modelName}();       
        \$model->loadData(\$request->getBody());
        if(\$model->validate() && \$model->update()) {
            // Redirect or show success message
            Application::\$app->session->setFlash('success', '{$modelName} Updated Successfully');
            //return \$model;
        }else {
            // Handle validation errors or other issues
            Application::\$app->session->setFlash('error', 'Failed to update {$modelName}. Please check the input.');
        }
        
        return \$model;
      }

    public function delete(Request \$request): ?{$modelName} {
        \$model = new {$modelName}();
        \$model->loadData(\$request->getBody());
        // Handle deletion of the model from the database
        if(\$model->delete()) {
            Application::\$app->session->setFlash('success', '{$modelName} Deleted Successfully');
            //return \$model;
        }else {
            Application::\$app->session->setFlash('error', 'Failed to delete {$modelName}.');
        }
        return \$model;
    }

    public function {$actionName}(Request \$request){
        
        \$id = \$request->getBody()['id'] ?? null;
        if(\$request->isPost()) {
            if(isset(\$request->getBody()['btnDelete'])) {
                \$model = \$this->delete(\$request);
            } elseif(isset(\$request->getBody()['btnUpdate'])) {
                \$model = \$this->update(\$request);
            }elseif(isset(\$request->getBody()['btnSave'])) {
                \$model = \$this->save(\$request);
            }else {
                \$model =  new {$modelName}();
                \$model->loadData(\$request->getBody());
            }                
        } else {
            if(\$id) {
                \$model = {$modelName}::findOne([{$modelName}::primaryKey() => \$id]);
                 if(!\$model) {
                    \$model = new {$modelName}();
                }
                
            } else {
                \$model = new {$modelName}();
            }                       
        }        
       
        return \$this->render('{$viewName}', ['model' => \$model]);
        
        
    }

}
PHP;        
            file_put_contents($filePathController, $template);
            echo "\033[32m[SUCCESS]\033[0m Created controller: app/controllers/{$controllerName}.php\n";
            return 0;
        }
    }
}