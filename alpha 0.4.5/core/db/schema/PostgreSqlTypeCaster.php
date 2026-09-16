<?php

namespace app\core\db\schema;

class PostgreSqlTypeCaster implements TypeCasterInterface
{
    public function defaultValue(?string $dbType): mixed
    {
        if ($dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if ($type === 'boolean') {
            return false;
        }

        if (preg_match('/(integer|smallint|bigint|serial|bigserial)/i', $type)) {
            return 0;
        }

        if (preg_match('/(numeric|decimal|real|double precision)/i', $type)) {
            return 0.0;
        }

        if (str_contains($type, 'json')) {
            return [];
        }

        return '';
    }

    public function castValue(mixed $value, ?string $dbType): mixed
    {
        if ($value === null || $dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if ($type === 'boolean') {
            if (is_string($value)) {
                return in_array(strtolower($value), ['t', 'true', '1', 'yes'], true);
            }
            return (bool) $value;
        }

        if (preg_match('/(integer|smallint|bigint|serial|bigserial)/i', $type)) {
            return (int) $value;
        }

        if (preg_match('/(numeric|decimal|real|double precision)/i', $type)) {
            return (float) $value;
        }

        if (str_contains($type, 'json')) {
            if (is_array($value)) {
                return $value;
            }
            return json_decode((string) $value, true) ?? [];
        }

        return (string) $value;
    }
}