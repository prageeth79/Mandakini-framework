<?php
namespace app\core\cli\commands;

use app\core\cli\CommandInterface;

class MakeControllerCommand implements CommandInterface {

    public function getDescription(): string {
        return "Generates a new Controller class in app/controllers";
    }

    public function execute(array $args): int {
        $name = $args[0] ?? null;

        if (!$name) {
            echo "\033[31m[ERROR]\033[0m Controller name is required. Usage: php mandakini make:controller <Name>\n";
            return 1;
        }

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

    public function index() {
        return \$this->render('home');
    }
}
PHP;

        file_put_contents($filePath, $template);
        echo "\033[32m[SUCCESS]\033[0m Created controller: app/controllers/{$className}.php\n";
        return 0;
    }
}