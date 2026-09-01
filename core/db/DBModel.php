<?php

namespace app\core\db;

use app\core\Application;
use app\core\Model;

abstract class DBModel extends Model
{
    /*
    |--------------------------------------------------------------------------
    | SCHEMA DISCOVERY & CACHING
    |--------------------------------------------------------------------------
    */

    /**
     * Cache of schema metadata per table.
     * Format: ['table_name' => ['columns' => [...], 'primary' => 'id', 'types' => [...]]]
     */
    protected static array $schemaCache = [];

    /**
     * Dynamic runtime storage for model column values.
     */
    protected array $columnData = [];

    abstract public static function tableName(): string;

    abstract public function calculate(): bool;

    /**
     * Retrieve array of writable column names for this model.
     */
    public function attributes(): array
    {
        return static::getSchema()['columns'] ?? [];
    }

    /**
     * Retrieve primary key column name.
     */
    public static function primaryKey(): string
    {
        return static::getSchema()['primary'] ?? 'id';
    }

    /**
     * Retrieve column database types.
     */
    public function getColumnTypes(): array
    {
        return static::getSchema()['types'] ?? [];
    }

    /**
     * Fetch schema details using the active database driver's reflector strategy.
     */
    protected static function getSchema(): array
    {
        $table = static::tableName();

        if (!isset(self::$schemaCache[$table])) {
            $reflector = Application::$app->db->getSchemaReflector();
            self::$schemaCache[$table] = $reflector->inspectTable($table);
        }

        return self::$schemaCache[$table];
    }

    /*
    |--------------------------------------------------------------------------
    | DYNAMIC PROPERTY ACCESS & TYPE CASTING
    |--------------------------------------------------------------------------
    */

    /**
     * Populate column defaults dynamically from schema information.
     */
    public function loadDBColumns(): void
    {
        $types = $this->getColumnTypes();
        $caster = Application::$app->db->getTypeCaster();

        foreach ($this->attributes() as $field) {
            if (!array_key_exists($field, $this->columnData)) {
                $this->columnData[$field] = $caster->defaultValue($types[$field] ?? null);
            }
        }

        $primaryKey = static::primaryKey();
        if ($primaryKey !== '' && !array_key_exists($primaryKey, $this->columnData)) {
            $this->columnData[$primaryKey] = null;
        }
    }

    public function __get(string $column): mixed
    {
        // Support direct class properties if declared
        if (property_exists($this, $column)) {
            return $this->{$column};
        }

        $this->loadDBColumns();
        return $this->columnData[$column] ?? null;
    }

    public function __set(string $column, mixed $value): void
    {
        // Support direct class properties if declared
        if (property_exists($this, $column)) {
            $this->{$column} = $value;
            return;
        }

        $this->loadDBColumns();
        $types = $this->getColumnTypes();
        $caster = Application::$app->db->getTypeCaster();

        if (isset($types[$column])) {
            $value = $caster->castValue($value, $types[$column]);
        }

        $this->columnData[$column] = $value;
    }

    public function __isset(string $column): bool
    {
        if (property_exists($this, $column)) {
            return isset($this->{$column});
        }
        $this->loadDBColumns();
        return isset($this->columnData[$column]);
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY BUILDER INTERFACE
    |--------------------------------------------------------------------------
    */

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(new static());
    }

