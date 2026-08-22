<?php
namespace app\core\db;
use app\core\Application;
use app\core\Model;

abstract class DBModel extends Model {
    abstract public static function tableName(): string;

    abstract public function attributes(): array;

    abstract public static function primaryKey(): string;


 
    public function save() {
        $tableName = static::tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($attr) => ":$attr", $attributes);
        $statement = self::prepare("INSERT INTO $tableName (" . implode(',', $attributes) . ") VALUES (" . implode(',', $params) . ")");
        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }
        $statement->execute();
        return true;
    }

    public function update($where = []) {
        $tableName = static::tableName();
        $attributes = $this->attributes();
        $conditions = array_map(fn($attr) => "$attr" ." = :$attr" . "Where", array_keys($where));
        $statement = self::prepare("UPDATE $tableName SET " . implode(', ', array_map(fn($attr) => "$attr = :$attr", $attributes)) . " WHERE " . implode(' AND ', $conditions));
        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }
        foreach ($where as $key => $value) {
            $statement->bindValue(":$key" . "Where", $value);
        }
        $statement->execute();
        return true;
    }

    public static function prepare($sql) {
        return Application::$app->db->pdo->prepare($sql);
    }

    /**
     * Begin a database transaction.
     *
     * Usage: DBModel::beginTransaction();
     *
     * @return bool
     */
    public static function beginTransaction(): bool
    {
        return Application::$app->db->pdo->beginTransaction();
    }

    /**
     * Commit the current database transaction.
     *
     * @return bool
     */
    public static function commitTransaction(): bool
    {
        return Application::$app->db->pdo->commit();
    }

    /**
     * Roll back the current database transaction.
     *
     * @return bool
     */
    public static function rollBackTransaction(): bool
    {
        return Application::$app->db->pdo->rollBack();
    }

    /**
     * Convenience helper that runs a callable inside a transaction.
     * The callable's return value is returned on success. On exception,
     * the transaction is rolled back and the exception rethrown.
     *
     * Usage:
     *  DBModel::transaction(function() {
     *      // do multiple DB operations
     *      return true; // optional
     *  });
     *
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    public static function transaction(callable $callback)
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

    public static function findOne($where) {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }
        $statement->execute();
        return $statement->fetchObject(static::class);
    }

    /**
     * Find all records optionally matching a where clause.
     *
     * Usage:
     *  - findAll() => returns all rows
     *  - findAll(['status' => 1]) => returns rows matching WHERE status = :status
     *  - findAll(['status' => 1], 'created_at DESC') => also applies ORDER BY
     *
     * @param array $where
     * @param string|null $orderBy
     * @return array An array of model instances
     */
    public static function findAll(array $where = [], string $orderBy = null, array $limit = []): array {
        $tableName = static::tableName();

        if (empty($where)) {
            $sql = "SELECT * FROM $tableName";
        } else{
            $attributes = array_keys($where);
            $sqlWhere = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
            $sql = "SELECT * FROM $tableName WHERE $sqlWhere";
            
        }

        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        if(isset($limit['offset']) && isset($limit['row_count'])){
            if(is_int($limit['offset']) && is_int($limit['row_count'])){
               $sql .= " LIMIT " . $limit['offset'] . "," . $limit['row_count'];
           }
        }elseif(count($limit) > 2 ){
            if(is_int($limit[0]) && is_int($limit[1])){
               $sql .= " LIMIT " . $limit[0] . "," . $limit[1];
           }
        }

        

        $statement = self::prepare($sql);
        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }
        
        $statement->execute();
        
        return $statement->fetchAll(\PDO::FETCH_CLASS, static::class);
    }
}