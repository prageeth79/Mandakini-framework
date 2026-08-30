<?php

namespace app\core\db;

/**
 * Oracle-backed DBModel that automatically discovers table columns
 * and provides sensible defaults for attributes() and primaryKey().
 */
abstract class OracleDBColumnsModel extends OracleDBModel
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
     * Oracle data type.
     */
    protected function defaultColumnValue(
        ?string $type
    ): mixed {

        if ($type === null) {
            return null;
        }

        $type = strtoupper(trim($type));

        return match ($type) {

            /*
             * Boolean
             *
             * Oracle BOOLEAN is available in modern Oracle
             * versions and PL/SQL. Depending on the database
             * version/schema, boolean columns may not exist.
             */
            'BOOLEAN'
                => false,

            /*
             * Integer / NUMBER
             *
             * Oracle commonly uses NUMBER for integers,
             * decimals and floating point values.
             */
            'INTEGER',
            'INT',
            'SMALLINT'
                => 0,

            /*
             * Floating / numeric
             */
            'NUMBER',
            'DECIMAL',
            'NUMERIC',
            'FLOAT',
            'BINARY_FLOAT',
            'BINARY_DOUBLE'
                => 0.0,

            /*
             * Character / string
             */
            'CHAR',
            'VARCHAR',
            'VARCHAR2',
            'NCHAR',
            'NVARCHAR2',
            'LONG'
                => '',

            /*
             * Date / time
             */
            'DATE',
            'TIMESTAMP',
            'TIMESTAMP WITH TIME ZONE',
            'TIMESTAMP WITH LOCAL TIME ZONE'
                => null,

            /*
             * Large objects
             */
            'CLOB',
            'NCLOB',
            'BLOB'
                => null,

            /*
             * Binary / raw
             */
            'RAW',
            'LONG RAW'
                => null,

            /*
             * ROWID
             */
            'ROWID',
            'UROWID'
                => null,

            /*
             * XML
             */
            'XMLTYPE'
                => null,

            /*
             * Default
             */
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

        $type = strtoupper(trim($type));

        return match ($type) {

            /*
             * Boolean
             */
            'BOOLEAN'
                => filter_var(
                    $value,
                    FILTER_VALIDATE_BOOLEAN
                ),

            /*
             * Integer
             */
            'INTEGER',
            'INT',
            'SMALLINT'
                => (int) $value,

            /*
             * Numeric
             *
             * WARNING:
             * Oracle NUMBER can have very high precision.
             * Casting every NUMBER to float can lose precision.
             */
            'NUMBER',
            'DECIMAL',
            'NUMERIC',
            'FLOAT',
            'BINARY_FLOAT',
            'BINARY_DOUBLE'
                => (float) $value,

            /*
             * Character / string
             */
            'CHAR',
            'VARCHAR',
            'VARCHAR2',
            'NCHAR',
            'NVARCHAR2',
            'LONG'
                => (string) $value,

            /*
             * Date / timestamp
             *
             * Oracle drivers commonly return these as strings
             * unless explicitly configured otherwise.
             */
            'DATE',
            'TIMESTAMP',
            'TIMESTAMP WITH TIME ZONE',
            'TIMESTAMP WITH LOCAL TIME ZONE'
                => (string) $value,

            /*
             * LOB / binary
             */
            'CLOB',
            'NCLOB',
            'BLOB',
            'RAW',
            'LONG RAW'
                => $value,

            /*
             * ROWID
             */
            'ROWID',
            'UROWID'
                => (string) $value,

            /*
             * XML
             */
            'XMLTYPE'
                => (string) $value,

            /*
             * Default
             */
            default
                => (string) $value,
        };
    }
}