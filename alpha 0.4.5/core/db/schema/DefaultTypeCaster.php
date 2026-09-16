<?php

namespace app\core\db\schema;

class DefaultTypeCaster implements TypeCasterInterface
{
    public function defaultValue(?string $dbType): mixed
    {
        return '';
    }

    public function castValue(mixed $value, ?string $dbType): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return str_contains((string) $value, '.') ? (float) $value : (int) $value;
        }

        if (is_bool($value)) {
            return $value;
        }

        return (string) $value;
    }
}