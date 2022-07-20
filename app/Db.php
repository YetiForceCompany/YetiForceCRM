<?php

namespace App;

/**
 * Database connection class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Db extends \yii\db\Connection
{
	/**
	 * Sorting order flag.
	 */
	public const ASC = 'ASC';

	/**
	 * Sorting order flag.
	 */
	public const DESC = 'DESC';

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

	/** {@inheritdoc} */
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
		if (isset(self::$cache[$type])) {
			return self::$cache[$type];
		}
		$db = new self(self::getConfig($type));
		$db->dbType = $type;
		self::$cache[$type] = $db;
		return $db;
	}

	/**
	 * Load database connection configuration.
	 *
	 * @param string $type
	 *
	 * @return array with database configuration
	 */
	public static function getConfig(string $type)
	{
		if (!isset(self::$config[$type])) {
			self::$config[$type] = Config::db($type) ?? Config::db('base');
		}
		return self::$config[$type];
	}

	/**
	 * Set database connection configuration.
	 *
	 * @param array  $config
	 * @param string $type
	 */
	public static function setConfig($config, $type = 'base')
	{
		self::$config[$type] = $config;
	}

	/**
	 * Get info database server.
	 *
	 * @return array
	 */
	public function getInfo()
	{
		$pdo = $this->getSlavePdo();
		$statement = $pdo->prepare('SHOW VARIABLES');
		$statement->execute();
		$conf = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
		$statement = $pdo->prepare('SHOW STATUS');
		$statement->execute();
		$conf = array_merge($conf, $statement->fetchAll(\PDO::FETCH_KEY_PAIR));
		$statement = $pdo->prepare('SELECT VERSION()');
		$statement->execute();
		$fullVersion = $statement->fetch(\PDO::FETCH_COLUMN);
		[$version] = explode('-', $conf['version']);
		$conf['version_comment'] = $conf['version_comment'] . '|' . $fullVersion;
		$typeDb = 'MySQL';
		if (false !== stripos($conf['version_comment'], 'MariaDb')) {
			$typeDb = 'MariaDb';
		}
		$memory = $conf['key_buffer_size'] + ($conf['query_cache_size'] ?? 0) + $conf['tmp_table_size'] + $conf['innodb_buffer_pool_size'] +
		($conf['innodb_additional_mem_pool_size'] ?? 0) + $conf['innodb_log_buffer_size'] + ($conf['max_connections'] * ($conf['sort_buffer_size']
				+ $conf['read_buffer_size'] + $conf['read_rnd_buffer_size'] + $conf['join_buffer_size'] + $conf['thread_stack'] + $conf['binlog_cache_size']));
		return \array_merge($conf, [
			'driver' => $this->getDriverName(),
			'typeDb' => $typeDb,
			'serverVersion' => $version,
			'maximumMemorySize' => $memory,
			'clientVersion' => $pdo->getAttribute(\PDO::ATTR_CLIENT_VERSION),
			'connectionStatus' => $pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS),
			'serverInfo' => $pdo->getAttribute(\PDO::ATTR_SERVER_INFO),
		]);
	}

	/**
	 * Get database info.
	 *
	 * @return array
	 */
	public function getDbInfo(): array
	{
		$return = [
			'isFileSize' => false,
			'size' => 0,
			'dataSize' => 0,
			'indexSize' => 0,
			'filesSize' => 0,
			'tables' => [],
		];
		$statement = $this->getSlavePdo()->prepare("SHOW TABLE STATUS FROM `{$this->dbName}`");
		$statement->execute();
		while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
			$return['tables'][$row['Name']] = [
				'rows' => $row['Rows'],
				'format' => $row['Row_format'],
				'engine' => $row['Engine'],
				'dataSize' => $row['Data_length'],
				'indexSize' => $row['Index_length'],
				'collation' => $row['Collation'],
			];
			$return['dataSize'] += $row['Data_length'];
			$return['indexSize'] += $row['Index_length'];
			$return['size'] += $row['Data_length'] += $row['Index_length'];
		}
		try {
			$statement = $this->getSlavePdo()->prepare("SELECT * FROM `information_schema`.`INNODB_SYS_TABLESPACES` WHERE `NAME` LIKE '{$this->dbName}/%'");
			$statement->execute();
			while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
				$tableName = str_replace($this->dbName . '/', '', $row['NAME']);
				if (!empty($row['ALLOCATED_SIZE'])) {
					if (isset($return['tables'][$tableName])) {
						$return['tables'][$tableName]['fileSize'] = $row['ALLOCATED_SIZE'];
						$return['isFileSize'] = true;
					}
					$return['filesSize'] += $row['ALLOCATED_SIZE'];
				}
			}
		} catch (\Throwable $th) {
		}
		return $return;
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
	 * @see https://www.php.net/manual/en/function.PDO-lastInsertId.php
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
		if (Debuger::isDebugBar()) {
			$bebugBar = Debuger::getDebugBar();
			$pdo = new Debug\DebugBar\TraceablePDO(parent::createPdoInstance());
			if ($bebugBar->hasCollector('pdo')) {
				$pdoCollector = $bebugBar->getCollector('pdo');
				$pdoCollector->addConnection($pdo, $this->dbType);
			} else {
				$pdoCollector = new \DebugBar\DataCollector\PDO\PDOCollector();
				$pdoCollector->addConnection($pdo, $this->dbType);
				$bebugBar->addCollector($pdoCollector);
			}
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
		return \in_array(str_replace('#__', $this->tablePrefix, $tableName), $this->getSchema()->getTableNames());
	}

	/**
	 * Creating a new DB table.
	 *
	 * @param string $tableName
	 * @param mixed  $columns
	 *
	 * @return bool
	 */
	public function createTable($tableName, $columns)
	{
		$tableOptions = null;
		if ('mysql' === $this->getDriverName()) {
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
		if ('mysql' === $this->getDriverName()) {
			$dataReader = $this->createCommand()->setSql('SHOW KEYS FROM ' . $tableName)->query();
			while ($row = $dataReader->read()) {
				$keys[$row['Key_name']][$row['Column_name']] = ['columnName' => $row['Column_name'], 'unique' => empty($row['Non_unique'])];
			}
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
		$key = [];
		if ('mysql' === $this->getDriverName()) {
			$tableKeys = $this->getTableKeys($tableName);
			$key = isset($tableKeys['PRIMARY']) ? ['PRIMARY' => array_keys($tableKeys['PRIMARY'])] : [];
		}
		Cache::save('getPrimaryKey', $tableName, $key, Cache::LONG);
		return $key;
	}
}
