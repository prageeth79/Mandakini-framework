<?php

namespace app\core\db\schema;

use app\core\Application;
use PDO;

class PostgreSqlSchemaReflector implements SchemaReflectorInterface
{
    public function inspectTable(string $table): array
    {
        $pdo = Application::$app->db->pdo;

        // 1. Get primary key column name
        $primaryKeyStmt = $pdo->prepare("
            SELECT a.attname
            FROM pg_index i
            JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
            WHERE i.indrelid = :table::regclass
              AND i.indisprimary
        ");
        
        $primaryKey = null;
        try {
            $primaryKeyStmt->execute(['table' => $table]);
            $primaryKey = $primaryKeyStmt->fetchColumn() ?: null;
        } catch (\Throwable $e) {
            $primaryKey = 'id';
        }

        // 2. Query information_schema for columns and data types
        $stmt = $pdo->prepare("
            SELECT column_name, data_type, column_default, is_nullable
            FROM information_schema.columns
            WHERE table_name = :table
              AND table_schema = 'public'
            ORDER BY ordinal_position
        ");
        $stmt->execute(['table' => $table]);
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $names = [];
        $types = [];

        foreach ($cols as $col) {
            $colName = $col['column_name'];
            $dataType = strtolower($col['data_type']);
            $default = $col['column_default'] ?? '';

            $types[$colName] = $dataType;

            // Exclude auto-incrementing serial/identity columns from normal insert attributes
            $isSerial = str_contains($default, 'nextval(') || str_contains($default, 'identity');
            if (!($colName === $primaryKey && $isSerial)) {
                $names[] = $colName;
            }
        }

        return [
            'columns' => $names,
            'primary' => $primaryKey ?? 'id',
            'types'   => $types,
        ];
    }
}