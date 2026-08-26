<?php
namespace app\core\db;

use app\core\Application;

/**
 * PostgreSQL-backed DBModel that automatically discovers table columns
 * and provides sensible defaults for `attributes()` and `primaryKey()`.
 */

abstract class PostgresDBModel extends DBModel
{
    /**
     * Cache of discovered columns metadata per table.
     * Format: [ 'table_name' => [ 'columns' => [...names...], 'primary' => 'id', 'types' => [...] ] ]
     *
     * @var array
     */
    protected static array $columnsCache = [];

    public function attributes(): array
    {
        $table = static::tableName();
        $this->ensureColumnsCached($table);
        return self::$columnsCache[$table]['columns'] ?? [];
    }

    public static function primaryKey(): string
    {
        $table = static::tableName();
        static::ensureStaticColumnsCached($table);
        return self::$columnsCache[$table]['primary'] ?? 'id';
    }

    protected function ensureColumnsCached(string $table): void
    {
        if (isset(self::$columnsCache[$table])) {
            return;
        }

        $pdo = Application::$app->db->pdo;

        $stmt = $pdo->prepare(
            "SELECT column_name, data_type, udt_name, is_identity, column_default, character_maximum_length, numeric_precision, numeric_scale " .
            "FROM information_schema.columns " .
            "WHERE table_name = :table AND table_schema = current_schema() " .
            "ORDER BY ordinal_position"
        );
        $stmt->execute(['table' => $table]);
        $cols = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $pkStmt = $pdo->prepare(
            "SELECT kcu.column_name " .
            "FROM information_schema.table_constraints tc " .
            "JOIN information_schema.key_column_usage kcu " .
            "  ON tc.constraint_name = kcu.constraint_name " .
            " AND tc.table_schema = kcu.table_schema " .
            "WHERE tc.table_name = :table " .
            "  AND tc.table_schema = current_schema() " .
            "  AND tc.constraint_type = 'PRIMARY KEY' " .
            "ORDER BY kcu.ordinal_position"
        );
        $pkStmt->execute(['table' => $table]);
        $primaryKeys = $pkStmt->fetchAll(\PDO::FETCH_COLUMN);
        $primary = $primaryKeys[0] ?? null;

        $types = [];
        $names = [];

        foreach ($cols as $col) {
            $colName = $col['column_name'];
            $colType = $col['data_type'];
            if ($col['character_maximum_length'] !== null) {
                $colType .= "({$col['character_maximum_length']})";
            } elseif ($col['numeric_precision'] !== null) {
                $colType .= "({$col['numeric_precision']}";
                if ($col['numeric_scale'] !== null) {
                    $colType .= ",{$col['numeric_scale']}";
                }
                $colType .= ")";
            } else {
                $colType = $col['udt_name'] ?? $colType;
            }

            $types[$colName] = $colType;

            $isSerial = stripos($col['column_default'] ?? '', 'nextval(') !== false;
            $isIdentity = isset($col['is_identity']) && $col['is_identity'] === 'YES';
            $isAutoPk = $primary === $colName && ($isSerial || $isIdentity);

            if (!$isAutoPk) {
                $names[] = $colName;
            }
        }

        self::$columnsCache[$table] = [
            'columns' => $names,
            'primary' => $primary,
            'types' => $types,
        ];
    }

    protected static function ensureStaticColumnsCached(string $table): void
    {
        if (isset(self::$columnsCache[$table])) {
            return;
        }

        $instanceClass = static::class;
        $tmp = (new \ReflectionClass($instanceClass))->newInstanceWithoutConstructor();
        if (method_exists($tmp, 'ensureColumnsCached')) {
            $tmp->ensureColumnsCached($table);
        }
    }

    public function getColumnTypes(): array
    {
        $table = static::tableName();
        $this->ensureColumnsCached($table);
        return self::$columnsCache[$table]['types'] ?? [];
    }

    public static function getColumnTypesStatic(): array
    {
        $table = static::tableName();
        static::ensureStaticColumnsCached($table);
        return self::$columnsCache[$table]['types'] ?? [];
    }
}





?>