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

        // 2. Discover columns, types, and identity flags
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
            'columns' => $names,
            'primary' => $primaryKey ?? 'id',
            'types'   => $types,
        ];
    }
}