<?php
namespace app\core\db\schema;

interface TypeCasterInterface {
    public function defaultValue(?string $dbType): mixed;
    public function castValue(mixed $value, ?string $dbType): mixed;
}