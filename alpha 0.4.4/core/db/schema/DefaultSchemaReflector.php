<?php

namespace app\core\db\schema;

use app\core\Application;
use PDO;

class DefaultSchemaReflector implements SchemaReflectorInterface
{
    public function inspectTable(string $table): array
    {
        $pdo = Application::$app->db->pdo;
        
        // Execute a zero-row limit query to inspect column metadata safely across drivers
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE 1 = 0");
        $stmt->execute();

        $names = [];
        $types = [];
        $primaryKey = 'id';

        $columnCount = $stmt->columnCount();
        for ($i = 0; $i < $columnCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            if (!$meta) {
                continue;
            }

            $colName = $meta['name'];
            $typeName = $meta['native_type'] ?? 'string';

            $types[$colName] = strtolower($typeName);

            // Basic heuristic to skip 'id' column from insert list if present
            if ($colName !== 'id') {
                $names[] = $colName;
            } else {
                $primaryKey = 'id';
            }
        }

        return [
            'columns' => $names,
            'primary' => $primaryKey,
            'types'   => $types,
        ];
    }
}