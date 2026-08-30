<?php
namespace app\core\db;

/**
 * MySQL-backed DBModel that automatically discovers table columns
 * and provides sensible defaults for `attributes()` and `primaryKey()`.
 */

abstract class MySqlDBColumnsModel extends MySqlDBModel
{
	protected const COLUMN_MODEL = true;
    /**
     * Runtime storage for database columns.
     */
    protected array $columnData = [];

    /**
     * Initialize all database columns.
     */
    public function loadDBColumns(): void
    {
        $types = $this->getColumnTypes();

        foreach ($this->attributes() as $field) {

            if (array_key_exists($field, $this->columnData)) {
                continue;
            }

            $this->columnData[$field] =
                $this->defaultColumnValue(
                    $types[$field] ?? null
                );
        }

        // Also prepare primary key
        $primaryKey = static::primaryKey();

        if (
            $primaryKey !== '' &&
            !array_key_exists($primaryKey, $this->columnData)
        ) {
            $this->columnData[$primaryKey] = null;
        }
    }

    /**
     * Get a column value.
     */
    public function __get(string $column): mixed
    {
        $this->loadDBColumns();

        return $this->columnData[$column] ?? null;
    }

    /**
     * Set a column value.
     */
    public function __set(string $column, mixed $value): void
    {
        $this->loadDBColumns();

        $types = $this->getColumnTypes();

        if (isset($types[$column])) {
            $value = $this->castColumnValue(
                $value,
                $types[$column]
            );
        }

        $this->columnData[$column] = $value;
    }

    /**
     * Determine the default PHP value from MySQL type.
     */
    protected function defaultColumnValue(
        ?string $type
    ): mixed {

        if ($type === null) {
            return null;
        }

        $type = strtolower($type);

        // Boolean
        if (str_starts_with($type, 'tinyint(1)')) {
            return false;
        }

        // Integer
        if (
            str_contains($type, 'tinyint') ||
            str_contains($type, 'smallint') ||
            str_contains($type, 'mediumint') ||
            str_contains($type, 'int') ||
            str_contains($type, 'bigint')
        ) {
            return 0;
        }

        // Decimal / floating
        if (
            str_contains($type, 'decimal') ||
            str_contains($type, 'numeric') ||
            str_contains($type, 'float') ||
            str_contains($type, 'double')
        ) {
            return 0.0;
        }

        // Everything else
        return '';
    }

    /**
     * Convert a value to the appropriate PHP type.
     */
    protected function castColumnValue(
        mixed $value,
        string $type
    ): mixed {

        if ($value === null) {
            return null;
        }

        $type = strtolower($type);

        // Boolean
        if (str_starts_with($type, 'tinyint(1)')) {
            return (bool) $value;
        }

        // Integer
        if (
            str_contains($type, 'tinyint') ||
            str_contains($type, 'smallint') ||
            str_contains($type, 'mediumint') ||
            str_contains($type, 'int') ||
            str_contains($type, 'bigint')
        ) {
            return (int) $value;
        }

        // Floating point
        if (
            str_contains($type, 'decimal') ||
            str_contains($type, 'numeric') ||
            str_contains($type, 'float') ||
            str_contains($type, 'double')
        ) {
            return (float) $value;
        }

        // String
        return (string) $value;
    }
}