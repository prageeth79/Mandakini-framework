<?php
namespace app\core\db;
use app\core\Application;
use app\core\Model;

abstract class DBModel extends Model {
    abstract public static function tableName(): string;

    abstract public function attributes(): array;

    abstract public static function primaryKey(): string;

    abstract public function calculate(): bool;


    public static function query(): QueryBuilder
    {
        $instance = new static();
        return new QueryBuilder($instance);
    }

    /**
     * Helper shorthand for where conditions.
     */
    public static function where(string $column, string $operator, mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Fetch all records.
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    public static function prepare($sql) {
        return Application::$app->db->pdo->prepare($sql);
    }

    /**
     * Safely binds values with correct PDO constants to prevent injections
     */
    protected static function bindWithDataType($statement, $param, $value) {
        if (is_int($value)) {
            $type = \PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            $type = \PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            $type = \PDO::PARAM_NULL;
        } else {
            $type = \PDO::PARAM_STR;
        }
        $statement->bindValue($param, $value, $type);
    }

    /**
     * Inserts a new record into the database securely
     */
    public function save() {
        $tableName = static::tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($attr) => ":$attr", $attributes);
        
        $sql = "INSERT INTO $tableName (" . implode(',', $attributes) . ") VALUES (" . implode(',', $params) . ")";
        $statement = self::prepare($sql);
        
        foreach ($attributes as $attribute) {
            self::bindWithDataType($statement, ":$attribute", $this->{$attribute});
        }
        return $statement->execute();
    }

    /**
     * Updates an existing record safely using explicit where bounds
     */
    public function update($where = []) {
        $allowed_operators = ['=', '<', '>', '<=', '>=', 'LIKE', '!='];
        $tableName = static::tableName();
        $attributes = $this->attributes();
        $sql = "";
        $conditions = "";
        if(is_string($where)) {
            $conditions = $where;
             $sql = "UPDATE $tableName SET " . implode(', ', array_map(fn($attr) => "$attr = :$attr", $attributes)) . " WHERE " . $conditions;
        }else{
            $conditions = [];
            foreach ($where as $key => $value) {
                if (!in_array($value[0], $allowed_operators)) {
                    throw new \InvalidArgumentException("Invalid operator: {$value[0]}");
                }
                $conditions[] = "$key {$value[0]} :{$key}Where";
            }
            //$conditions = array_map(fn($attr) => "$attr {$where[$attr][0]} :{$attr}Where", array_keys($where));
            
            $sql = "UPDATE $tableName SET " . implode(', ', array_map(fn($attr) => "$attr = :$attr", $attributes)) . " WHERE " . implode(' AND ', $conditions);
        }

        $statement = self::prepare($sql);
        
        foreach ($attributes as $attribute) {
            self::bindWithDataType($statement, ":$attribute", $this->{$attribute});
        }
        if(is_string($where)) {
            //self::bindWithDataType($statement, ":where", $where);
        }else{
            foreach ($where as $key => $value) {
                //self::bindWithDataType($statement, ":{$key}Op", $value[0]);
                self::bindWithDataType($statement, ":{$key}Where", $value[1]);
            }
        }
        return $statement->execute();
    }

    /**
     * NEW METHOD: Safely deletes a record based on primary key or conditions
     */
    public function delete($where = []) {
        $allowed_operators = ['=', '<', '>', '<=', '>=', 'LIKE', '!='];
        $tableName = static::tableName();
        if (empty($where)) {
            $primaryKey = static::primaryKey();
            $where = [$primaryKey => $this->{$primaryKey}];
        }
        $sql = "";
        $conditions = "";
        if(is_string($where)) {
            $conditions = $where;
            $sql = "DELETE FROM $tableName WHERE " . $conditions;
        }else{
            $conditions = [];
            foreach ($where as $key => $value) {
                if (!in_array($value[0], $allowed_operators)) {
                    throw new \InvalidArgumentException("Invalid operator: {$value[0]}");
                }
                $conditions[] = "$key {$value[0]} :{$key}Where";
            }
            $sql = "DELETE FROM $tableName WHERE " . implode(' AND ', $conditions);
        }

        $statement = self::prepare($sql);
        if(is_string($where)) {
            //self::bindWithDataType($statement, ":where", $where);
        }else{
            foreach ($where as $key => $value) {
                //self::bindWithDataType($statement, ":{$key}Op", $value[0]);
                self::bindWithDataType($statement, ":{$key}Where", $value[1]);
            }
        }
        return $statement->execute();
    }
    /**
     * Begin a database transaction
     */
    public static function beginTransaction(): bool {
        return Application::$app->db->pdo->beginTransaction();
    }

