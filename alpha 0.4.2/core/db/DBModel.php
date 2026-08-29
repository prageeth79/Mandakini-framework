<?php

namespace app\core\db;

use app\core\Application;
use app\core\Model;
use PDO;

abstract class DBModel extends Model
{
    /*
    |--------------------------------------------------------------------------
    | MODEL DEFINITION
    |--------------------------------------------------------------------------
    */

    abstract public static function tableName(): string;

    abstract public function attributes(): array;

    abstract public static function primaryKey(): string;

    /**
     * Used by the application/model when calculated values
     * need to be generated.
     */
    abstract public function calculate(): bool;


    /*
    |--------------------------------------------------------------------------
    | QUERY BUILDER
    |--------------------------------------------------------------------------
    */

    /**
     * Start a new query for this model.
     *
     * Example:
     *
     * User::query()
     *     ->where('status', '=', 'active')
     *     ->get();
     */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(new static());
    }


    /**
     * Shortcut for a simple WHERE condition.
     *
     * Example:
     *
     * User::where('status', 'active')->get();
     *
     * User::where('marks', '>', 50)->get();
     */
    public static function where(
        string $column,
        string $operator,
        mixed $value = null
    ): QueryBuilder {
        return static::query()->where(
            $column,
            $operator,
            $value
        );
    }


    /**
     * Shortcut for OR WHERE.
     */
    public static function orWhere(
        string $column,
        string $operator,
        mixed $value = null
    ): QueryBuilder {
        return static::query()->orWhere(
            $column,
            $operator,
            $value
        );
    }


    /**
     * Get all records.
     *
     * Example:
     *
     * User::all();
     */
    public static function all(): array
    {
        return static::query()->get();
    }


    /**
     * Find the first record.
     *
     * Example:
     *
     * User::where('email', $email)->first();
     */
    public static function first(): ?static
    {
        return static::query()->first();
    }


    /**
     * Find a record by primary key.
     *
     * Example:
     *
     * User::find(10);
     */
    public static function find(
        mixed $id
    ): ?static {
        $primaryKey = static::primaryKey();

        return static::query()
            ->where($primaryKey, '=', $id)
            ->first();
    }


    /**
     * Find one record using the old associative condition format.
     *
     * Supported:
     *
     * [
     *     'status' => ['=', 'active'],
     *     'age'    => ['>', 18],
     * ]
     *
     * For multiple conditions on the SAME column, use the QueryBuilder
     * directly:
     *
     * User::query()
     *     ->where('marks', '>', 0)
     *     ->where('marks', '<', 100)
     *     ->first();
     */
    public static function findOne(
        array $where = []
    ): ?static {
        $query = static::query();

        static::applyLegacyWhere(
            $query,
            $where
        );

        return $query->first();
    }


    /**
     * Find all records.
     *
     * Legacy-compatible signature:
     *
     * findAll($where, $orderBy, $limit)
     *
     * Example:
     *
     * User::findAll(
     *     ['status' => ['=', 'active']],
     *     'name ASC',
     *     ['offset' => 0, 'row_count' => 20]
     * );
     */
    public static function findAll(
        array $where = [],
        ?string $orderBy = null,
        array $limit = []
    ): array {
        $query = static::query();

        static::applyLegacyWhere(
            $query,
            $where
        );

        if ($orderBy !== null && trim($orderBy) !== '') {
            static::applyLegacyOrderBy(
                $query,
                $orderBy
            );
        }

        if (
            isset($limit['offset']) &&
            isset($limit['row_count'])
        ) {
            $query
                ->offset((int) $limit['offset'])
                ->limit((int) $limit['row_count']);
        } elseif (count($limit) >= 2) {
            $query
                ->offset((int) $limit[0])
                ->limit((int) $limit[1]);
        }

        return $query->get();
    }


    /*
    |--------------------------------------------------------------------------
    | MODEL SAVE
    |--------------------------------------------------------------------------
    */

    /**
     * Insert this model into the database.
     *
     * Returns true when the insert succeeds.
     *
     * If the primary key is auto-incremented and its property is null,
     * the generated ID is assigned back to the model.
     */
    public function save(): bool
    {
        $attributes = $this->attributes();

        if (empty($attributes)) {
            throw new \InvalidArgumentException(
                'Model has no attributes to save.'
            );
        }

        $data = [];

        foreach ($attributes as $attribute) {
            $data[$attribute] = $this->{$attribute};
        }

        $success = static::query()->insert($data);

        if ($success) {
            $primaryKey = static::primaryKey();

            if (
                property_exists($this, $primaryKey) &&
                $this->{$primaryKey} === null
            ) {
                $id = Application::$app
                    ->db
                    ->pdo
                    ->lastInsertId();

                if ($id !== '') {
                    $this->{$primaryKey} =
                        is_numeric($id)
                            ? (int) $id
                            : $id;
                }
            }
        }

        return $success;
    }


    /*
    |--------------------------------------------------------------------------
    | MODEL UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Update this model.
     *
     * If no WHERE condition is supplied, the primary key is used.
     *
     * Example:
     *
     * $user->name = 'John';
     * $user->update();
     *
     * This becomes:
     *
     * UPDATE users
     * SET name = ...
     * WHERE id = ...
     */
    public function update(
        array $where = []
    ): int {
        $query = static::query();

        if (empty($where)) {
            $primaryKey = static::primaryKey();
            $primaryValue = $this->{$primaryKey} ?? null;

            if ($primaryValue === null) {
                throw new \InvalidArgumentException(
                    'Cannot update model without a primary key value.'
                );
            }

            $query->where(
                $primaryKey,
                '=',
                $primaryValue
            );
        } else {
            static::applyLegacyWhere(
                $query,
                $where
            );
        }

        $data = [];

        foreach ($this->attributes() as $attribute) {
            $data[$attribute] = $this->{$attribute};
        }

        return $query->update($data);
    }


    /*
    |--------------------------------------------------------------------------
    | MODEL DELETE
    |--------------------------------------------------------------------------
    */

    /**
     * Delete this model.
     *
     * If no WHERE condition is supplied, the primary key is used.
     */
    public function delete(
        array $where = []
    ): int {
        $query = static::query();

        if (empty($where)) {
            $primaryKey = static::primaryKey();
            $primaryValue = $this->{$primaryKey} ?? null;

            if ($primaryValue === null) {
                throw new \InvalidArgumentException(
                    'Cannot delete model without a primary key value.'
                );
            }

            $query->where(
                $primaryKey,
                '=',
                $primaryValue
            );
        } else {
            static::applyLegacyWhere(
                $query,
                $where
            );
        }

        return $query->delete();
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSACTIONS
    |--------------------------------------------------------------------------
    */

    public static function beginTransaction(): bool
    {
        return Application::$app
            ->db
            ->pdo
            ->beginTransaction();
    }


    public static function commitTransaction(): bool
    {
        return Application::$app
            ->db
            ->pdo
            ->commit();
    }


    public static function rollBackTransaction(): bool
    {
        return Application::$app
            ->db
            ->pdo
            ->rollBack();
    }


    /**
     * Execute code inside a transaction.
     */
    public static function transaction(
        callable $callback
    ): mixed {
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


    /*
    |--------------------------------------------------------------------------
    | PDO HELPERS
    |--------------------------------------------------------------------------
    */

    public static function prepare(
        string $sql
    ): \PDOStatement {
        return Application::$app
            ->db
            ->pdo
            ->prepare($sql);
    }


    /*
    |--------------------------------------------------------------------------
    | LEGACY WHERE CONVERTER
    |--------------------------------------------------------------------------
    */

    /**
     * Converts the old DBModel WHERE format into QueryBuilder calls.
     *
     * Old:
     *
     * [
     *     'name' => ['LIKE', 'John'],
     *     'age'  => ['>', 18],
     * ]
     *
     * New QueryBuilder calls:
     *
     * ->where('name', 'LIKE', 'John')
     * ->where('age', '>', 18)
     */
    protected static function applyLegacyWhere(
        QueryBuilder $query,
        array $where
    ): void {
        foreach ($where as $column => $condition) {

            /*
             * Simple format:
             *
             * ['status' => 'active']
             */
            if (!is_array($condition)) {
                $query->where(
                    $column,
                    '=',
                    $condition
                );

                continue;
            }

            if (count($condition) < 2) {
                throw new \InvalidArgumentException(
                    "Invalid WHERE condition for column: {$column}"
                );
            }

            $operator = $condition[0];
            $value = $condition[1];

            $query->where(
                $column,
                $operator,
                $value
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LEGACY ORDER BY CONVERTER
    |--------------------------------------------------------------------------
    */

    /**
     * Convert the old orderBy string into QueryBuilder calls.
     *
     * Supported:
     *
     * name ASC
     * name DESC
     */
    protected static function applyLegacyOrderBy(
        QueryBuilder $query,
        string $orderBy
    ): void {
        $parts = preg_split(
            '/\s+/',
            trim($orderBy)
        );

        if (empty($parts[0])) {
            return;
        }

        $column = $parts[0];

        $direction =
            strtoupper($parts[1] ?? 'ASC');

        if (
            !in_array(
                $direction,
                ['ASC', 'DESC'],
                true
            )
        ) {
            throw new \InvalidArgumentException(
                'Invalid ORDER BY direction.'
            );
        }

        $query->orderBy(
            $column,
            $direction
        );
    }
}

