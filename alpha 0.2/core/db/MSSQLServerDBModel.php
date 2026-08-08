<?php
namespace app\core\db;

use app\core\Application;

class MSSQLServerDBModel extends DBModel
{
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
            "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, " .
            "COLUMNPROPERTY(OBJECT_ID(QUOTENAME(TABLE_SCHEMA) + '.' + QUOTENAME(TABLE_NAME)), COLUMN_NAME, 'IsIdentity') AS IS_IDENTITY " .
            "FROM INFORMATION_SCHEMA.COLUMNS " .
            "WHERE TABLE_NAME = :table AND TABLE_SCHEMA = SCHEMA_NAME() " .
            "ORDER BY ORDINAL_POSITION"
        );
        $stmt->execute(['table' => $table]);
        $cols = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $pkStmt = $pdo->prepare(
            "SELECT KCU.COLUMN_NAME " .
            "FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC " .
            "JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KCU " .
            "  ON TC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME " .
            " AND TC.CONSTRAINT_SCHEMA = KCU.CONSTRAINT_SCHEMA " .
            "WHERE TC.TABLE_NAME = :table " .
            "  AND TC.TABLE_SCHEMA = SCHEMA_NAME() " .
            "  AND TC.CONSTRAINT_TYPE = 'PRIMARY KEY' " .
            "ORDER BY KCU.ORDINAL_POSITION"
        );
        $pkStmt->execute(['table' => $table]);
        $primaryKeys = $pkStmt->fetchAll(\PDO::FETCH_COLUMN);
        $primary = $primaryKeys[0] ?? null;

        $types = [];
        $names = [];

        foreach ($cols as $col) {
            $colName = $col['COLUMN_NAME'];
            $colType = $col['DATA_TYPE'];
            if ($col['CHARACTER_MAXIMUM_LENGTH'] !== null) {
                $colType .= "({$col['CHARACTER_MAXIMUM_LENGTH']})";
            } elseif ($col['NUMERIC_PRECISION'] !== null) {
                $colType .= "({$col['NUMERIC_PRECISION']}";
                if ($col['NUMERIC_SCALE'] !== null) {
                    $colType .= ",{$col['NUMERIC_SCALE']}";
                }
                $colType .= ")";
            }

            $types[$colName] = $colType;

            $isIdentity = isset($col['IS_IDENTITY']) && (int)$col['IS_IDENTITY'] === 1;
            $isAutoPk = $primary === $colName && $isIdentity;

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