    /**
     * Commit the current database transaction
     */
    public static function commitTransaction(): bool {
        return Application::$app->db->pdo->commit();
    }

    /**
     * Roll back the current database transaction
     */
    public static function rollBackTransaction(): bool {
        return Application::$app->db->pdo->rollBack();
    }

    /**
     * Runs a callable inside a managed transaction structure safely
     */
    public static function transaction(callable $callback) {
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

    /**
     * Finds a single row mapped directly to the static Model object
     */
    
    public static function findOne($where): ?object {
        $allowed_operators = ['=', '<', '>', '<=', '>=', 'LIKE', '!='];
        $tableName = static::tableName();
        $attributes = array_keys($where);
        // $sql = implode(" AND ", array_map(fn($attr) => "$attr {:$attr}Op :{$attr}Where", $attributes));
        if (is_string($where)) {
            $sql = $where;
        } else {
            $conditions = [];
            foreach ($where as $key => $item) { 
                
                if (!in_array($item[0], $allowed_operators)) {
                    throw new \InvalidArgumentException("Invalid operator: {$item[0]}");
                }
                if($where[$key][0] === 'LIKE') {
                    $where[$key][1] = "%{$where[$key][1]}%";
                }
                $conditions[] = "$key {$item[0]} :{$key}Where";
            }
            $sql = implode(" AND ", $conditions);
            //$sql = implode(" AND ", array_map(fn($attr) => "$attr {$where[$attr][0]} :{$attr}Where", $attributes));
        }
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
       
            //self::bindWithDataType($statement, ":{$key}Op", $item[0]);
        if (!is_string($where)) {
            //self::bindWithDataType($statement, ":where", $where);
            self::bindWithDataType($statement, ":{$key}Where", $item[1]);
        }
        $statement->execute();
        $result = $statement->fetchObject(static::class);
        return $result ?: null;
        //return $statement->fetchObject(static::class);
    }
  
    /**
     * Find all records with fully validated injection-free LIMIT and ORDER matrices
     */
    
    public static function findAll($where = [], string $orderBy = null, array $limit = []): array {
        $tableName = static::tableName();
        $sql = "SELECT * FROM $tableName";
        if (is_string($where)) {
            $sql .= " WHERE $where";
        } else{
            if (!empty($where)) {
                $conditions = [];
                $attributes = array_keys($where);
                foreach ($where as $key => $item) {
                    if (!in_array($item[0], ['=', '<', '>', '<=', '>=', 'LIKE', '!='])) {
                        throw new \InvalidArgumentException("Invalid operator: {$item[0]}");
                    }
                    if($where[$key][0] === 'LIKE') {
                        $where[$key][1] = "%{$where[$key][1]}%";
                    }
                    $conditions[] = "$key {$item[0]} :{$key}Where";

                }
                $sqlWhere = implode(" AND ", $conditions);
                $sql .= " WHERE $sqlWhere";
            }
        }
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        // Clean integer casting for absolute LIMIT execution
        if (isset($limit['offset']) && isset($limit['row_count'])) {
            $sql .= " LIMIT " . (int)$limit['offset'] . ", " . (int)$limit['row_count'];
        } elseif (count($limit) >= 2) {
            $sql .= " LIMIT " . (int)$limit[0] . ", " . (int)$limit[1];
        }

        $statement = self::prepare($sql);
        if (is_string($where)) {
            //self::bindWithDataType($statement, ":where", $where);
        } else {
            foreach ($where as $key => $item) {
                //self::bindWithDataType($statement, ":{$key}Op", $item[0]);
                self::bindWithDataType($statement, ":{$key}Where", $item[1]);
            }
        }
        
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_CLASS, static::class);
    }
    
}
