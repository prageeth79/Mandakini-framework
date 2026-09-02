<?php
namespace app\core\cli\commands;
use app\core\cli\CommandInterface;
use app\core\cli\commands\MakeModelCommand;

class MakeControllerCommand implements CommandInterface {

    public function getDescription(): string {
        return "Generates a new Controller class in app/controllers. 
        \t\tUsage: make:controller <Name>
        \t\t     : make:controller <controller_name>/<model_name>[/<view_name>] <table_name>";
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
        
        if(count($nameParts) !== 2 || count($nameParts) !== 3) {
            echo "\033[31m[ERROR]\033[0m Invalid format. Usage: php mm make:controller <controller_name>/<model_name> <table_name>\n";
            return 1;
        }else{
            $controllerName = ucfirst($nameParts[0]) . 'Controller';
            $modelName = ucfirst($nameParts[1]);
            if(count($nameParts) == 3){
                $viewName = $nameParts[2];
            }
            else{
            $viewName = lcfirst($modelName);
            }
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

            $template = <<<PHP
<?php
namespace app\\controllers;

use app\\core\\Controller;
use app\\models\\{$modelName};

class {$controllerName} extends Controller {

    public function indexAction() {
        return \$this->render('home');
    }

    public function createAction() {
        \$model = new {$modelName}();
        // Handle form submission and save data to the database
        if(\$this->request->isPost()) {
            \$model->loadData(\$this->request->getBody());
            if(\$model->validate() && \$model->save()) {
                // Redirect or show success message
                Application::\$app->session->setFlash('success', '{$modelName} Added Successfully');
            }
        }
        return \$this->render('{$viewName}', ['model' => \$model]);
    }

    public function update(\$id) {
        \$model = {$modelName}::findOne(['{$primaryKey}' => \$id]);
        if(!\$model) {
            throw new \Exception('Model not found');
        }
        // Handle form submission and update data in the database
        if(\$this->request->isPost()) {
            \$model->loadData(\$this->request->getBody());
            if(\$model->validate() && \$model->save()) {
                // Redirect or show success message
                Application::\$app->session->setFlash('success', '{$modelName} Updated Successfully');
            }
        }
        return \$this->render('{$viewName}', ['model' => \$model]);
    }

    public function delete(\$id) {
        \$model = {$modelName}::findOne(['{$primaryKey}' => \$id]);
        if(!\$model) {
            throw new \Exception('Model not found');
        }
        // Handle deletion of the model from the database
        if(\$model->delete()) {
            Application::\$app->session->setFlash('success', '{$modelName} Deleted Successfully');
        }
    }

}
PHP;
        
            file_put_contents($filePathController, $template);
            echo "\033[32m[SUCCESS]\033[0m Created controller: app/controllers/{$controllerName}.php\n";
            return 0;
        }
    }
}