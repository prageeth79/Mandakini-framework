<?php
namespace app\core\db\schema;

interface SchemaReflectorInterface {
    /**
     * Inspect database metadata for a table.
     * Must return: [ 'columns' => [...], 'primary' => 'id', 'types' => [...] ]
     */
    public function inspectTable(string $table): array;
}