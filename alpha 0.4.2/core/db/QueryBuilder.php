<?php

namespace app\core\db;

use PDO;
use InvalidArgumentException;

class QueryBuilder
{
    protected DBModel $model;
    protected string $table;

    protected array $selects = ['*'];
    protected array $wheres = [];
    protected array $joins = [];
    protected array $bindings = [];

    protected array $groups = [];
    protected array $havings = [];

    protected ?string $orderBy = null;
    protected ?int $limit = null;
    protected ?int $offset = null;

    protected array $allowedOperators = [
        '=',
        '!=',
        '<>',
        '>',
        '<',
        '>=',
        '<=',
        'LIKE',
        'NOT LIKE',
        'IN',
        'NOT IN',
        'IS',
        'IS NOT',
    ];

    protected array $allowedJoinTypes = [
        'INNER',
        'LEFT',
        'RIGHT',
        'FULL',
    ];


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(DBModel $model)
    {
        $this->model = $model;
        $this->table = $model::tableName();
    }


    /*
    |--------------------------------------------------------------------------
    | SELECT
    |--------------------------------------------------------------------------
    */

    public function select(array|string ...$columns): self
    {
        if (
            count($columns) === 1 &&
            is_array($columns[0])
        ) {
            $columns = $columns[0];
        }

        $this->selects = empty($columns)
            ? ['*']
            : $columns;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | ADD SELECT
    |--------------------------------------------------------------------------
    */

    public function addSelect(array|string ...$columns): self
    {
        if (
            count($columns) === 1 &&
            is_array($columns[0])
        ) {
            $columns = $columns[0];
        }

        $this->selects = array_merge(
            $this->selects,
            $columns
        );

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE
    |--------------------------------------------------------------------------
    */

    public function where(
        string $column,
        string $operator,
        mixed $value = null
    ): self {

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->addWhere(
            $column,
            $operator,
            $value,
            'AND'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OR WHERE
    |--------------------------------------------------------------------------
    */

    public function orWhere(
        string $column,
        string $operator,
        mixed $value = null
    ): self {

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->addWhere(
            $column,
            $operator,
            $value,
            'OR'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INTERNAL WHERE
    |--------------------------------------------------------------------------
    */

    protected function addWhere(
        string $column,
        string $operator,
        mixed $value,
        string $boolean = 'AND'
    ): self {

        $operator = strtoupper(
            trim($operator)
        );

        if (
            !in_array(
                $operator,
                $this->allowedOperators,
                true
            )
        ) {
            throw new InvalidArgumentException(
                "Invalid SQL operator: {$operator}"
            );
        }


        /*
        |--------------------------------------------------------------------------
        | NULL
        |--------------------------------------------------------------------------
        */

        if ($value === null) {

            if ($operator === '=') {
                return $this->whereNull(
                    $column,
                    false,
                    $boolean
                );
            }

            if (
                $operator === '!=' ||
                $operator === '<>'
            ) {
                return $this->whereNull(
                    $column,
                    true,
                    $boolean
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | IN
        |--------------------------------------------------------------------------
        */

        if (
            $operator === 'IN' ||
            $operator === 'NOT IN'
        ) {

            if (!is_array($value)) {
                throw new InvalidArgumentException(
                    "{$operator} requires an array."
                );
            }

            return $this->whereIn(
                $column,
                $value,
                $operator === 'NOT IN',
                $boolean
            );
        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL WHERE
        |--------------------------------------------------------------------------
        */

        $param = $this->parameterName(
            $column
        );

        $condition =
            "{$column} {$operator} {$param}";

        $this->appendWhere(
            $condition,
            $boolean
        );

        $this->bindings[$param] = $value;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE GROUP
    |--------------------------------------------------------------------------
    */

    public function whereGroup(
        callable $callback,
        string $boolean = 'AND'
    ): self {

        $nested = new static($this->model);

        /*
         * Important:
         * Copy current bindings so generated parameter
         * names remain unique.
         */
        $nested->bindings = $this->bindings;

        $callback($nested);

        if (empty($nested->wheres)) {
            return $this;
        }

        $condition =
            '(' .
            implode(' ', $nested->wheres) .
            ')';

        $this->appendWhere(
            $condition,
            $boolean
        );

        $this->bindings =
            $nested->bindings;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE NESTED
    |--------------------------------------------------------------------------
    */

    public function whereNested(
        callable $callback
    ): self {
        return $this->whereGroup(
            $callback,
            'AND'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OR WHERE NESTED
    |--------------------------------------------------------------------------
    */

    public function orWhereNested(
        callable $callback
    ): self {
        return $this->whereGroup(
            $callback,
            'OR'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE NULL
    |--------------------------------------------------------------------------
    */

    public function whereNull(
        string $column,
        bool $not = false,
        string $boolean = 'AND'
    ): self {

        $condition =
            "{$column} IS " .
            ($not ? 'NOT ' : '') .
            'NULL';

        $this->appendWhere(
            $condition,
            $boolean
        );

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | OR WHERE NULL
    |--------------------------------------------------------------------------
    */

    public function orWhereNull(
        string $column,
        bool $not = false
    ): self {

        return $this->whereNull(
            $column,
            $not,
            'OR'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE IN
    |--------------------------------------------------------------------------
    */

    public function whereIn(
        string $column,
        array $values,
        bool $not = false,
        string $boolean = 'AND'
    ): self {

        /*
         * Empty arrays:
         *
         * WHERE id IN ()
         *
         * is invalid SQL.
         */

        if (empty($values)) {

            $condition = $not
                ? '1 = 1'
                : '1 = 0';

            $this->appendWhere(
                $condition,
                $boolean
            );

            return $this;
        }


        $params = [];

        foreach ($values as $value) {

            $param =
                $this->parameterName(
                    $column
                );

            $params[] = $param;

            $this->bindings[$param] =
                $value;
        }


        $operator = $not
            ? 'NOT IN'
            : 'IN';

        $condition =
            "{$column} {$operator} (" .
            implode(', ', $params) .
            ')';

        $this->appendWhere(
            $condition,
            $boolean
        );

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | OR WHERE IN
    |--------------------------------------------------------------------------
    */

    public function orWhereIn(
        string $column,
        array $values
    ): self {

        return $this->whereIn(
            $column,
            $values,
            false,
            'OR'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE NOT IN
    |--------------------------------------------------------------------------
    */

    public function whereNotIn(
        string $column,
        array $values
    ): self {

        return $this->whereIn(
            $column,
            $values,
            true,
            'AND'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OR WHERE NOT IN
    |--------------------------------------------------------------------------
    */

    public function orWhereNotIn(
        string $column,
        array $values
    ): self {

        return $this->whereIn(
            $column,
            $values,
            true,
            'OR'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE BETWEEN
    |--------------------------------------------------------------------------
    */

    public function whereBetween(
        string $column,
        mixed $min,
        mixed $max,
        string $boolean = 'AND'
    ): self {

        $param1 =
            $this->parameterName(
                $column . '_min'
            );

        $param2 =
            $this->parameterName(
                $column . '_max'
            );

        $condition =
            "{$column} BETWEEN {$param1} AND {$param2}";

        $this->appendWhere(
            $condition,
            $boolean
        );

        $this->bindings[$param1] = $min;
        $this->bindings[$param2] = $max;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | OR WHERE BETWEEN
    |--------------------------------------------------------------------------
    */

    public function orWhereBetween(
        string $column,
        mixed $min,
        mixed $max
    ): self {

        return $this->whereBetween(
            $column,
            $min,
            $max,
            'OR'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE NOT BETWEEN
    |--------------------------------------------------------------------------
    */

    public function whereNotBetween(
        string $column,
        mixed $min,
        mixed $max
    ): self {

        $param1 =
            $this->parameterName(
                $column . '_min'
            );

        $param2 =
            $this->parameterName(
                $column . '_max'
            );

        $condition =
            "{$column} NOT BETWEEN " .
            "{$param1} AND {$param2}";

        $this->appendWhere(
            $condition,
            'AND'
        );

        $this->bindings[$param1] = $min;
        $this->bindings[$param2] = $max;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | JOIN
    |--------------------------------------------------------------------------
    */

    public function join(
        string $table,
        string $first,
        string $operator,
        string $second,
        string $type = 'INNER'
    ): self {

        $type = strtoupper(
            $type
        );

        if (
            !in_array(
                $type,
                $this->allowedJoinTypes,
                true
            )
        ) {
            throw new InvalidArgumentException(
                "Invalid join type: {$type}"
            );
        }

        $this->joins[] =
            "{$type} JOIN {$table} " .
            "ON {$first} {$operator} {$second}";

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | LEFT JOIN
    |--------------------------------------------------------------------------
    */

    public function leftJoin(
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {

        return $this->join(
            $table,
            $first,
            $operator,
            $second,
            'LEFT'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RIGHT JOIN
    |--------------------------------------------------------------------------
    */

    public function rightJoin(
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {

        return $this->join(
            $table,
            $first,
            $operator,
            $second,
            'RIGHT'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GROUP BY
    |--------------------------------------------------------------------------
    */

    public function groupBy(
        string|array ...$columns
    ): self {

        if (
            count($columns) === 1 &&
            is_array($columns[0])
        ) {
            $columns = $columns[0];
        }

        foreach ($columns as $column) {
            $this->groups[] = $column;
        }

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | HAVING
    |--------------------------------------------------------------------------
    */

    public function having(
        string $column,
        string $operator,
        mixed $value = null
    ): self {

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->addHaving(
            $column,
            $operator,
            $value,
            'AND'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | OR HAVING
    |--------------------------------------------------------------------------
    */

    public function orHaving(
        string $column,
        string $operator,
        mixed $value = null
    ): self {

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return $this->addHaving(
            $column,
            $operator,
            $value,
            'OR'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INTERNAL HAVING
    |--------------------------------------------------------------------------
    */

    protected function addHaving(
        string $column,
        string $operator,
        mixed $value,
        string $boolean
    ): self {

        $operator = strtoupper(
            trim($operator)
        );

        if (
            !in_array(
                $operator,
                $this->allowedOperators,
                true
            )
        ) {
            throw new InvalidArgumentException(
                "Invalid HAVING operator: {$operator}"
            );
        }

        $param =
            $this->parameterName(
                'having_' . $column
            );

        $condition =
            "{$column} {$operator} {$param}";

        $this->appendHaving(
            $condition,
            $boolean
        );

        $this->bindings[$param] =
            $value;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | ORDER BY
    |--------------------------------------------------------------------------
    */

    public function orderBy(
        string $column,
        string $direction = 'ASC'
    ): self {

        $direction =
            strtoupper($direction);

        if (
            !in_array(
                $direction,
                ['ASC', 'DESC'],
                true
            )
        ) {
            $direction = 'ASC';
        }

        $this->orderBy =
            "{$column} {$direction}";

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | LIMIT
    |--------------------------------------------------------------------------
    */

    public function limit(int $limit): self
    {
        if ($limit < 0) {
            throw new InvalidArgumentException(
                'Limit cannot be negative.'
            );
        }

        $this->limit = $limit;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | OFFSET
    |--------------------------------------------------------------------------
    */

    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new InvalidArgumentException(
                'Offset cannot be negative.'
            );
        }

        $this->offset = $offset;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | SQL
    |--------------------------------------------------------------------------
    */

    public function toSql(): string
    {
        $sql =
            'SELECT ' .
            implode(', ', $this->selects) .
            " FROM {$this->table}";


        /*
         * JOIN
         */

        if (!empty($this->joins)) {

            $sql .= ' ' .
                implode(
                    ' ',
                    $this->joins
                );
        }


        /*
         * WHERE
         */

        if (!empty($this->wheres)) {

            $sql .=
                ' WHERE ' .
                implode(
                    ' ',
                    $this->wheres
                );
        }


        /*
         * GROUP BY
         */

        if (!empty($this->groups)) {

            $sql .=
                ' GROUP BY ' .
                implode(
                    ', ',
                    $this->groups
                );
        }


        /*
         * HAVING
         */

        if (!empty($this->havings)) {

            $sql .=
                ' HAVING ' .
                implode(
                    ' ',
                    $this->havings
                );
        }


        /*
         * ORDER
         */

        if ($this->orderBy !== null) {

            $sql .=
                " ORDER BY {$this->orderBy}";
        }


        /*
         * LIMIT
         */

        if ($this->limit !== null) {

            $sql .=
                " LIMIT {$this->limit}";
        }


        /*
         * OFFSET
         */

        if ($this->offset !== null) {

            $sql .=
                " OFFSET {$this->offset}";
        }


        return $sql;
    }


    /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

    protected function execute()
    {
        $stmt = DBModel::prepare(
            $this->toSql()
        );

        foreach (
            $this->bindings
            as $param => $value
        ) {

            $stmt->bindValue(
                $param,
                $value,
                $this->getPDOType($value)
            );
        }

        $stmt->execute();

        return $stmt;
    }


    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

    public function get(): array
    {
        return $this->execute()
            ->fetchAll(
                PDO::FETCH_CLASS,
                get_class($this->model)
            );
    }


    /*
    |--------------------------------------------------------------------------
    | GET RAW
    |--------------------------------------------------------------------------
    */

    public function getRaw(): array
    {
        return $this->execute()
            ->fetchAll(
                PDO::FETCH_ASSOC
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FIRST
    |--------------------------------------------------------------------------
    */

    public function first(): ?object
    {
        $oldLimit = $this->limit;

        $this->limit(1);

        $result =
            $this->get()[0] ?? null;

        $this->limit = $oldLimit;

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | VALUE
    |--------------------------------------------------------------------------
    */

    public function value(
        string $column
    ): mixed {

        $oldSelects = $this->selects;
        $oldLimit = $this->limit;

        $this->selects = [$column];
        $this->limit = 1;

        $result =
            $this->execute()
                ->fetchColumn();

        $this->selects = $oldSelects;
        $this->limit = $oldLimit;

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | PLUCK
    |--------------------------------------------------------------------------
    */

    public function pluck(
        string $column
    ): array {

        $oldSelects = $this->selects;

        $this->selects = [$column];

        $rows = $this->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        $this->selects = $oldSelects;

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | COUNT
    |--------------------------------------------------------------------------
    */

    public function count(
        string $column = '*'
    ): int {

        $oldSelects = $this->selects;

        $this->selects = [
            "COUNT({$column}) AS aggregate_count"
        ];

        $result =
            $this->execute()
                ->fetch(PDO::FETCH_ASSOC);

        $this->selects = $oldSelects;

        return (int) (
            $result['aggregate_count'] ?? 0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUM
    |--------------------------------------------------------------------------
    */

    public function sum(
        string $column
    ): float|int {

        return $this->aggregate(
            'SUM',
            $column
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AVG
    |--------------------------------------------------------------------------
    */

    public function avg(
        string $column
    ): float|int {

        return $this->aggregate(
            'AVG',
            $column
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MIN
    |--------------------------------------------------------------------------
    */

    public function min(
        string $column
    ): mixed {

        return $this->aggregate(
            'MIN',
            $column
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MAX
    |--------------------------------------------------------------------------
    */

    public function max(
        string $column
    ): mixed {

        return $this->aggregate(
            'MAX',
            $column
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AGGREGATE
    |--------------------------------------------------------------------------
    */

    protected function aggregate(
        string $function,
        string $column
    ): mixed {

        $oldSelects = $this->selects;

        $this->selects = [
            "{$function}({$column}) AS aggregate_value"
        ];

        $result =
            $this->execute()
                ->fetch(PDO::FETCH_ASSOC);

        $this->selects = $oldSelects;

        return $result['aggregate_value'] ?? null;
    }


    /*
    |--------------------------------------------------------------------------
    | EXISTS
    |--------------------------------------------------------------------------
    */

    public function exists(): bool
    {
        $oldSelects = $this->selects;
        $oldLimit = $this->limit;

        $this->selects = ['1'];
        $this->limit = 1;

        $result =
            $this->execute()
                ->fetchColumn();

        $this->selects = $oldSelects;
        $this->limit = $oldLimit;

        return $result !== false;
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    public function insert(
        array $data
    ): bool {

        if (empty($data)) {
            throw new InvalidArgumentException(
                'Insert data cannot be empty.'
            );
        }

        $columns = array_keys($data);

        $params = [];

        $bindings = [];

        foreach ($data as $column => $value) {

            $param =
                ':' .
                str_replace(
                    '.',
                    '_',
                    $column
                ) .
                '_' .
                count($bindings);

            $params[] = $param;
            $bindings[$param] = $value;
        }

        $sql =
            "INSERT INTO {$this->table} " .
            "(" .
            implode(', ', $columns) .
            ") VALUES (" .
            implode(', ', $params) .
            ")";

        $stmt = DBModel::prepare($sql);

        foreach ($bindings as $param => $value) {

            $stmt->bindValue(
                $param,
                $value,
                $this->getPDOType($value)
            );
        }

        return $stmt->execute();
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        array $data
    ): int {

        if (empty($data)) {
            throw new InvalidArgumentException(
                'Update data cannot be empty.'
            );
        }


        /*
         * Safety:
         * Don't allow UPDATE without WHERE.
         */

        if (empty($this->wheres)) {
            throw new InvalidArgumentException(
                'Update requires at least one WHERE condition.'
            );
        }


        $sets = [];

        $updateBindings = [];

        foreach ($data as $column => $value) {

            $param =
                ':update_' .
                str_replace(
                    '.',
                    '_',
                    $column
                ) .
                '_' .
                count($updateBindings);

            $sets[] =
                "{$column} = {$param}";

            $updateBindings[$param] =
                $value;
        }


        $sql =
            "UPDATE {$this->table} SET " .
            implode(', ', $sets);


        if (!empty($this->wheres)) {

            $sql .=
                ' WHERE ' .
                implode(
                    ' ',
                    $this->wheres
                );
        }


        $stmt = DBModel::prepare($sql);


        foreach (
            $updateBindings
            as $param => $value
        ) {

            $stmt->bindValue(
                $param,
                $value,
                $this->getPDOType($value)
            );
        }


        foreach (
            $this->bindings
            as $param => $value
        ) {

            $stmt->bindValue(
                $param,
                $value,
                $this->getPDOType($value)
            );
        }


        $stmt->execute();

        return $stmt->rowCount();
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function delete(): int
    {
        /*
         * Safety:
         * Don't allow DELETE without WHERE.
         */

        if (empty($this->wheres)) {

            throw new InvalidArgumentException(
                'Delete requires at least one WHERE condition.'
            );
        }


        $sql =
            "DELETE FROM {$this->table}";


        if (!empty($this->wheres)) {

            $sql .=
                ' WHERE ' .
                implode(
                    ' ',
                    $this->wheres
                );
        }


        $stmt = DBModel::prepare(
            $sql
        );


        foreach (
            $this->bindings
            as $param => $value
        ) {

            $stmt->bindValue(
                $param,
                $value,
                $this->getPDOType($value)
            );
        }


        $stmt->execute();

        return $stmt->rowCount();
    }


    /*
    |--------------------------------------------------------------------------
    | PAGINATE
    |--------------------------------------------------------------------------
    */

    public function paginate(
        int $page = 1,
        int $perPage = 15
    ): array {

        if ($page < 1) {
            $page = 1;
        }

        if ($perPage < 1) {
            $perPage = 15;
        }

        /*
         * Count before applying pagination.
         */

        $total = $this->count();

        $offset =
            ($page - 1) * $perPage;

        $oldLimit = $this->limit;
        $oldOffset = $this->offset;

        $this->limit($perPage);
        $this->offset($offset);


        $data = $this->get();

        $this->limit = $oldLimit;
        $this->offset = $oldOffset;



        $lastPage =
            $total > 0
                ? (int) ceil(
                    $total / $perPage
                )
                : 1;

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' =>
                $total > 0
                    ? $offset + 1
                    : null,
            'to' =>
                $total > 0
                    ? min(
                        $offset + $perPage,
                        $total
                    )
                    : null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PARAMETER NAME
    |--------------------------------------------------------------------------
    */

    protected function parameterName(
        string $column
    ): string {

        $column =
            str_replace(
                '.',
                '_',
                $column
            );

        return ':' .
            $column .
            '_' .
            count($this->bindings);
    }


    /*
    |--------------------------------------------------------------------------
    | APPEND WHERE
    |--------------------------------------------------------------------------
    */

    protected function appendWhere(
        string $condition,
        string $boolean = 'AND'
    ): void {

        $boolean =
            strtoupper($boolean);

        if (empty($this->wheres)) {

            $this->wheres[] =
                $condition;

        } else {

            $this->wheres[] =
                "{$boolean} {$condition}";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | APPEND HAVING
    |--------------------------------------------------------------------------
    */

    protected function appendHaving(
        string $condition,
        string $boolean = 'AND'
    ): void {

        $boolean =
            strtoupper($boolean);

        if (empty($this->havings)) {

            $this->havings[] =
                $condition;

        } else {

            $this->havings[] =
                "{$boolean} {$condition}";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PDO TYPE
    |--------------------------------------------------------------------------
    */

    protected function getPDOType(
        mixed $value
    ): int {

        return match (true) {

            is_int($value)
                => PDO::PARAM_INT,

            is_bool($value)
                => PDO::PARAM_BOOL,

            $value === null
                => PDO::PARAM_NULL,

            default
                => PDO::PARAM_STR,
        };
    }
}

