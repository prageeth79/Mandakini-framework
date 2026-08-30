<?php

namespace app\core\db\postgresql;

/**
 * PostgreSQL-backed DBModel that automatically discovers table columns
 * and provides sensible defaults for `attributes()` and `primaryKey()`.
 */
abstract class PostgreSQLDBColumnsModel extends PostgreSQLDBModel
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
     * Determine the default PHP value from PostgreSQL type.
     */
    protected function defaultColumnValue(
        ?string $type
    ): mixed {

        if ($type === null) {
            return null;
        }

        $type = strtolower($type);

        /*
         * Boolean
         */
        if (
            $type === 'boolean' ||
            $type === 'bool'
        ) {
            return false;
        }

        /*
         * Integer types
         */
        if (
            $type === 'smallint' ||
            $type === 'integer' ||
            $type === 'int' ||
            $type === 'bigint' ||
            str_contains($type, 'int')
        ) {
            return 0;
        }

        /*
         * Decimal / floating point
         */
        if (
            $type === 'numeric' ||
            $type === 'decimal' ||
            $type === 'real' ||
            $type === 'double precision'
        ) {
            return 0.0;
        }

        /*
         * Everything else
         */
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

        /*
         * Boolean
         */
        if (
            $type === 'boolean' ||
            $type === 'bool'
        ) {
            return (bool) $value;
        }

        /*
         * Integer
         */
        if (
            $type === 'smallint' ||
            $type === 'integer' ||
            $type === 'int' ||
            $type === 'bigint' ||
            str_contains($type, 'int')
        ) {
            return (int) $value;
        }

        /*
         * Floating point / numeric
         */
        if (
            $type === 'decimal' ||
            $type === 'numeric' ||
            $type === 'real' ||
            $type === 'double precision'
        ) {
            return (float) $value;
        }

        /*
         * PostgreSQL date/time values are normally returned
         * as strings, so leave them as strings.
         */
        return (string) $value;
    }
}