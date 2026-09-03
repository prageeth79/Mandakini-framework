<?php

namespace app\core\db\schema;

class SqlServerTypeCaster implements TypeCasterInterface
{
    public function defaultValue(?string $dbType): mixed
    {
        if ($dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if ($type === 'bit') {
            return false;
        }

        if (preg_match('/(int|bigint|smallint|tinyint)/i', $type)) {
            return 0;
        }

        if (preg_match('/(decimal|numeric|float|real|money|smallmoney)/i', $type)) {
            return 0.0;
        }

        return '';
    }

    public function castValue(mixed $value, ?string $dbType): mixed
    {
        if ($value === null || $dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        // SQL Server uses BIT for booleans (0 or 1)
        if ($type === 'bit') {
            if (is_string($value)) {
                return in_array(strtolower($value), ['1', 'true', 'yes', 't'], true) ? 1 : 0;
            }
            return $value ? 1 : 0;
        }

        if (preg_match('/(int|bigint|smallint|tinyint)/i', $type)) {
            return (int) $value;
        }

        if (preg_match('/(decimal|numeric|float|real|money|smallmoney)/i', $type)) {
            return (float) $value;
        }

        return (string) $value;
    }
}