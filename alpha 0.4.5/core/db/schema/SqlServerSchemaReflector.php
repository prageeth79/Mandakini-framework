<?php

namespace app\core\db\schema;

use app\core\Application;
use PDO;

class SqlServerSchemaReflector implements SchemaReflectorInterface
{
    public function inspectTable(string $table): array
    {
        $pdo = Application::$app->db->pdo;

        // 1. Discover Primary Key column name
        $primaryKeyStmt = $pdo->prepare("
            SELECT c.name AS column_name
            FROM sys.indexes i
            INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
            INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
            WHERE i.is_primary_key = 1
              AND i.object_id = OBJECT_ID(:table)
        ");

        $primaryKey = null;
        try {
            $primaryKeyStmt->execute(['table' => $table]);
            $primaryKey = $primaryKeyStmt->fetchColumn() ?: null;
        } catch (\Throwable $e) {
            $primaryKey = 'id';
        }

        // 2. Discover Foreign Keys
        $fkStmt = $pdo->prepare("
            SELECT 
                parent_col.name AS local_column,
                OBJECT_NAME(fk.referenced_object_id) AS referenced_table,
                ref_col.name AS referenced_column,
                fk.update_referential_action_desc AS on_update,
                fk.delete_referential_action_desc AS on_delete
            FROM sys.foreign_keys fk
            INNER JOIN sys.foreign_key_columns fkc 
                ON fk.object_id = fkc.constraint_object_id
            INNER JOIN sys.columns parent_col 
                ON fkc.parent_object_id = parent_col.object_id 
               AND fkc.parent_column_id = parent_col.column_id
            INNER JOIN sys.columns ref_col 
                ON fkc.referenced_object_id = ref_col.object_id 
               AND fkc.referenced_column_id = ref_col.column_id
            WHERE fk.parent_object_id = OBJECT_ID(:table)
        ");

        $foreignKeys = [];
        try {
            $fkStmt->execute(['table' => $table]);
            $fks = $fkStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fks as $fk) {
                $foreignKeys[$fk['local_column']] = [
                    'referenced_table'  => $fk['referenced_table'],
                    'referenced_column' => $fk['referenced_column'],
                    'on_update'         => str_replace('_', ' ', $fk['on_update']), // e.g., NO_ACTION -> NO ACTION
                    'on_delete'         => str_replace('_', ' ', $fk['on_delete']),
                ];
            }
        } catch (\Throwable $e) {
            $foreignKeys = [];
        }

        // 3. Discover columns, types, and identity flags
        $stmt = $pdo->prepare("
            SELECT 
                c.name AS column_name,
                t.name AS data_type,
                c.is_identity
            FROM sys.columns c
            INNER JOIN sys.types t ON c.user_type_id = t.user_type_id
            WHERE c.object_id = OBJECT_ID(:table)
            ORDER BY c.column_id
        ");
        $stmt->execute(['table' => $table]);
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $names = [];
        $types = [];

        foreach ($cols as $col) {
            $colName = $col['column_name'];
            $dataType = strtolower($col['data_type']);
            $isIdentity = (bool) $col['is_identity'];

            $types[$colName] = $dataType;

            // Exclude auto-increment IDENTITY primary keys from normal insert lists
            if (!($colName === $primaryKey && $isIdentity)) {
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