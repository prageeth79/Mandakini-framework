<?php
namespace app\core\db\schema;

use app\core\Application;
use PDO;

class SqliteSchemaReflector implements SchemaReflectorInterface {

    public function inspectTable(string $table): array {
        $pdo = Application::$app->db->pdo;
        
        // Escape double quotes to safely format identifier inside PRAGMA calls
        $safeTable = str_replace('"', '""', $table);

        // 1. Fetch Foreign Key Constraints
        // PRAGMA foreign_key_list returns: id, seq, table, from, to, on_update, on_delete, match
        $fkStmt = $pdo->query("PRAGMA foreign_key_list(\"{$safeTable}\")");
        $fks = $fkStmt ? $fkStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $foreignKeys = [];
        foreach ($fks as $fk) {
            $foreignKeys[$fk['from']] = [
                'referenced_table'  => $fk['table'],
                'referenced_column' => $fk['to'],
                'on_update'         => $fk['on_update'],
                'on_delete'         => $fk['on_delete'],
            ];
        }

        // 2. Fetch Columns and Table Info
        // PRAGMA table_info returns: cid, name, type, notnull, dflt_value, pk
        $stmt = $pdo->query("PRAGMA table_info(\"{$safeTable}\")");
        $cols = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $names = [];
        $primary = null;
        $types = [];

        foreach ($cols as $col) {
            $colName = $col['name'];
            $colType = $col['type'];
            $isPk    = (int) $col['pk'] > 0;

            $types[$colName] = $colType;

            if ($isPk && $primary === null) {
                $primary = $colName;
            }

            // In SQLite, an INTEGER PRIMARY KEY column auto-increments by default
            $isAutoIncrementPk = $isPk && strcasecmp(trim($colType), 'INTEGER') === 0;

            if (!$isAutoIncrementPk) {
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