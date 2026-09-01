<?php

namespace app\core\db\mssql;

/**
 * Microsoft SQL Server-backed DBModel that automatically discovers
 * table columns and provides sensible defaults for attributes()
 * and primaryKey().
 */
abstract class MSSQLServerDBColumnsModel extends MSSQLServerDBModel
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
    public function __set(
        string $column,
        mixed $value
    ): void {

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
     * Determine the default PHP value from
     * Microsoft SQL Server data type.
     */
    protected function defaultColumnValue(
        ?string $type
    ): mixed {

        if ($type === null) {
            return null;
        }

        $type = strtolower(trim($type));

        return match ($type) {

            // Boolean
            'bit'
                => false,

            // Integer
            'tinyint',
            'smallint',
            'int',
            'integer',
            'bigint'
                => 0,

            // Decimal / floating point
            'decimal',
            'numeric',
            'money',
            'smallmoney',
            'real',
            'float'
                => 0.0,

            // UUID / GUID
            'uniqueidentifier'
                => null,

            // Date / Time
            'date',
            'datetime',
            'datetime2',
            'smalldatetime',
            'datetimeoffset',
            'time'
                => null,

            // Binary
            'binary',
            'varbinary',
            'image',
                => null,

            // XML
            'xml'
                => null,

            // Character / String
            'char',
            'varchar',
            'text',
            'nchar',
            'nvarchar',
            'ntext'
                => '',

            // Default
            default
                => '',
        };
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

        $type = strtolower(trim($type));

        return match ($type) {

            // Boolean
            'bit'
                => filter_var(
                    $value,
                    FILTER_VALIDATE_BOOLEAN
                ),

            // Integer
            'tinyint',
            'smallint',
            'int',
            'integer',
            'bigint'
                => (int) $value,

            // Decimal / floating point
            'decimal',
            'numeric',
            'money',
            'smallmoney',
            'real',
            'float'
                => (float) $value,

            // UUID / GUID
            'uniqueidentifier'
                => (string) $value,

            // Date / time
            'date',
            'datetime',
            'datetime2',
            'smalldatetime',
            'datetimeoffset',
            'time'
                => (string) $value,

            // Binary
            'binary',
            'varbinary',
            'image'
                => $value,

            // XML
            'xml'
                => (string) $value,

            // String
            'char',
            'varchar',
            'text',
            'nchar',
            'nvarchar',
            'ntext'
                => (string) $value,

            // Default
            default
                => (string) $value,
        };
    }
}