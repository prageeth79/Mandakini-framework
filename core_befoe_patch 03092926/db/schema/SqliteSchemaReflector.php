<?php
namespace app\core\db\schema;

use app\core\Application;
use PDO;

class SqliteSchemaReflector implements SchemaReflectorInterface {

    public function inspectTable(string $table): array {
        $pdo = Application::$app->db->pdo;
        
        // SQLite PRAGMA table_info returns: cid, name, type, notnull, dflt_value, pk
        $stmt = $pdo->prepare("PRAGMA table_info(" . $pdo->quote($table) . ")");
        $stmt->execute();
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            'columns' => $names,
            'primary' => $primary ?? 'id',
            'types'   => $types,
        ];
    }
}