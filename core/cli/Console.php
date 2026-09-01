<?php

namespace app\core\cli;

class Console {
    protected array $commands = [];

    public function __construct() {
        $this->autoRegisterCommands();
    }

    public function register(string $name, CommandInterface $command): void {
        $this->commands[$name] = $command;
    }

    protected function autoRegisterCommands(): void {
        $commandsDir = __DIR__ . '/commands';
        if (!is_dir($commandsDir)) {
            return;
        }

        foreach (scandir($commandsDir) as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
                continue;
            }

            $className = 'app\\core\\cli\\commands\\' . pathinfo($file, PATHINFO_FILENAME);
            if (class_exists($className) && is_subclass_of($className, CommandInterface::class)) {
                // Convert class name to kebab-case command key (e.g., MakeControllerCommand -> make:controller)
                $commandName = strtr(
                    lcfirst(preg_replace('/Command$/', '', pathinfo($file, PATHINFO_FILENAME))),
                    ['Make' => 'make:', 'Migrate' => 'migrate:']
                );
                
                $this->register(strtolower($commandName), new $className());
            }
        }
    }

    public function run(array $argv): int {
        $commandName = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        if ($commandName === 'help' || $commandName === '-h' || $commandName === '--help') {
            $this->showHelp();
            return 0;
        }

        if (!isset($this->commands[$commandName])) {
            $this->error("Unknown command: {$commandName}");
            $this->showHelp();
            return 1;
        }

        return $this->commands[$commandName]->execute($args);
    }

    public function info(string $message): void {
        echo "\033[32m[INFO]\033[0m {$message}\n";
    }

    public function error(string $message): void {
        echo "\033[31m[ERROR]\033[0m {$message}\n";
    }

    protected function showHelp(): void {
        echo "\033[33mMandakini Framework CLI Tool\033[0m\n";
        echo "Usage: php mandakini <command> [options]\n\n";
        echo "Available Commands:\n";

        foreach ($this->commands as $name => $cmd) {
            printf("  \033[36m%-20s\033[0m %s\n", $name, $cmd->getDescription());
        }
        echo "\n";
    }
}