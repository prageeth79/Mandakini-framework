<?php
namespace app\core\db;

use app\core\Application;

/**
 * MySQL-backed DBModel that automatically discovers table columns
 * and provides sensible defaults for `attributes()` and `primaryKey()`.
 */
abstract class MySqlDBColumnsModel extends MySqlDBModel
{
	/**
	 * Cache of discovered columns metadata per table.
	 * Format: [ 'table_name' => [ 'columns' => [...names...], 'primary' => 'id' ] ]
	 *
	 * @var array
	 */
	protected static array $columnsCache = [];
	protected array $columnData = [];


	public function __construct(){
		$this->loadDBColumns();
	}


	public function __get($key): mixed
	{
		return $this->columnData[$key] ?? null;
	}

	public function __set($key, $value): void
	{
		// If column type metadata is available, cast common MySQL types appropriately
		if (method_exists($this, 'getColumnTypes')) {
			$types = $this->getColumnTypes();
			if (isset($types[$key])) {
				$colType = strtolower($types[$key]);
				if (strpos($colType, 'tinyint(1)') === 0 || $colType === 'tinyint(1)') {
					$value = (bool) $value;
					$this->columnData[$key] = $value;
					return;
				} elseif (strpos($colType, 'int') !== false) {
					$value = (int) $value;
					$this->columnData[$key] = $value;
					return;
				} elseif (strpos($colType, 'decimal') !== false || strpos($colType, 'numeric') !== false || strpos($colType, 'float') !== false || strpos($colType, 'double') !== false) {
					$value = (float) $value;
					$this->columnData[$key] = $value;
					return;
				} elseif (strpos($colType, 'json') === 0) {
					$decoded = json_decode($value, true);
					if (json_last_error() === JSON_ERROR_NONE) {
						$value = $decoded;
						$this->columnData[$key] = $value;
						return;
					}
				}
			}
		}

		$this->columnData[$key] = $value;
	}

	public function loadDBColumns(){
		$types = $this->getColumnTypes();
		foreach($this->attributes() as $field){
			$colType = strtolower($types[$field]);
			if(strpos($colType, 'tinyint(1)') === 0 || $colType === 'tinyint(1)' || strpos($colType, 'decimal') !== false || strpos($colType, 'numeric') !== false || strpos($colType, 'float') !== false || strpos($colType, 'double') !== false || strpos($colType, 'int') !== false){
				$this->{$field} = 0;
			}else{ 
				$this->{$field} = "";
			}
		}
	}

	/**
	 * Return attributes (columns) for the model's table.
	 * By default this excludes an auto-increment primary key column.
	 *
	 * @return array
	 */


	public function attributes(): array
	{
		$table = static::tableName();
		$this->ensureColumnsCached($table);
		return self::$columnsCache[$table]['columns'] ?? [];
	}

	/**
	 * Return the primary key column name for the table.
	 *
	 * @return string
	 */
	public static function primaryKey(): string
	{
		$table = static::tableName();
		// ensure cache populated
		static::ensureStaticColumnsCached($table);
		return self::$columnsCache[$table]['primary'] ?? 'id';
	}

	/**
	 * Populate the columns cache for a given table (instance helper).
	 */
	protected function ensureColumnsCached(string $table): void
	{
		if (isset(self::$columnsCache[$table])) {
			return;
		}
		$pdo = Application::$app->db->pdo;
		// include COLUMN_TYPE so we can provide type metadata (e.g. int(11), varchar(255), json)
		$stmt = $pdo->prepare("SELECT COLUMN_NAME, COLUMN_KEY, EXTRA, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table ORDER BY ORDINAL_POSITION");
		$stmt->execute(['table' => $table]);
		$cols = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		$names = [];
		$primary = null;
		$types = [];
		foreach ($cols as $col) {
			$colName = $col['COLUMN_NAME'];
			$colKey = $col['COLUMN_KEY'] ?? '';
			$extra = $col['EXTRA'] ?? '';
			$colType = $col['COLUMN_TYPE'] ?? null;
			if ($colType !== null) {
				$types[$colName] = $colType;
			}
			if ($colKey === 'PRI' && $primary === null) {
				$primary = $colName;
			}
			// Exclude auto-increment primary key from attributes (so inserts won't attempt to set it)
			if (!($colKey === 'PRI' && stripos($extra, 'auto_increment') !== false)) {
				$names[] = $colName;
			}
		}

		self::$columnsCache[$table] = [
			'columns' => $names,
			'primary' => $primary,
			'types' => $types,
		];
	}

	/**
	 * Static wrapper to allow calling from static context.
	 */
	protected static function ensureStaticColumnsCached(string $table): void
	{
		if (isset(self::$columnsCache[$table])) {
			return;
		}
		// Create a temporary instance to use the Application PDO access
		$instanceClass = static::class;
		// instantiate without constructor assumptions
		$tmp = (new \ReflectionClass($instanceClass))->newInstanceWithoutConstructor();
		if (method_exists($tmp, 'ensureColumnsCached')) {
			$tmp->ensureColumnsCached($table);
		}
	}

/**

	Return an associative array of column => MySQL COLUMN_TYPE for the model's table.
	Example: ['id' => 'int(11)', 'name' => 'varchar(255)']
	@return array
*/
public function getColumnTypes(): array
{
$table = static::tableName();
$this->ensureColumnsCached($table);
return self::$columnsCache[$table]['types'] ?? [];
}
/**

	Static wrapper to return column types from static context.
	@return array
*/
public static function getColumnTypesStatic(): array
{
$table = static::tableName();
static::ensureStaticColumnsCached($table);
return self::$columnsCache[$table]['types'] ?? [];
}
}