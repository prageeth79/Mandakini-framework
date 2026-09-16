<?php
namespace app\core\db\schema;

use app\core\Application;
use PDO;

class MySqlSchemaReflector implements SchemaReflectorInterface {

    public function inspectTable(string $table): array {
        $pdo = Application::$app->db->pdo;
        
        $stmt = $pdo->prepare("
            SELECT 
                c.COLUMN_NAME, 
                c.COLUMN_KEY, 
                c.EXTRA, 
                c.COLUMN_TYPE,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.UPDATE_RULE,
                rc.DELETE_RULE
            FROM INFORMATION_SCHEMA.COLUMNS c
            LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                ON c.TABLE_SCHEMA = kcu.TABLE_SCHEMA 
                AND c.TABLE_NAME = kcu.TABLE_NAME 
                AND c.COLUMN_NAME = kcu.COLUMN_NAME
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
                AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
            WHERE c.TABLE_SCHEMA = DATABASE() AND c.TABLE_NAME = :table 
            ORDER BY c.ORDINAL_POSITION
        ");
        $stmt->execute(['table' => $table]);
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $names = [];
        $primary = null;
        $types = [];
        $foreignKeys = [];

        foreach ($cols as $col) {
            $colName = $col['COLUMN_NAME'];
            $colKey = $col['COLUMN_KEY'] ?? '';
            $extra = $col['EXTRA'] ?? '';
            $colType = $col['COLUMN_TYPE'] ?? null;

            if ($colType !== null) {
                $types[$colName] = $colType;
            }

            if ($colKey === 'PRI' && $primary === null) {
                $primary = $colName;
            }

            // Capture foreign key relationships if present
            if (!empty($col['REFERENCED_TABLE_NAME'])) {
                $foreignKeys[$colName] = [
                    'referenced_table'  => $col['REFERENCED_TABLE_NAME'],
                    'referenced_column' => $col['REFERENCED_COLUMN_NAME'],
                    'on_update'         => $col['UPDATE_RULE'],
                    'on_delete'         => $col['DELETE_RULE'],
                ];
            }

            // Omit auto-increment primary key from writable attributes
            if (!($colKey === 'PRI' && stripos($extra, 'auto_increment') !== false)) {
                $names[] = $colName;
            }
        }

        return [
            'columns'      => $names,
            'primary'      => $primary ?? 'id',
            'types'        => $types,
            'foreign_keys' => $foreignKeys,
        ];
    }
}