    public static function where(string $column, string $operator, mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function orWhere(string $column, string $operator, mixed $value = null): QueryBuilder
    {
        return static::query()->orWhere($column, $operator, $value);
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function first(): ?static
    {
        return static::query()->first();
    }

    public static function find(mixed $id): ?static
    {
        $primaryKey = static::primaryKey();
        return static::query()->where($primaryKey, '=', $id)->first();
    }

    public static function findOne(array $where = []): ?static
    {
        $query = static::query();
        static::applyLegacyWhere($query, $where);
        return $query->first();
    }

    public static function findAll(array $where = [], ?string $orderBy = null, array $limit = []): array
    {
        $query = static::query();
        static::applyLegacyWhere($query, $where);

        if ($orderBy !== null && trim($orderBy) !== '') {
            static::applyLegacyOrderBy($query, $orderBy);
        }

        if (isset($limit['offset']) && isset($limit['row_count'])) {
            $query->offset((int) $limit['offset'])->limit((int) $limit['row_count']);
        } elseif (count($limit) >= 2) {
            $query->offset((int) $limit[0])->limit((int) $limit[1]);
        }

        return $query->get();
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL PERSISTENCE (SAVE / UPDATE / DELETE)
    |--------------------------------------------------------------------------
    */

    public function save(): bool
    {
        $attributes = $this->attributes();

        if (empty($attributes)) {
            throw new \InvalidArgumentException('Model has no attributes to save.');
        }

        $data = [];
        foreach ($attributes as $attribute) {
            $data[$attribute] = $this->{$attribute};
        }

        $success = static::query()->insert($data);

        if ($success) {
            $primaryKey = static::primaryKey();
            if ($this->{$primaryKey} === null) {
                $id = Application::$app->db->pdo->lastInsertId();
                if ($id !== '') {
                    $this->{$primaryKey} = is_numeric($id) ? (int) $id : $id;
                }
            }
        }

        return $success;
    }

    public function update(array $where = []): int
    {
        $query = static::query();

        if (empty($where)) {
            $primaryKey = static::primaryKey();
            $primaryValue = $this->{$primaryKey} ?? null;

            if ($primaryValue === null) {
                throw new \InvalidArgumentException('Cannot update model without a primary key value.');
            }

            $query->where($primaryKey, '=', $primaryValue);
        } else {
            static::applyLegacyWhere($query, $where);
        }

        $data = [];
        foreach ($this->attributes() as $attribute) {
            $data[$attribute] = $this->{$attribute};
        }

        return $query->update($data);
    }

    public function delete(array $where = []): int
    {
        $query = static::query();

        if (empty($where)) {
            $primaryKey = static::primaryKey();
            $primaryValue = $this->{$primaryKey} ?? null;

            if ($primaryValue === null) {
                throw new \InvalidArgumentException('Cannot delete model without a primary key value.');
            }

            $query->where($primaryKey, '=', $primaryValue);
        } else {
            static::applyLegacyWhere($query, $where);
        }

        return $query->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTIONS & HELPER UTILITIES
    |--------------------------------------------------------------------------
    */

    public static function beginTransaction(): bool
    {
        return Application::$app->db->pdo->beginTransaction();
    }

    public static function commitTransaction(): bool
    {
        return Application::$app->db->pdo->commit();
    }

    public static function rollBackTransaction(): bool
    {
        return Application::$app->db->pdo->rollBack();
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = Application::$app->db->pdo;

        try {
            $pdo->beginTransaction();
            $result = $callback();
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function prepare(string $sql): \PDOStatement
    {
        return Application::$app->db->pdo->prepare($sql);
    }

    protected static function applyLegacyWhere(QueryBuilder $query, array $where): void
    {
        foreach ($where as $column => $condition) {
            if (!is_array($condition)) {
                $query->where($column, '=', $condition);
                continue;
            }

            if (count($condition) < 2) {
                throw new \InvalidArgumentException("Invalid WHERE condition for column: {$column}");
            }

            $query->where($column, $condition[0], $condition[1]);
        }
    }

    protected static function applyLegacyOrderBy(QueryBuilder $query, string $orderBy): void
    {
        $parts = preg_split('/\s+/', trim($orderBy));
        if (empty($parts[0])) {
            return;
        }

        $column = $parts[0];
        $direction = strtoupper($parts[1] ?? 'ASC');

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException('Invalid ORDER BY direction.');
        }

        $query->orderBy($column, $direction);
    }
}