<?php
namespace app\core\db\schema;

class SqliteTypeCaster implements TypeCasterInterface {

    public function defaultValue(?string $dbType): mixed {
        if ($dbType === null) {
            return null;
        }

        $type = strtolower($dbType);

        if (str_contains($type, 'bool')) {
            return false;
        }

        if (str_contains($type, 'int')) {
            return 0;
        }

        if (preg_match('/(real|float|double|numeric|decimal)/i', $type)) {
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

        if (str_contains($type, 'bool')) {
            return (bool) $value;
        }

        if (str_contains($type, 'int')) {
            return (int) $value;
        }

        if (preg_match('/(real|float|double|numeric|decimal)/i', $type)) {
            return (float) $value;
        }

        if (str_contains($type, 'json') && is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return (string) $value;
    }
}