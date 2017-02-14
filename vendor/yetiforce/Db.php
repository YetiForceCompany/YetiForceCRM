<?php
namespace App;

/**
 * Database connection class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Db extends \yii\db\Connection
{

	/**
	 * @var boolean whether to turn on prepare emulation. Defaults to false, meaning PDO
	 * will use the native prepare support if available. For some databases (such as MySQL),
	 * this may need to be set true so that PDO can emulate the prepare support to bypass
	 * the buggy native prepare support.
	 * The default value is null, which means the PDO ATTR_EMULATE_PREPARES value will not be changed.
	 */
	public $emulatePrepare = false;

	/**
	 * @var Table of connections with database
	 */
	static private $cache = [];

	/**
	 * @var Configuration with database
	 */
	static private $config;

	/**
	 * @var boolean Enable caching database instance 
	 */
	static public $connectCache = false;

	/**
	 * @var Database Name
	 */
	public $dbName;

	/**
	 * @var Database type
	 */
	public $dbType;

	/**
	 * @var Host database server
	 */
	public $host;

	/**
	 * @var Port database server
	 */
	public $port;

	/**
	 * @var string the class used to create new database [[Command]] objects. If you want to extend the [[Command]] class,
	 * you may configure this property to use your extended version of the class.
	 */
	public $commandClass = '\App\Db\Command';

	/**
	 * @var Cache|string the cache object or the ID of the cache application component that
	 * is used to cache the table metadata.
	 * @see enableSchemaCache
	 */
	public $schemaCache = false;

	/**
	 * Creates the \yii\db\Connection instance.
	 * @param string $type Name of database connection
	 * @return \yii\db\Connection instance
	 */
	public static function getInstance($type = 'base')
	{
		if (isset(static::$cache[$type])) {
			return static::$cache[$type];
		}
		$db = new self(static::getConfig($type));
		$db->dbType = $type;
		static::$cache[$type] = $db;
		return $db;
	}

	/**
	 * Load database connection configuration
	 * @param array $type
	 * @return Array with database configuration.
	 */
	public static function getConfig($type, $reload = false)
	{
		if (!static::$config || $reload) {
			static::$config = require('config/config.db.php');
		}
		if (isset(static::$config[$type])) {
			return static::$config[$type];
		}
		return static::$config['base'];
	}

	/**
	 * Set database connection configuration
	 * @param array $config
	 * @param string $type 
	 */
	public static function setConfig($config, $type = 'base')
	{
		static::$config[$type] = $config;
	}

	/**
	 * Processes a SQL statement by quoting table and column names that are enclosed within double brackets.
	 * Tokens enclosed within double curly brackets are treated as table names, while
	 * tokens enclosed within double square brackets are column names. They will be quoted accordingly.
	 * Also, the percentage character "%" at the beginning or ending of a table name will be replaced
	 * with [[tablePrefix]].
	 * @param string $sql the SQL to be quoted
	 * @return string the quoted SQL
	 */
	public function quoteSql($sql)
	{
		return str_replace('#__', $this->tablePrefix, $sql);
	}

	/**
	 * Returns the ID of the last inserted row or sequence value.
	 * @param string $sequenceName name of the sequence object (required by some DBMS) ex. table vtiger_picklist >>> vtiger_picklist_picklistid_seq
	 * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
	 * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
	 */
	public function getLastInsertID($sequenceName = '')
	{
		return parent::getLastInsertID(str_replace('#__', $this->tablePrefix, $sequenceName));
	}

	/**
	 * Creates the PDO instance.
	 * This method is called by [[open]] to establish a DB connection.
	 * The default implementation will create a PHP PDO instance.
	 * You may override this method if the default PDO needs to be adapted for certain DBMS.
	 * @return PDO the pdo instance
	 */
	protected function createPdoInstance()
	{
		if (\App\Debuger::isDebugBar()) {
			$pdo = new \DebugBar\DataCollector\PDO\TraceablePDO(parent::createPdoInstance());
			\App\Debuger::getDebugBar()->addCollector(new \DebugBar\DataCollector\PDO\PDOCollector($pdo, null, $this->dbType));
			return $pdo;
		}
		return parent::createPdoInstance();
	}

	/**
	 * Get table unique ID. Temporary function 
	 * @param string $tableName
	 * @param false|string $columnName
	 * @param bool $seq
	 * @return int
	 */
	public function getUniqueID($tableName, $columnName = false, $seq = true)
	{
		if ($seq) {
			$tableName .= '_seq';
			$id = (new \App\Db\Query())->from($tableName)->scalar($this);
			$id++;
			$this->createCommand()->update($tableName, [
				'id' => $id,
			])->execute();
		} else {
			$id = (new \App\Db\Query())
				->from($tableName)
				->max($columnName, $this);
			$id++;
		}
		return $id;
	}

	/**
	 * Check if table is present in database
	 * @param string $tableName
	 * @return boolean
	 */
	public function isTableExists($tableName)
	{
		return $this->getSchema()->getTableSchema($tableName) !== null;
	}

	/**
	 * Creating a new DB table
	 * @param string $tableName
	 * @return boolean
	 */
	public function createTable($tableName, $columns)
	{
		$tableOptions = null;
		if ($this->getDriverName() === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 ENGINE=InnoDB';
		}
		$this->createCommand()->createTable($tableName, $columns, $tableOptions)->execute();
	}
}
