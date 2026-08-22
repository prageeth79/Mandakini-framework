<?php

namespace app\core\db;

use app\core\Application;

class OracleDBModel extends DBModel
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
        $schema = $pdo->query("SELECT SYS_CONTEXT('USERENV', 'CURRENT_SCHEMA') FROM DUAL")->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT COLUMN_NAME, DATA_TYPE, DATA_LENGTH, DATA_PRECISION, DATA_SCALE, NULLABLE, IDENTITY_COLUMN " .
            "FROM ALL_TAB_COLUMNS " .
            "WHERE TABLE_NAME = :table AND OWNER = :schema " .
            "ORDER BY COLUMN_ID"
        );
        $stmt->execute(['table' => strtoupper($table), 'schema' => strtoupper($schema)]);
        $cols = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $pkStmt = $pdo->prepare(
            "SELECT cols.COLUMN_NAME " .
            "FROM ALL_CONSTRAINTS cons " .
            "JOIN ALL_CONS_COLUMNS cols ON cons.CONSTRAINT_NAME = cols.CONSTRAINT_NAME AND cons.OWNER = cols.OWNER " .
            "WHERE cons.TABLE_NAME = :table " .
            "  AND cons.OWNER = :schema " .
            "  AND cons.CONSTRAINT_TYPE = 'P' " .
            "ORDER BY cols.POSITION"
        );
        $pkStmt->execute(['table' => strtoupper($table), 'schema' => strtoupper($schema)]);
        $primaryKeys = $pkStmt->fetchAll(\PDO::FETCH_COLUMN);
        $primary = $primaryKeys[0] ?? null;

        $types = [];
        $names = [];

        foreach ($cols as $col) {
            $colName = $col['COLUMN_NAME'];
            $colType = $col['DATA_TYPE'];
            if ($col['DATA_TYPE'] === 'VARCHAR2' || $col['DATA_TYPE'] === 'CHAR' || $col['DATA_TYPE'] === 'NVARCHAR2' || $col['DATA_TYPE'] === 'NCHAR') {
                $colType .= "({$col['DATA_LENGTH']})";
            } elseif ($col['DATA_TYPE'] === 'NUMBER') {
                if ($col['DATA_PRECISION'] !== null) {
                    $colType .= "({$col['DATA_PRECISION']}";
                    if ($col['DATA_SCALE'] !== null) {
                        $colType .= ",{$col['DATA_SCALE']}";
                    }
                    $colType .= ")";
                }
            }

            $types[$colName] = $colType;

            $isIdentity = isset($col['IDENTITY_COLUMN']) && strtoupper($col['IDENTITY_COLUMN']) === 'YES';
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
