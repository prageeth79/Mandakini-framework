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

        // 2. Get foreign key constraints
        $fkStmt = $pdo->prepare("
            SELECT
                kcu.column_name,
                ccu.table_name AS referenced_table,
                ccu.column_name AS referenced_column,
                rc.update_rule,
                rc.delete_rule
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name
             AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage ccu
              ON ccu.constraint_name = tc.constraint_name
             AND ccu.table_schema = tc.table_schema
            JOIN information_schema.referential_constraints rc
              ON rc.constraint_name = tc.constraint_name
             AND rc.constraint_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
              AND tc.table_name = :table
              AND tc.table_schema = 'public'
        ");

        $foreignKeys = [];
        try {
            $fkStmt->execute(['table' => $table]);
            $fks = $fkStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fks as $fk) {
                $foreignKeys[$fk['column_name']] = [
                    'referenced_table'  => $fk['referenced_table'],
                    'referenced_column' => $fk['referenced_column'],
                    'on_update'         => $fk['update_rule'],
                    'on_delete'         => $fk['delete_rule'],
                ];
            }
        } catch (\Throwable $e) {
            $foreignKeys = [];
        }

        // 3. Query information_schema for columns and data types
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
            'columns'      => $names,
            'primary'      => $primaryKey ?? 'id',
            'types'        => $types,
            'foreign_keys' => $foreignKeys,
        ];
    }
}