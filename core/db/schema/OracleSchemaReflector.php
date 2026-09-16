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

        // 2. Discover Foreign Keys
        $fkStmt = $pdo->prepare("
            SELECT 
                a.column_name AS local_column,
                c_pk.table_name AS referenced_table,
                b.column_name AS referenced_column,
                c.delete_rule AS delete_rule
            FROM all_cons_columns a
            JOIN all_constraints c 
              ON a.constraint_name = c.constraint_name 
             AND a.owner = c.owner
            JOIN all_constraints c_pk 
              ON c.r_constraint_name = c_pk.constraint_name 
             AND c.r_owner = c_pk.owner
            JOIN all_cons_columns b 
              ON c_pk.constraint_name = b.constraint_name 
             AND c_pk.owner = b.owner 
             AND a.position = b.position
            WHERE c.constraint_type = 'R'
              AND a.table_name = :table_name
        ");

        $foreignKeys = [];
        try {
            $fkStmt->execute(['table_name' => $tableName]);
            $fks = $fkStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fks as $fk) {
                $colName = strtolower($fk['LOCAL_COLUMN']);
                $foreignKeys[$colName] = [
                    'referenced_table'  => strtolower($fk['REFERENCED_TABLE']),
                    'referenced_column' => strtolower($fk['REFERENCED_COLUMN']),
                    'on_delete'         => $fk['DELETE_RULE'], // NO ACTION, CASCADE, SET NULL
                    'on_update'         => 'NO ACTION',        // Oracle does not natively support ON UPDATE
                ];
            }
        } catch (\Throwable $e) {
            $foreignKeys = [];
        }

        // 3. Discover columns, data types, and identity generation
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
            'columns'      => $names,
            'primary'      => $primaryKey ?? 'id',
            'types'        => $types,
            'foreign_keys' => $foreignKeys,
        ];
    }
}