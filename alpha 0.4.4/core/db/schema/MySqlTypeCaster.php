<?php
namespace app\core\db\schema;

class MySqlTypeCaster implements TypeCasterInterface {

    public function defaultValue(?string $dbType): mixed {
        if ($dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if (str_starts_with($type, 'tinyint(1)')) {
            return false;
        }

        if (preg_match('/(int|bigint|smallint|tinyint|mediumint)/i', $type)) {
            return 0;
        }

        if (preg_match('/(decimal|numeric|float|double)/i', $type)) {
            return 0.0;
        }

        if (str_contains($type, 'json')) {
            return [];
        }

        return '';
    }

    public function castValue(mixed $value, ?string $dbType): mixed {
        if ($value === null || $dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if (str_starts_with($type, 'tinyint(1)')) {
            return (bool) $value;
        }

        if (preg_match('/(int|bigint|smallint|tinyint|mediumint)/i', $type)) {
            return (int) $value;
        }

        if (preg_match('/(decimal|numeric|float|double)/i', $type)) {
            return (float) $value;
        }

        if (str_contains($type, 'json') && is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return (string) $value;
    }
}