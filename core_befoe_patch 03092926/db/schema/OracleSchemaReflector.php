<?php

namespace app\core\db\schema;

use app\core\Application;
use PDO;

class OracleSchemaReflector implements SchemaReflectorInterface
{
    public function inspectTable(string $table): array
    {
        $pdo = Application::$app->db->pdo;
        $tableName = strtoupper($table);

        // 1. Discover Primary Key column name
        $primaryKeyStmt = $pdo->prepare("
            SELECT cols.column_name
            FROM all_constraints cons
            JOIN all_cons_columns cols 
              ON cons.constraint_name = cols.constraint_name 
             AND cons.owner = cols.owner
            WHERE cons.constraint_type = 'P'
              AND cons.table_name = :table_name
            ORDER BY cols.position
        ");

        $primaryKey = null;
        try {
            $primaryKeyStmt->execute(['table_name' => $tableName]);
            $rawPk = $primaryKeyStmt->fetchColumn();
            $primaryKey = $rawPk ? strtolower($rawPk) : null;
        } catch (\Throwable $e) {
            $primaryKey = 'id';
        }

        // 2. Discover columns, data types, and identity generation
        $stmt = $pdo->prepare("
            SELECT 
                column_name,
                data_type,
                identity_column
            FROM all_tab_cols
            WHERE table_name = :table_name
              AND hidden_column = 'NO'
            ORDER BY column_id
        ");
        $stmt->execute(['table_name' => $tableName]);
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $names = [];
        $types = [];

        foreach ($cols as $col) {
            $colName = strtolower($col['COLUMN_NAME']);
            $dataType = strtolower($col['DATA_TYPE']);
            $isIdentity = isset($col['IDENTITY_COLUMN']) && strtoupper($col['IDENTITY_COLUMN']) === 'YES';

            $types[$colName] = $dataType;

            // Exclude auto-incrementing IDENTITY primary key columns from normal insert operations
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