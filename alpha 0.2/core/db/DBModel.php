<?php
namespace app\core\db;
use app\core\Application;
use app\core\Model;

abstract class DBModel extends Model {
    abstract public static function tableName(): string;

    abstract public function attributes(): array;

    abstract public static function primaryKey(): string;

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
        $tableName = static::tableName();
        $attributes = $this->attributes();
        $conditions = array_map(fn($attr) => "$attr = :{$attr}Where", array_keys($where));
        
        $sql = "UPDATE $tableName SET " . implode(', ', array_map(fn($attr) => "$attr = :$attr", $attributes)) . " WHERE " . implode(' AND ', $conditions);
        $statement = self::prepare($sql);
        
        foreach ($attributes as $attribute) {
            self::bindWithDataType($statement, ":$attribute", $this->{$attribute});
        }
        foreach ($where as $key => $value) {
            self::bindWithDataType($statement, ":{$key}Where", $value);
        }
        return $statement->execute();
    }

    /**
     * NEW METHOD: Safely deletes a record based on primary key or conditions
     */
    public function delete($where = []) {
        $tableName = static::tableName();
        if (empty($where)) {
            $primaryKey = static::primaryKey();
            $where = [$primaryKey => $this->{$primaryKey}];
        }
        $conditions = array_map(fn($attr) => "$attr = :$attr", array_keys($where));
        $sql = "DELETE FROM $tableName WHERE " . implode(' AND ', $conditions);
        
        $statement = self::prepare($sql);
        foreach ($where as $key => $value) {
            self::bindWithDataType($statement, ":$key", $value);
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
    public static function findOne($where) {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $key => $item) {
            self::bindWithDataType($statement, ":$key", $item);
        }
        $statement->execute();
        return $statement->fetchObject(static::class);
    }

    /**
     * Find all records with fully validated injection-free LIMIT and ORDER matrices
     */
    public static function findAll(array $where = [], string $orderBy = null, array $limit = []): array {
        $tableName = static::tableName();
        $sql = "SELECT * FROM $tableName";

        if (!empty($where)) {
            $attributes = array_keys($where);
            $sqlWhere = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
            $sql .= " WHERE $sqlWhere";
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
        foreach ($where as $key => $item) {
            self::bindWithDataType($statement, ":$key", $item);
        }
        
        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_CLASS, static::class);
    }
}
