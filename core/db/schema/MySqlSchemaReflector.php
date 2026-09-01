<?php
namespace app\core\db\schema;

use app\core\Application;
use PDO;

class MySqlSchemaReflector implements SchemaReflectorInterface {

    public function inspectTable(string $table): array {
        $pdo = Application::$app->db->pdo;
        
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME, COLUMN_KEY, EXTRA, COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table 
            ORDER BY ORDINAL_POSITION
        ");
        $stmt->execute(['table' => $table]);
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $names = [];
        $primary = null;
        $types = [];

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

            // Omit auto-increment primary key from writable attributes
            if (!($colKey === 'PRI' && stripos($extra, 'auto_increment') !== false)) {
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