<?php
namespace app\core\db\schema;

use PDO;
use RuntimeException;

class SchemaFactory {

    public static function createReflector(PDO $pdo): SchemaReflectorInterface {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'mysql'  => new MySqlSchemaReflector(),
            'pgsql'  => new PostgreSqlSchemaReflector(),
            'sqlite' => new SqliteSchemaReflector(),
            'sqlsrv' => new SqlServerSchemaReflector(),
            'oci'   => new OracleSchemaReflector(),
            default  => throw new RuntimeException("Unsupported PDO driver for SchemaReflector: {$driver}"),
        };
    }

    public static function createTypeCaster(PDO $pdo): TypeCasterInterface {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        return match ($driver) {
            'mysql'  => new MySqlTypeCaster(),
            'pgsql'  => new PostgreSqlTypeCaster(),
            'sqlite' => new SqliteTypeCaster(),
            'sqlsrv' => new SqlServerTypeCaster(),
            'oci'   => new OracleTypeCaster(),
            default  => throw new RuntimeException("Unsupported PDO driver for TypeCaster: {$driver}"),
        };
    }
}