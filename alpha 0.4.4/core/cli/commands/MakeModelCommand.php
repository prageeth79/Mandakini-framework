<?php

namespace app\core\cli\commands;

use app\core\Application;
use app\core\cli\CommandInterface;

class MakeModelCommand implements CommandInterface
{
    public string $classNameArg = '';
    public string $tableNameArg = '';
    public array $columns = [];
    public array $rules = [];
    public array $labels = [];
    public string$primaryKey = '';

    public function getDescription(): string
    {
        return 'Generate DBModel classes by inspecting database tables (Usage: make:model <table_name|--all>)';
    }

    public function execute(array $args): int
    {
        $tableArg = $args[1] ?? null;
        $this->tableNameArg = $args[1] ?? null;
        $this->classNameArg = $args[0] ?? null;

        if (!$this->classNameArg || !$tableArg) {
            echo "\033[33mUsage:\033[0m php mm make:model <class_name> <table_name|--all>\n";
            return 1;
        }

        $db = Application::$app->db ?? null;
        if (!$db) {
            echo "\033[31m[ERROR]\033[0m Database connection is not available. Start MySQL/PostgreSQL and try again.\n";
            return 1;
        }

        if ($tableArg === '--all') {
            $pdo = $db->pdo;
            $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

            $sql = match ($driver) {
                'pgsql'  => "SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'",
                'sqlsrv' => "SELECT name FROM sys.tables",
                'oci'    => "SELECT table_name FROM user_tables",
                'sqlite' => "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'",
                default  => "SHOW TABLES",
            };

            $tables = $pdo->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                if ($table === 'migrations') {
                    continue;
                }
                $this->generateModel($table);
            }

            return 0;
        }

        return $this->generateModel($tableArg);
    }

    protected function generateModel(string $tableName): int
    {
        $reflector = Application::$app->db->getSchemaReflector();

        try {
            $meta = $reflector->inspectTable($tableName);
        } catch (\Throwable $e) {
            echo "\033[31m[ERROR]\033[0m Could not inspect table '{$tableName}': " . $e->getMessage() . PHP_EOL;
            return 1;
        }

        //$className = $this->tableNameToClassName($tableName);
        $className = $this->classNameArg;
        $outputDir = Application::$ROOT_DIR . '/models';
        $filePath  = "{$outputDir}/{$className}.php";

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $propertyDocBlocks = [];
        $rules = [];
        $columns = [];
        foreach ($meta['types'] as $column => $dbType) {
            $phpType = match (true) {
                str_contains($dbType, 'int') => 'int',
                str_contains($dbType, 'bool') || $dbType === 'bit' => 'bool',
                str_contains($dbType, 'float') || str_contains($dbType, 'decimal') || str_contains($dbType, 'numeric') || str_contains($dbType, 'double') => 'float',
                str_contains($dbType, 'json') => 'array',
                default => 'string',
            };
            $propertyDocBlocks[] = " * @property {$phpType} \${$column}";
            $rules[] = "            '{$column}' => [self::RULE_REQUIRED],"; // Example rule, you can customize this based on your needs
            $columns[] = "    public {$phpType} \${$column};";
            $this->columns[] = $column;
            $this->rules[$column] = ['required']; // Example rule, you can customize this based on your needs   
            $this->labels[$column] = ucfirst(str_replace('_', ' ', $column)); // Example label, you can customize this based on your needs
            $this->primaryKey = $meta['primaryKey'] ?? '';
        }
        $rulesStr = implode("\n", $rules);
        $columnsStr = implode("\n", $columns);
        $docBlockStr = implode("\n", $propertyDocBlocks);

        $code = <<<PHP
<?php

namespace app\models;

use app\core\db\DBModel;

/**
 * Class {$className}
 *
{$docBlockStr}
 *
 * @package app\models
 */
class {$className} extends DBModel
{

{$columnsStr}

    public static function tableName(): string
    {
        return '{$tableName}';
    }

     public function rules():array{

        return [
            // Define validation rules here
{$rulesStr}
        ];

    }

    public function labels(): array
    {
        \$labels = parent::labels();
        // Add custom labels for the model's attributes here
        // Example: \$labels['attribute_name'] = 'Custom Label';
        return \$labels;
    }

    public function calculate(): bool
    {
        // Implement any calculations or business logic here
        // this method is called in DBTable class when displaying the table data in the model.
        return true;
    }
}
PHP;

        file_put_contents($filePath, $code);
        echo "\033[32m[SUCCESS]\033[0m Model 'app\\models\\{$className}' created at models/{$className}.php" . PHP_EOL;
        return 0;
    }

    protected function tableNameToClassName(string $tableName): string
    {
        $singular = rtrim($tableName, 's');
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $singular)));
    }
}