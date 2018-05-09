<?php

namespace App;

/**
 * Database connection class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Db extends \yii\db\Connection
{
	/**
	 * @var bool whether to turn on prepare emulation. Defaults to false, meaning PDO
	 *           will use the native prepare support if available. For some databases (such as MySQL),
	 *           this may need to be set true so that PDO can emulate the prepare support to bypass
	 *           the buggy native prepare support.
	 *           The default value is null, which means the PDO ATTR_EMULATE_PREPARES value will not be changed
	 */
	public $emulatePrepare = false;

	/**
	 * @var \App\Db Table of connections with database
	 */
	private static $cache = [];

	/**
	 * @var array Configuration with database
	 */
	private static $config = [];

	/**
	 * @var bool Enable caching database instance
	 */
	public static $connectCache = false;

	/**
	 * @var string Database Name
	 */
	public $dbName;

	/**
	 * @var string Database section
	 */
	public $dbType;

	/**
	 * @var string Host database server
	 */
	public $host;

	/**
	 * @var int Port database server
	 */
	public $port;

	/**
	 * {@inheritdoc}
	 */
	public $schemaMap = [
		'pgsql' => 'App\Db\Drivers\Pgsql\Schema', // PostgreSQL
		'mysqli' => 'yii\db\mysql\Schema', // MySQL
		'mysql' => 'App\Db\Drivers\Mysql\Schema', // MySQL
		'sqlite' => 'yii\db\sqlite\Schema', // sqlite 3
		'sqlite2' => 'yii\db\sqlite\Schema', // sqlite 2
		'sqlsrv' => 'yii\db\mssql\Schema', // newer MSSQL driver on MS Windows hosts
		'oci' => 'yii\db\oci\Schema', // Oracle driver
		'mssql' => 'yii\db\mssql\Schema', // older MSSQL driver on MS Windows hosts
		'dblib' => 'yii\db\mssql\Schema', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
		'cubrid' => 'yii\db\cubrid\Schema', // CUBRID
	];

	/**
	 * @var string the class used to create new database [[Command]] objects. If you want to extend the [[Command]] class,
	 *             you may configure this property to use your extended version of the class
	 */
	public $commandClass = '\App\Db\Command';

	/**
	 * @var Cache|string the cache object or the ID of the cache application component that
	 *                   is used to cache the table metadata
	 *
	 * @see enableSchemaCache
	 */
	public $schemaCache = false;

	/**
	 * Creates the Db connection instance.
	 *
	 * @param string $type Name of database connection
	 *
	 * @return \App\Db
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
	 * Load database connection configuration.
	 *
	 * @param array $type
	 *
	 * @return array with database configuration
	 */
	public static function getConfig($type, $reload = false)
	{
		if (!static::$config || $reload) {
			static::$config = require 'config/config.db.php';
		}
		if (isset(static::$config[$type])) {
			return static::$config[$type];
		}
		return static::$config['base'];
	}

	/**
	 * Set database connection configuration.
	 *
	 * @param array  $config
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
	 *
	 * @param string $sql the SQL to be quoted
	 *
	 * @return string the quoted SQL
	 */
	public function quoteSql($sql)
	{
		return str_replace('#__', $this->tablePrefix, $sql);
	}

	/**
	 * Returns the ID of the last inserted row or sequence value.
	 *
	 * @param string $sequenceName name of the sequence object (required by some DBMS) ex. table vtiger_picklist >>> vtiger_picklist_picklistid_seq
	 *
	 * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
	 *
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
	 *
	 * @return PDO the pdo instance
	 */
	protected function createPdoInstance()
	{
		if (\App\Debuger::isDebugBar() && !\App\Debuger::getDebugBar()->hasCollector('pdo')) {
			$pdo = new \DebugBar\DataCollector\PDO\TraceablePDO(parent::createPdoInstance());
			\App\Debuger::getDebugBar()->addCollector(new \DebugBar\DataCollector\PDO\PDOCollector($pdo, null, $this->dbType));

			return $pdo;
		}
		return parent::createPdoInstance();
	}

	/**
	 * Get table unique ID. Temporary function.
	 *
	 * @param string       $tableName
	 * @param false|string $columnName
	 * @param bool         $seq
	 *
	 * @return int
	 */
	public function getUniqueID($tableName, $columnName = false, $seq = true)
	{
		if ($seq) {
			$tableName .= '_seq';
			$id = (new \App\Db\Query())->from($tableName)->scalar($this);
			++$id;
			$this->createCommand()->update($tableName, [
				'id' => $id,
			])->execute();
		} else {
			$id = (new \App\Db\Query())
				->from($tableName)
				->max($columnName, $this);
			++$id;
		}
		return $id;
	}

	/**
	 * Check if table is present in database.
	 *
	 * @param string $tableName
	 *
	 * @return bool
	 */
	public function isTableExists($tableName)
	{
		return in_array(str_replace('#__', $this->tablePrefix, $tableName), $this->getSchema()->getTableNames());
	}

	/**
	 * Creating a new DB table.
	 *
	 * @param string $tableName
	 *
	 * @return bool
	 */
	public function createTable($tableName, $columns)
	{
		$tableOptions = null;
		if ($this->getDriverName() === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 ENGINE=InnoDB';
		}
		$this->createCommand()->createTable($tableName, $columns, $tableOptions)->execute();
	}

	/**
	 * Get table keys.
	 *
	 * @param string $tableName
	 *
	 * @return array
	 */
	public function getTableKeys($tableName)
	{
		if (Cache::has('getTableKeys', $tableName)) {
			return Cache::get('getTableKeys', $tableName);
		}
		if (!$this->isTableExists($tableName)) {
			return [];
		}
		$tableName = $this->quoteTableName(str_replace('#__', $this->tablePrefix, $tableName));
		$keys = [];
		switch ($this->getDriverName()) {
			case 'mysql':
				$dataReader = $this->createCommand()->setSql('SHOW KEYS FROM ' . $tableName)->query();
				while ($row = $dataReader->read()) {
					$keys[$row['Key_name']][$row['Column_name']] = ['columnName' => $row['Column_name'], 'unique' => empty($row['Non_unique'])];
				}
				break;
		}
		Cache::save('getTableKeys', $tableName, $keys, Cache::LONG);
		return $keys;
	}

	/**
	 * Get table primary keys.
	 *
	 * @param type $tableName
	 *
	 * @return type
	 */
	public function getPrimaryKey($tableName)
	{
		if (Cache::has('getPrimaryKey', $tableName)) {
			return Cache::get('getPrimaryKey', $tableName);
		}
		$tableName = $this->quoteTableName(str_replace('#__', $this->tablePrefix, $tableName));
		$key = [];
		switch ($this->getDriverName()) {
			case 'mysql':
				$tableKeys = $this->getTableKeys($tableName);
				$key = isset($tableKeys['PRIMARY']) ? ['PRIMARY' => array_keys($tableKeys['PRIMARY'])] : [];
				break;
		}
		Cache::save('getPrimaryKey', $tableName, $key, Cache::LONG);
		return $key;
	}
}
