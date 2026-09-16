<?php

namespace app\core\db\schema;

class OracleTypeCaster implements TypeCasterInterface
{
    public function defaultValue(?string $dbType): mixed
    {
        if ($dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if (preg_match('/(number|integer|float|binary_float|binary_double)/i', $type)) {
            return 0;
        }

        return '';
    }

    public function castValue(mixed $value, ?string $dbType): mixed
    {
        if ($value === null || $dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if (preg_match('/(number|integer)/i', $type)) {
            // Handle booleans stored as NUMBER(1) flags
            if (is_bool($value)) {
                return $value ? 1 : 0;
            }
            return (int) $value;
        }

        if (preg_match('/(float|binary_float|binary_double)/i', $type)) {
            return (float) $value;
        }

        return (string) $value;
    }
}