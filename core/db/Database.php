<?php

namespace app\core\db;

use app\core\Application;
use app\core\db\schema\SchemaReflectorInterface;
use app\core\db\schema\TypeCasterInterface;
use app\core\db\schema\MySqlSchemaReflector;
use app\core\db\schema\MySqlTypeCaster;
use app\core\db\schema\PostgreSqlSchemaReflector;
use app\core\db\schema\PostgreSqlTypeCaster;
use app\core\db\schema\SqlServerSchemaReflector;
use app\core\db\schema\SqlServerTypeCaster;
use app\core\db\schema\OracleSchemaReflector;
use app\core\db\schema\OracleTypeCaster;
use app\core\db\schema\DefaultSchemaReflector;
use app\core\db\schema\DefaultTypeCaster;
use PDO;

class Database {
    public PDO $pdo;
    protected SchemaReflectorInterface $schemaReflector;
    protected TypeCasterInterface $typeCaster;

    /**
     * Runtime registry mapping DSN driver keys to strategy factory callables or class instances.
     * Format: ['driver_prefix' => ['reflector' => ..., 'caster' => ...]]
     */
    protected static array $customDrivers = [];

    public function __construct(array $config) {
        $dsn = $config['dsn'] ?? '';
        $user = $config['username'] ?? $config['user'] ?? '';
        $password = $config['password'] ?? '';

        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->resolveDriverStrategies($dsn);
    }

    /**
     * Dynamically register or override database strategies for a DSN driver prefix.
     *
     * @param string $driverPrefix E.g., 'sqlite', 'firebird', 'cubrid', or 'mysql'
     * @param SchemaReflectorInterface|callable():SchemaReflectorInterface $reflector
     * @param TypeCasterInterface|callable():TypeCasterInterface $caster
     */
    public static function registerDriverStrategy(
        string $driverPrefix,
        SchemaReflectorInterface|callable $reflector,
        TypeCasterInterface|callable $caster
    ): void {
        $key = strtolower(trim($driverPrefix));
        self::$customDrivers[$key] = [
            'reflector' => $reflector,
            'caster'    => $caster,
        ];
    }

    public function getSchemaReflector(): SchemaReflectorInterface
    {
        return $this->schemaReflector;
    }

    public function getTypeCaster(): TypeCasterInterface
    {
        return $this->typeCaster;
    }

    protected function resolveDriverStrategies(string $dsn): void
    {
        // Extract the driver prefix from DSN (e.g., 'pgsql' from 'pgsql:host=...')
        $driverPrefix = strtolower(explode(':', $dsn, 2)[0] ?? '');

        // 1. Check dynamically registered custom driver strategies first
        if (isset(self::$customDrivers[$driverPrefix])) {
            $custom = self::$customDrivers[$driverPrefix];
            
            $this->schemaReflector = is_callable($custom['reflector'])
                ? ($custom['reflector'])()
                : $custom['reflector'];

            $this->typeCaster = is_callable($custom['caster'])
                ? ($custom['caster'])()
                : $custom['caster'];

            return;
        }

        // 2. Built-in driver strategies
        if ($driverPrefix === 'mysql') {
            $this->schemaReflector = new MySqlSchemaReflector();
            $this->typeCaster = new MySqlTypeCaster();
            return;
        }

        if ($driverPrefix === 'pgsql') {
            $this->schemaReflector = new PostgreSqlSchemaReflector();
            $this->typeCaster = new PostgreSqlTypeCaster();
            return;
        }

        if (in_array($driverPrefix, ['sqlsrv', 'dblib'], true)) {
            $this->schemaReflector = new SqlServerSchemaReflector();
            $this->typeCaster = new SqlServerTypeCaster();
            return;
        }

        if ($driverPrefix === 'oci') {
            $this->schemaReflector = new OracleSchemaReflector();
            $this->typeCaster = new OracleTypeCaster();
            return;
        }

        // 3. Fall back to generic ANSI SQL reflector & caster
        $this->schemaReflector = new DefaultSchemaReflector();
        $this->typeCaster = new DefaultTypeCaster();
    }

    public function applyMigrations() {
        $this->creatMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR . '/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations);

        foreach ($toApplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }

            require_once Application::$ROOT_DIR . '/migrations/' . $migration;
            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Applying migration: $migration");
            $instance->up();
            $this->log("Applied migration: $migration");
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("All migrations are applied");
        }
    }

    public function saveMigrations(array $migrations) {
        $str = implode(",", array_map(fn($m) => "('$m')", $migrations));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES $str");
        $statement->execute();
    }

    protected function log($message) {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }

    public function getAppliedMigrations() {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function creatMigrationsTable() {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $sql = match ($driver) {
            'pgsql' => "CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );",

            'sqlsrv', 'dblib' => "IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'migrations')
                CREATE TABLE migrations (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL,
                    created_at DATETIME DEFAULT GETDATE()
                );",

            'oci' => "DECLARE
                tbl_count NUMBER;
            BEGIN
                SELECT COUNT(*) INTO tbl_count FROM user_tables WHERE table_name = 'MIGRATIONS';
                IF tbl_count = 0 THEN
                    EXECUTE IMMEDIATE 'CREATE TABLE migrations (
                        id NUMBER GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                        migration VARCHAR2(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )';
                END IF;
            END;",

            default => "CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );"
        };

        $this->pdo->exec($sql);
    }
}